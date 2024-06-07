<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class GroupExcurtion extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $email, $attach_file, $agency_user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $agency_user)
    {
        $this->data = $data;
        $this->email = $data['email'];
        $this->attach_file = $data['file'];
        $this->agency_user = $agency_user; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if(is_null($this->attach_file)){
            return $this->replyTo($this->email)
                        ->subject('Hielo & Aventura - nueva solicitud de reserva grupal')
                        ->view('emails.group-excurtion');
        }

        return $this->replyTo($this->email)
                    ->subject('Hielo & Aventura - nueva solicitud de reserva grupal')
                    ->view('emails.group-excurtion')
                    ->attach($this->attach_file);
    }
}
