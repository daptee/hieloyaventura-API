<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class BorrarViejosPdfs extends Command
{
    protected $signature = 'pdfs:borrar-viejos';
    protected $description = 'Borra los archivos PDF del mes que está 3 meses atrás desde la fecha actual (carpeta principal reservations).';

    public function handle()
    {
        try {
            $now = Carbon::now();

            $target = $now->copy()->subMonths(3);
            $startDate = Carbon::create($target->year, $target->month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            $directory = public_path('reservations');

            if (!File::exists($directory)) {
                $this->warn("⚠️ La carpeta {$directory} no existe.");
                return Command::SUCCESS;
            }

            $pdfFiles = collect(File::files($directory))
                ->filter(fn($file) => strtolower($file->getExtension()) === 'pdf');

            if ($pdfFiles->isEmpty()) {
                $this->info("ℹ️ No se encontraron archivos PDF en {$directory}.");
                return Command::SUCCESS;
            }

            $deletedFiles = [];
            $skippedCount = 0;

            foreach ($pdfFiles as $file) {
                try {
                    $fileCreated = Carbon::createFromTimestamp(File::lastModified($file));

                    if ($fileCreated->between($startDate, $endDate)) {
                        File::delete($file);
                        $deletedFiles[] = [
                            'name' => $file->getFilename(),
                            'created_at' => $fileCreated->toDateTimeString(),
                        ];
                    } else {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    $this->warn("⚠️ Error procesando {$file->getFilename()}: {$e->getMessage()}");
                }
            }

            $deletedCount = count($deletedFiles);

            $logMessage = "🧹 [pdfs:borrar-viejos] Ejecutado el {$now->toDateTimeString()}\n"
                . "Carpeta: {$directory}\n"
                . "Rango: {$startDate->toDateString()} → {$endDate->toDateString()}\n"
                . "Eliminados: {$deletedCount}\n"
                . "Saltados: {$skippedCount}\n";

            if ($deletedCount > 0) {
                $logMessage .= "Archivos eliminados:\n";
                foreach ($deletedFiles as $f) {
                    $logMessage .= " - {$f['name']} ({$f['created_at']})\n";
                }
            }

            $logMessage .= str_repeat('-', 60) . "\n";

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/pdf_cleanup.log'),
            ])->info($logMessage);

            $this->info("✅ Se eliminaron {$deletedCount} PDF(s) del rango {$startDate->toDateString()} a {$endDate->toDateString()}.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/pdf_cleanup.log'),
            ])->error("[pdfs:borrar-viejos] Error: " . $e->getMessage());

            $this->error("❌ Error durante la ejecución del comando: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}