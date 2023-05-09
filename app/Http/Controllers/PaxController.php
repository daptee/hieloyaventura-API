<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaxRequest;
use App\Http\Requests\UpdatePaxRequest;
use App\Models\Pax;
use App\Models\ReservationStatus;
use App\Mail\UserReservation as MailUserReservation;
use App\Models\PaxFile;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

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
        
        if (isset($paxs)) {
            foreach ($paxs as $pax) {
                $pax = Pax::create($pax + ['user_reservation_id' => $request->user_reservation_id]);
                $files = $request->file('files');
                
                if($files){
                    foreach ($files as $file) {
                        $fileName   = time() . '.' . $file->getClientOriginalExtension();
                        
                        Storage::putFileAs('public/paxs/files', $file, $fileName);
                        
                        $path = "storage/paxs/files/$fileName";
                        
                        $pax_file = [
                            'pax_id' => $pax->id,
                            'url' => $path,
                        ];
                        PaxFile::create($pax_file);
                    }
                }
            }
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

        Mail::to($mailTo)->send(new MailUserReservation($mailTo, $pathReservationPdf['pathToSavePdf'], $is_bigice, $hash_reservation_number, $reservation_number, $excurtion_name));                        

        return response(["message" => "Pasajeros guardados con exito"], 200);
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
        
        switch ($language_id) {
            case 1: // Español
                $dateFormated = "$dayText $dayNumber de $month";
                $details = 'Por favor, recordá, que el tiempo de espera del pick up puede ser de hasta 40 minutos.';
                break;
            case 2: // Ingles
                $dateFormated = "$month $dayText $dayNumber";
                $details = 'Please remember that the pick up waiting time can be up to 40 minutes.';
                break;
            case 3: // Portugues
                $dateFormated = "$dayText, $dayNumber de $month";
                $details = 'Lembre-se de que o tempo de espera para retirada pode ser de até 40 minutos.';
                break;
            default: // Default
                $dateFormated = "$dayText $dayNumber de $month";
                $details = 'Por favor, recordá, que el tiempo de espera del pick up puede ser de hasta 40 minutos.';
                break;
            }

        $excurtionName = $newUserReservation->excurtion->name;
        $pathExcurtionLogo = public_path($newUserReservation->excurtion->icon);
        

        $firstPage = $this->withOrWithoutTrf($excurtionName, $newUserReservation->is_transfer, $languageToPdf);
        $secondPage = public_path("excursions/bases/$languageToPdf.pdf");

        // initiate FPDI
        $pdf = new Fpdi();

        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($firstPage);
        $tplId1 = $pdf->importPage(1);

        $pdf->useTemplate($tplId1, -8, -8, 227);

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
            $pdf->SetXY(10, 35);
            $pdf->Write(0, "$thanks $contactName!");
        
        //Nro de reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(40, 61.4);
            $pdf->Write(0, $reservationNumber);

        //nombre del contact data
            $pdf->SetFont('Nunito-SemiBold', '', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->SetXY(55, 74);
            $pdf->Write(0, $contactFullName);

        //cantidad (pasajeros) y nombre de la excursion
            $pdf->SetFont('Nunito-Regular', '', 12);
            $pdf->SetXY(19, 82);
            $pdf->Write(0, $amountPaxesWithDeatails);

            $pdf->SetFont('Nunito-Bold', '', 12);
            // $pdf->SetXY(19, 82);
            $pdf->Write(0, $withTranslation);


        //Fecha de la reserva
            $pdf->SetFont('Nunito-Bold', '', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetXY(19, 88.2);
            $pdf->MultiCell(62, 8.6, $reservationDate, 0, 'C');
        //Hora de la reserva
            // $pdf->SetXY(84, 92.5);
            $pdf->SetXY(83.5, 88.1);
            $pdf->MultiCell(20.5, 8.8, $reservationTurn, 0, 'C');

        //si hay translado poner lo del hotel
        if ($withTranslation) {
            
            $pdf->Image(public_path('ubicacion.png'),20, 105, 5);
            $pdf->SetFont('Nunito-Light','', 12);
            $pdf->SetTextColor(42, 42, 42);
            $pdf->SetXY(28, 108);
            $str = iconv('UTF-8', 'ISO-8859-1', $traduccionesPDF[$languageToPdf]['withTranslationHotel'] . ' ');
            $pdf->Write(0, $str);
            
            $pdf->SetXY(28, 113);
            $str = iconv('UTF-8', 'cp1250', $traduccionesPDF[$languageToPdf]['por_el_hotel'] . ': ');
            $pdf->Write(0, $str);
            //si hay translado poner lo del hotel
            $pdf->SetFont('Nunito-SemiBold','', 12);
            $pdf->SetTextColor(54, 134, 195);
            $pdf->Write(0, $hotelName);
            //Texto informativo 1
            $pdf->SetFont('Nunito-Regular','', 12);
            $pdf->SetTextColor(42, 42, 42);

            $pdf->SetXY(10, 142);
            $pdf->MultiCell(120, 5, $details, 0, 'L');
        }

        //Img
        $pdf->Image($pathExcurtionLogo, 159, 67, 25);

        //Nombre de la excursion
        $pdf->SetFont('GothamRounded-Bold','', 18);
        
        $pdf->SetXY(140, 98);
        $pdf->SetTextColor(54, 134, 195);
        // $pdf->Write(0, $excurtionName);
        $pdf->MultiCell(62, 8, $excurtionName, 0, 'C');


        // $pdf->Output();  
        $pdf->Output($pathToSavePdf, "F");  

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
