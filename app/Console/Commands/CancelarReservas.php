<?php

namespace App\Console\Commands;

use App\Models\ReservationStatus;
use App\Models\UserReservation;
use Illuminate\Console\Command;

class CancelarReservas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancelar:reservas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando se encarga de cancelar reservas que quedaron pendientes (perdidas)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reservations = UserReservation::where('reservation_status_id', ReservationStatus::STARTED)
                                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                                    ->update(['reservation_status_id' => ReservationStatus::AUTOMATIC_CANCELED]);
    }
}
