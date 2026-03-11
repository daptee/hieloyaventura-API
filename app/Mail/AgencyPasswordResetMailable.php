<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgencyPasswordResetMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $user_name;
    public string $new_password;

    public function __construct(string $user_name, string $new_password)
    {
        $this->user_name    = $user_name;
        $this->new_password = $new_password;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Hielo & Aventura - Restablecimiento de contraseña')
                    ->view('emails.agency-password-reset');
    }
}
