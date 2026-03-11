<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgencyOtpMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp_code;
    public string $type;

    /**
     * @param string $otp_code  Código OTP de 6 dígitos
     * @param string $type      'login' o 'email_change'
     */
    public function __construct(string $otp_code, string $type = 'login')
    {
        $this->otp_code = $otp_code;
        $this->type     = $type;
    }

    public function build()
    {
        $subject = $this->type === 'email_change'
            ? 'Hielo & Aventura - Confirmar cambio de correo'
            : 'Hielo & Aventura - Código de verificación';

        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject)
                    ->view('emails.agency-otp');
    }
}
