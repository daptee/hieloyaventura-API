<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class UploadFileHelper
{
    /**
     *
     *
     * @return String
     */
    public static function createFiles($file, $disk, $name, $path)
    {
        $index = 0;
        while (Storage::disk($disk)->exists('/' . $path . '/' . $name . '_' . $index . '.' . $file->getClientOriginalExtension())) {
            $index++;
        }
        $nombre = $name . '_' . $index . '.' . $file->getClientOriginalExtension();
        Storage::disk($disk)->putFileAs($path, $file, $nombre);
        return Storage::disk($disk)->url('/' . $path . '/' . $nombre);
    }
}
