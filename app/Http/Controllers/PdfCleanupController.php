<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class PdfCleanupController extends Controller
{
    public function deleteByRange(Request $request)
    {
        // 1) VALIDACIÓN
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        try {
            $startDate = Carbon::parse($request->from_date)->startOfDay();
            $endDate   = Carbon::parse($request->to_date)->endOfDay();

            $directory = public_path('reservations');

            if (!File::exists($directory)) {
                return response()->json([
                    'message' => "La carpeta no existe: {$directory}"
                ], 404);
            }

            // Obtener solo PDFs
            $pdfFiles = collect(File::files($directory))
                ->filter(fn($file) => strtolower($file->getExtension()) === 'pdf');

            if ($pdfFiles->isEmpty()) {
                return response()->json([
                    'message' => "No se encontraron archivos PDF en {$directory}"
                ]);
            }

            $deletedFiles = [];
            $skippedCount = 0;

            // 2) PROCESAR ARCHIVOS
            foreach ($pdfFiles as $file) {
                try {
                    $fileCreated = Carbon::createFromTimestamp(File::lastModified($file));

                    if ($fileCreated->between($startDate, $endDate)) {
                        File::delete($file);
                        $deletedFiles[] = [
                            'name'       => $file->getFilename(),
                            'created_at' => $fileCreated->toDateTimeString(),
                        ];
                    } else {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    Log::warning("⚠️ Error procesando {$file->getFilename()}: {$e->getMessage()}");
                }
            }

            $deletedCount = count($deletedFiles);

            // 3) LOG EN ESPAÑOL
            $logMessage = "🧹 [API] Limpieza de PDFs por rango (Manual)\n"
                . "Rango solicitado: {$startDate->toDateString()} → {$endDate->toDateString()}\n"
                . "Carpeta: {$directory}\n"
                . "Eliminados: {$deletedCount}\n"
                . "Ignorados (fuera de rango): {$skippedCount}\n";

            if ($deletedCount > 0) {
                $logMessage .= "Archivos eliminados:\n";
                foreach ($deletedFiles as $f) {
                    $logMessage .= " - {$f['name']} (creado el {$f['created_at']})\n";
                }
            }

            $logMessage .= str_repeat('-', 60) . "\n";

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/pdf_cleanup.log'),
            ])->info($logMessage);

            // 4) RESPUESTA EN ESPAÑOL
            return response()->json([
                'message'       => "Limpieza realizada correctamente.",
                'descripcion'   => "Se eliminaron todos los PDF dentro del rango especificado.",
                'eliminados'    => $deletedCount,
                'ignorados'     => $skippedCount,
                'archivos'      => $deletedFiles,
            ]);

        } catch (Exception $e) {
            Log::error("[API] Error en limpieza de PDFs: " . $e->getMessage());

            return response()->json([
                'error' => "Ocurrió un error durante el proceso: " . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteByRangeAgencies(Request $request)
    {
        // 1) VALIDACIÓN
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        try {
            $startDate = Carbon::parse($request->from_date)->startOfDay();
            $endDate   = Carbon::parse($request->to_date)->endOfDay();

            $directory = public_path('reservations/agencies');

            if (!File::exists($directory)) {
                return response()->json([
                    'message' => "La carpeta no existe: {$directory}"
                ], 404);
            }

            // Obtener solo PDFs
            $pdfFiles = collect(File::files($directory))
                ->filter(fn($file) => strtolower($file->getExtension()) === 'pdf');

            if ($pdfFiles->isEmpty()) {
                return response()->json([
                    'message' => "No se encontraron archivos PDF en {$directory}"
                ]);
            }

            $deletedFiles = [];
            $skippedCount = 0;

            // 2) PROCESAR ARCHIVOS
            foreach ($pdfFiles as $file) {
                try {
                    $fileCreated = Carbon::createFromTimestamp(File::lastModified($file));

                    if ($fileCreated->between($startDate, $endDate)) {
                        File::delete($file);
                        $deletedFiles[] = [
                            'name'       => $file->getFilename(),
                            'created_at' => $fileCreated->toDateTimeString(),
                        ];
                    } else {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    Log::warning("⚠️ Error procesando {$file->getFilename()}: {$e->getMessage()}");
                }
            }

            $deletedCount = count($deletedFiles);

            // 3) LOG EN ESPAÑOL
            $logMessage = "🧹 [API] Limpieza de PDFs por rango (Manual)\n"
                . "Rango solicitado: {$startDate->toDateString()} → {$endDate->toDateString()}\n"
                . "Carpeta: {$directory}\n"
                . "Eliminados: {$deletedCount}\n"
                . "Ignorados (fuera de rango): {$skippedCount}\n";

            if ($deletedCount > 0) {
                $logMessage .= "Archivos eliminados:\n";
                foreach ($deletedFiles as $f) {
                    $logMessage .= " - {$f['name']} (creado el {$f['created_at']})\n";
                }
            }

            $logMessage .= str_repeat('-', 60) . "\n";

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/agencies_pdf_cleanup.log'),
            ])->info($logMessage);

            // 4) RESPUESTA EN ESPAÑOL
            return response()->json([
                'message'       => "Limpieza realizada correctamente (agencia).",
                'descripcion'   => "Se eliminaron todos los PDF dentro del rango especificado. (agencia)",
                'eliminados'    => $deletedCount,
                'ignorados'     => $skippedCount,
                'archivos'      => $deletedFiles,
            ]);

        } catch (Exception $e) {
            Log::error("[API] Error en limpieza de PDFs: " . $e->getMessage());

            return response()->json([
                'error' => "Ocurrió un error durante el proceso: " . $e->getMessage(),
            ], 500);
        }
    }
}