<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class ProcessCv extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $path_file;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $file_path)
    {
        $this->data = $data;
        $this->path_file = $file_path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.process-cv')->attach($this->path_file);
    }
}
