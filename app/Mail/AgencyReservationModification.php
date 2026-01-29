<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgencyReservationModification extends Mailable
{
    use Queueable, SerializesModels;

    public $reservationNumber;
    public $agencyName;
    public $formattedData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservationNumber, $agencyName, $requestData)
    {
        $this->reservationNumber = $reservationNumber;
        $this->agencyName = $agencyName;
        $this->formattedData = $this->formatRequestData($requestData);
    }

    /**
     * Format request data for email display
     * - Convert underscores to spaces
     * - Capitalize first letter of each word
     * 
     * @param array $data
     * @return array
     */
    private function formatRequestData($data)
    {
        $formatted = [];

        // Exclude these fields from the email body as they're used elsewhere
        $excludeFields = ['reservation_number', 'authenticated_agency', 'excursion_id'];

        foreach ($data as $key => $value) {
            // Skip excluded fields and null values
            if (in_array($key, $excludeFields) || is_null($value)) {
                continue;
            }

            // Skip arrays and objects for now (could be enhanced later)
            if (is_array($value) || is_object($value)) {
                continue;
            }

            // Format the key: replace underscores with spaces and capitalize
            $formattedKey = $this->formatFieldName($key);

            $formatted[] = [
                'label' => $formattedKey,
                'value' => $value
            ];
        }

        return $formatted;
    }

    /**
     * Format field name: replace underscores with spaces and capitalize properly
     * 
     * @param string $fieldName
     * @return string
     */
    private function formatFieldName($fieldName)
    {
        // Replace underscores with spaces
        $withSpaces = str_replace('_', ' ', $fieldName);

        // Capitalize first letter of each word, rest lowercase
        return ucwords(strtolower($withSpaces));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pedido de modificación reserva nro ' . $this->reservationNumber)
            ->view('emails.agency-reservation-modification');
    }
}
