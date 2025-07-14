<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiciosDiariosExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return collect($this->data)->map(function ($item) {
            return [
                $item['reservation_number'],
                $item['pax'],
                $item['number_of_passengers'],
                $item['excursion'],
                $item['hotel'],
                $item['transfer'],
                $item['hour'],
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Nro Reserva',
            'Pasajero',
            'Cant',
            'Excursion',
            'Hotel',
            'Transfer',
            'Hora',
        ];
    }
}
