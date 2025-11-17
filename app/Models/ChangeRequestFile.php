<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeRequestFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'change_request_id',
        'path',
        'original_name',
    ];

    public function changeRequest()
    {
        return $this->belongsTo(ChangeRequest::class);
    }
}