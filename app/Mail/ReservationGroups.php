<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationGroups extends Mailable
{
    use Queueable, SerializesModels;

    public $id_solicitud;
    public $attachments;

    /**
     * Create a new message instance.
     * 
     * @param int $id_solicitud
     * @param array $attachments Array of ['path' => '/file/path', 'as' => 'custom_name', 'mime' => 'type']
     */
    public function __construct($id_solicitud, $attachments = [])
    {
        $this->id_solicitud = $id_solicitud;
        $this->attachments = $attachments;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = "Reserva Agencias Grupos - Solicitud nro {$this->id_solicitud} - Archivos";

        $m = $this->subject($subject)
            ->view('emails.reservation_groups')
            ->with([
                'id_solicitud' => $this->id_solicitud,
            ]);

        // Attach files with custom names using file paths (simple, only 'path' entries)
        foreach ($this->attachments as $att) {
            if (is_array($att) && isset($att['path']) && file_exists($att['path'])) {
                $m->attach($att['path'], [
                    'as' => $att['as'] ?? basename($att['path']),
                    'mime' => $att['mime'] ?? 'application/octet-stream',
                ]);
            }
        }

        return $m;
    }
}
