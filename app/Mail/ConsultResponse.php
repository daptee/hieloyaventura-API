<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConsultResponse extends Mailable
{
    use Queueable, SerializesModels;

    public $response = null;
    public $replyto = null;
    public $message = null;
    public $name = null;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->message = $data['message'];
        $this->replyto = $data['email'];
        $this->name = $data['name'];
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->replyTo, $this->name)->markdown('emails.consults.consult');
    }
}
