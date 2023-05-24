<?php

namespace App\Models;

use App\Http\Controllers\PaxController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserReservation as MailUserReservation;
use Illuminate\Support\Facades\Log;

class UserReservation extends Model
{
    use HasFactory;
    protected $casts = [
        'turn' => 'datetime:H:i',
        'date' => 'datetime:Y-m-d'
    ];

    const SHOW = [
        'user',
        'status',
        'excurtion',
        'billing_data',
        'contact_data',
        'paxes.files', 
        'reservation_paxes.pax_type',
        'status_history.status',
        'rejected_history'
    ];
    const INDEX = [
        'status',
        'excurtion',
        'paxes.files',
        'reservation_paxes'
    ];

    protected $fillable = [
        'reservation_number',
        'user_id',
        'hotel_id',
        'excurtion_id',
        'reservation_status_id',
        'date',
        'turn',
        'hotel_name',
        'price',
        'children_price',
        'special_discount',
        'is_paid',
        'is_transfer',
        'notifications_accepted',
        'reservation_checked',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function status(): BelongsTo
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id', 'id');
    }
    
    public function excurtion(): BelongsTo
    {
        return $this->belongsTo(Excurtion::class, 'excurtion_id', 'id');
    }

    public function billing_data(): HasOne
    {
        return $this->hasOne(BillingDataReservation::class, 'user_reservation_id', 'id');
    }

    public function contact_data(): HasOne
    {
        return $this->hasOne(ContactDataReservation::class, 'user_reservation_id', 'id');
    }

    public function paxes()
    {
        return $this->hasMany(Pax::class, 'user_reservation_id', 'id');
    }

    public function reservation_paxes()
    {
        return $this->hasMany(ReservationPax::class, 'user_reservation_id', 'id');
    }

    public function status_history()
    {
        return $this->hasMany(UserReservationStatusHistory::class, 'user_reservation_id', 'id');
    }

    public function rejected_history()
    {
        return $this->hasMany(RejectedReservation::class, 'user_reservation_id', 'id');
    }

    public static function store_user_reservation_status_history($status_id, $user_reservation_id)
    {
        $user_reservation_status = new UserReservationStatusHistory();
        $user_reservation_status->status_id = $status_id;
        $user_reservation_status->user_reservation_id = $user_reservation_id;
        $user_reservation_status->save();
    }

    public static function send_mail_user_reservation_voucher($userReservation)
    {
        $mailTo = $userReservation->contact_data->email;
        $is_bigice = $userReservation->excurtion_id == 2 ? true : false;
        $hash_reservation_number = Crypt::encryptString($userReservation->reservation_number);
        $reservation_number = $userReservation->reservation_number;
        $excurtion_name = $userReservation->excurtion->name;
    
        $pax_controller = new PaxController();
        $zipFilesReservation = $pax_controller->createZipFilesReservation($userReservation->id);
        
        if($zipFilesReservation['fileNameZipReservation']){
            $pathReservationZip = public_path($zipFilesReservation['fileNameZipReservation']);
        }else{
            $pathReservationZip = null;
        }

        // Mail voucher
            try {
                Mail::to($mailTo)->send(new MailUserReservation($mailTo, public_path(parse_url($userReservation->pdf, PHP_URL_PATH)), $pathReservationZip, $is_bigice, $hash_reservation_number, $reservation_number, $excurtion_name, $userReservation->language_id));                        
                return ["status" => 200];
            } catch (\Throwable $th) {
                Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
                return ["message" => "Error al enviar el mail.", "status" => 500];
            }
        //
    }
}
