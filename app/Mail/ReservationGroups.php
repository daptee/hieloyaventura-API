<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationGroups extends Mailable
{
    use Queueable, SerializesModels;

    public $id_solicitud;
    public $files;

    /**
     * Create a new message instance.
     * 
     * @param int $id_solicitud
     * @param array $attachments Array of ['path' => '/file/path', 'as' => 'custom_name', 'mime' => 'type']
     */
    public function __construct($id_solicitud, $files = [])
    {
        $this->id_solicitud = $id_solicitud;
        $this->files = $files;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->subject('Reserva Grupos - Solicitud: ' . $this->id_solicitud)
            ->view('emails.reservation_groups');

        if (is_array($this->files)) {
            foreach ($this->files as $filePath) {
                if (file_exists($filePath)) {

                    $mime = function_exists('mime_content_type')
                        ? mime_content_type($filePath)
                        : 'application/octet-stream';

                    $email->attach($filePath, [
                        'as' => basename($filePath),
                        'mime' => $mime
                    ]);
                }
            }
        }

        return $email;
    }

}
