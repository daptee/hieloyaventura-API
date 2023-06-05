<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaxRequest;
use App\Http\Requests\UpdatePaxRequest;
use App\Models\Pax;
use App\Models\ReservationStatus;
use App\Mail\UserReservation as MailUserReservation;
use App\Mail\UserReservationAttachedPassengerFiles;
use App\Models\PaxFile;
use Illuminate\Support\Facades\Log;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\File;

class PaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaxRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaxRequest $request)
    {
        $userReservation = UserReservation::find($request->user_reservation_id);

        if(!isset($userReservation))
            return response(["message" => "User Reservation ID Invalido."], 422);

        $paxs = $request->paxs;

        try {
            if (isset($paxs)) {
                DB::transaction(function () use ($paxs, $request) {
                    $paxFiles = [];

                    ini_set('memory_limit', '128M');
                    foreach ($paxs as $pax) {
                        $new_pax = Pax::create($pax + ['user_reservation_id' => $request->user_reservation_id]);
                        if($pax['files']){
                            foreach ($pax['files'] as $file) {
                                $fileName   = Str::random(5) . time() . '.' . $file->extension();
                                $file->move(public_path("paxs/files/$request->user_reservation_id"),$fileName);
                                $path = "/paxs/files/$request->user_reservation_id/$fileName";
                                
                                $paxFiles[] = [
                                    'pax_id' => $new_pax->id,
                                    'url' => $path,
                                ];

                                // $pax_file = [
                                //     'pax_id' => $new_pax->id,
                                //     'url' => $path,
                                // ];
                                // PaxFile::create($pax_file);
                            }
                        }
                    }

                    if (!empty($paxFiles)) {
                        PaxFile::insert($paxFiles);
                    }
                });
            }

            $userReservation->reservation_status_id = ReservationStatus::COMPLETED;
            $userReservation->save();

            $user_reservation_status = new UserReservationStatusHistory();
            $user_reservation_status->status_id = ReservationStatus::COMPLETED;
            $user_reservation_status->user_reservation_id = $userReservation->id;
            $user_reservation_status->save();

            //Mandar email con el PDF adjunto
            $pathReservationPdf = $this->createPdf($userReservation);                                
            $userReservation->pdf = $pathReservationPdf['urlToSave'];
            $userReservation->save();

            $mailTo = $userReservation->contact_data->email;
            $is_bigice = $userReservation->excurtion_id == 2 ? true : false;
            $hash_reservation_number = Crypt::encryptString($userReservation->reservation_number);
            $reservation_number = $userReservation->reservation_number;
            $excurtion_name = $userReservation->excurtion->name;

            $zipFilesReservation = $this->createZipFilesReservation($request->user_reservation_id);
        
            if($zipFilesReservation['fileNameZipReservation']){
                $pathReservationZip = public_path($zipFilesReservation['fileNameZipReservation']);
                $paxs = Pax::where('user_reservation_id', $request->user_reservation_id);
                try {
                    // Mail::to("enzo100amarilla@gmail.com")->send(new UserReservationAttachedPassengerFiles($pathReservationZip, $reservation_number, $paxs));                        
                    Mail::to("ventas@hieloyaventura.com")->send(new UserReservationAttachedPassengerFiles($pathReservationZip, $reservation_number, $paxs));                        
                } catch (Exception $error) {
                    Log::debug(print_r([$error->getMessage(), $error->getLine()],  true));
                }
            }
            
            try {
                Mail::to($mailTo)->send(new MailUserReservation($mailTo, $pathReservationPdf['pathToSavePdf'], $is_bigice, $hash_reservation_number, $reservation_number, $excurtion_name, $userReservation->language_id));                        
                // Mail::to("enzo100amarilla@gmail.com")->send(new MailUserReservation($mailTo, $pathReservationPdf['pathToSavePdf'], $is_bigice, $hash_reservation_number, $reservation_number, $excurtion_name, $userReservation->language_id));                        
            } catch (Exception $error) {
                Log::debug(print_r([$error->getMessage(), $error->getLine()],  true));
                return response(["error" => $error->getMessage()], 600);
            }

            File::delete($pathReservationZip);

        } catch (\Throwable $th) {
            Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
            return response(["error" => $th->getMessage()], 500);
        }

        return response(["message" => "Pasajeros guardados con exito"], 200);
    }

    public function createZipFilesReservation($user_reservation_id)
    {
        $zip = new ZipArchive;
   
        $fileNameZipReservation = "zipFilesReservation$user_reservation_id.zip";
        $directoryPath = public_path("paxs/files/$user_reservation_id");
      
        if (file_exists($directoryPath)) {
            if ($zip->open(public_path($fileNameZipReservation), ZipArchive::CREATE) === TRUE)
            {

                $files = File::files($directoryPath);
                
                foreach ($files as $key => $value) {
                    $relativeNameInZipFile = basename($value);
                    $zip->addFile($value, $relativeNameInZipFile);
                }
                    
                $zip->close();
            }
        }else{
            $fileNameZipReservation = null;
        }

        return ['fileNameZipReservation'=> $fileNameZipReservation];
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function show(Pax $pax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function edit(Pax $pax)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaxRequest  $request
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaxRequest $request, Pax $pax)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pax $pax)
    {
        //
    }

    private function createPdf($newUserReservation)
    {            
        // Language
        $array_languages = [ 
            1 => 'ES',
            2 => 'EN',
            3 => 'PT'
        ];
            
        $language_id = $newUserReservation->language_id ?? 1;

        Carbon::setLocale(strtolower($array_languages[$language_id]));
        
        $languageToPdf = $array_languages[$language_id];

        if(!is_dir('reservations'))
            mkdir(public_path("reservations"));

        $date = $newUserReservation->date;
        $dayText = ucfirst($date->translatedFormat('l'));
        $dayNumber = $date->format('j');
        $month = ucfirst($date->translatedFormat('F'));
        $excurtionName = $newUserReservation->excurtion->name;
        
        switch ($language_id) {
            case 1: // Español
                $dateFormated = "$dayText $dayNumber de $month";
                $details = "Nos complace informarte que tu reserva del ";
                $booking_report = "$excurtionName ha sido confirmada";
                break;
            case 2: // Ingles
                $dateFormated = "$month $dayText $dayNumber";
                $details = "We are pleased to inform you that your reservation of the ";
                $booking_report = "$excurtionName has been confirmed";
                break;
            case 3: // Portugues
                $dateFormated = "$dayText, $dayNumber de $month";
                $details = "Temos o prazer de informar que a sua reserva do ";
                $booking_report = "$excurtionName foi confirmado";
                break;
            default: // Default
                $dateFormated = "$dayText $dayNumber de $month";
                $details = "Nos complace informarte que tu reserva del ";
                $booking_report = "$excurtionName ha sido confirmada";
                break;
            }

        // $pathExcurtionLogo = public_path($newUserReservation->excurtion->icon);
        

        // $firstPage = $this->withOrWithoutTrf($excurtionName, $newUserReservation->is_transfer, $languageToPdf);
        // $secondPage = public_path("excursions/bases/$languageToPdf.pdf");
        $base_pdf = $languageToPdf . '_' . str_replace(' ', '_', $excurtionName);
        $secondPage = public_path("excursions/bases/$base_pdf.pdf");

        // initiate FPDI
        $pdf = new Fpdi();

        // $pdf->AddPage();
        // set the source file
        // $pdf->setSourceFile($firstPage);
        // $tplId1 = $pdf->importPage(1);

        // $pdf->useTemplate($tplId1, -8, -8, 227);

        // add a page
        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($secondPage);
        // import page 1
        $tplId = $pdf->importPage(1);
        // use the imported page
        $pdf->useTemplate($tplId, 0, 0, 210);


        $traduccionesPDF = [
            'ES' => [
                'de_dateFormated' => 'de',
                'thanks' => '¡Gracias por tu compra',
                'withTranslation' => 'con traslado',
                'withTranslationHotel' => 'El dia de la excursión, el pick up pasará a buscarte',
                'por_el_hotel' => 'por el hotel'
            ],
            'EN' => [
                'de_dateFormated' => 'of',
                'thanks' => 'Thanks for your purchase',
                'withTranslation' => 'with transfer',
                'withTranslationHotel' => 'On the day of the excursion, the pick up will pick you up',
                'por_el_hotel' => 'by the hotel'
            ],
            'PT' => [
                'de_dateFormated' => 'do',
                'thanks' => 'Obrigado pela sua compra',
                'withTranslation' => 'com transferência',
                'withTranslationHotel' => 'No dia da excursão, o pick up irá buscá-lo',
                'por_el_hotel' => 'pelo hotel'
            ]
        ];
        
        //Textos
        $thanks                  = iconv('UTF-8', 'ISO-8859-1', $traduccionesPDF[$languageToPdf]['thanks']);
        $reservationNumber       = iconv('UTF-8', 'cp1250', "#$newUserReservation->reservation_number");
        $contactFullName         = iconv('UTF-8', 'cp1250', $newUserReservation->contact_data->name . " " . $newUserReservation->contact_data->lastname);
        $contactName             = iconv('UTF-8', 'cp1250', $newUserReservation->contact_data->name);
        $withTranslation         = iconv('UTF-8', 'ISO-8859-1', $newUserReservation->is_transfer ? ' ' . $traduccionesPDF[$languageToPdf]['withTranslation'] : '');
        $amountPaxesWithDeatails = iconv('UTF-8', 'cp1250', $newUserReservation->reservation_paxes->sum('quantity') . "x $excurtionName");
        $reservationDate         = iconv('UTF-8', 'cp1250', $dateFormated);
        $reservationTurn         = iconv('UTF-8', 'cp1250', $newUserReservation->turn->format('H:i\h\s'));
        $hotelName               = iconv('UTF-8', 'cp1250', $newUserReservation->hotel_name);
        $details                 = iconv('UTF-8', 'cp1250', $details);
        $excurtionName           = iconv('UTF-8', 'cp1250', $excurtionName);
        $namePdf                 = "reservation-$newUserReservation->id" . "-$newUserReservation->reservation_number.pdf";
        $pathToSavePdf           = public_path("reservations/$namePdf");
        $urlToSave               = url("reservations/$namePdf");

        // now write some text above the imported page
        //Nro de reserva
        $pdf->AddFont('Nunito-Light','','Nunito-Light.php');
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AddFont('Nunito-SemiBold','','Nunito-SemiBold.php');
        $pdf->AddFont('Nunito-Bold','','Nunito-Bold.php');
        $pdf->AddFont('Nunito-Medium','','Nunito-Medium.php');
        $pdf->AddFont('GothamRounded-Bold','','GothamRounded-Bold.php');

        //Agradecimiento por la compra
            $pdf->SetFont('GothamRounded-Bold', '', 14);
            $pdf->SetTextColor(12, 180, 181);
            $pdf->SetXY(8, 75);
            $pdf->Write(0, "$thanks $contactName!");
        
        //Nro de reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(40, 102.4);
            $pdf->Write(0, $reservationNumber);

        //nombre del contact data
            $pdf->SetFont('Nunito-SemiBold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(54.4, 114.9);
            $pdf->Write(0, $contactFullName);

        //cantidad (pasajeros) y nombre de la excursion
            $pdf->SetFont('Nunito-Regular', '', 12);
            $pdf->SetXY(19, 122);
            $pdf->Write(0, $amountPaxesWithDeatails);

            $pdf->SetFont('Nunito-Bold', '', 12);
            // $pdf->SetXY(19, 82);
            $pdf->Write(0, $withTranslation);


        //Fecha de la reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetXY(19, 129);
            $pdf->MultiCell(62, 8.6, $reservationDate, 0, 'C');
        //Hora de la reserva
            // $pdf->SetXY(84, 92.5);
            $pdf->SetXY(83.5, 129);
            $pdf->MultiCell(20.5, 8.8, $reservationTurn, 0, 'C');

        //si hay translado poner lo del hotel
        if ($withTranslation) {
            
            $pdf->Image(public_path('ubicacion.png'),20, 142, 5);
            $pdf->SetFont('Nunito-Light','', 12);
            $pdf->SetTextColor(42, 42, 42);
            $pdf->SetXY(28, 145);
            $str = iconv('UTF-8', 'ISO-8859-1', $traduccionesPDF[$languageToPdf]['withTranslationHotel'] . ' ');
            $pdf->Write(0, $str);
            
            $pdf->SetXY(28, 150);
            $str = iconv('UTF-8', 'cp1250', $traduccionesPDF[$languageToPdf]['por_el_hotel'] . ': ');
            $pdf->Write(0, $str);
            //si hay translado poner lo del hotel
            $pdf->SetFont('Nunito-SemiBold','', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->Write(0, $hotelName);
        }
        
        // Booking report
        $pdf->SetFont('Nunito-Regular','', 11);
        $pdf->SetTextColor(42, 42, 42);
        $pdf->SetXY(8, 82);
        
        $pdf->Write(0, $details);
        
        $pdf->SetTextColor(54, 134, 195);
        $pdf->Write(0, $booking_report);

        //Img
        // $pdf->Image($pathExcurtionLogo, 162, 67, 16);

        //Nombre de la excursion
        $pdf->SetFont('GothamRounded-Bold','', 18);
        
        $pdf->SetXY(140, 98);
        $pdf->SetTextColor(54, 134, 195);
        // $pdf->Write(0, $excurtionName);
        // $pdf->MultiCell(62, 8, $excurtionName, 0, 'C');


        $pdf->Output($pathToSavePdf, "F");  

        // return $pdf->Output();
        return [
            'urlToSave' => $urlToSave, 
            'pathToSavePdf' => $pathToSavePdf
        ];

    }   

    private function withOrWithoutTrf( $excursionName, $transfer, $language = 'ES')
    {
        $withOrWithoutTrf = $transfer ? 'con-trf' : 'sin-trf' ;

        $excursionName = strtolower($excursionName);

        return public_path("excursions/$excursionName/pdfs/$withOrWithoutTrf/$language.pdf");
    }
}
