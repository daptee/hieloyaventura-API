<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgencyIntegrationWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $agencyName;
    public $apiKey;
    public $environment;
    public $documentationUrl;

    public function __construct($agencyName, $apiKey, $environment)
    {
        $this->agencyName = $agencyName;
        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->documentationUrl = 'https://documenter.getpostman.com/view/1349716/2sBXcKCdkr';
    }

    public function build()
    {
        $envLabel = $this->environment === 'production' ? 'Producción' : 'Desarrollo';
        return $this->subject("Hielo & Aventura - Bienvenido a la integración por API ($envLabel)")
            ->view('emails.agency-integration-welcome');
    }
}
