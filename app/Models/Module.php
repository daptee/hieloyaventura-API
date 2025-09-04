<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    const USUARIOS = 1;
    const RESERVAS_WEB = 2;
    const CONFIGURACIONES = 3;
    const AGENCIAS = 4;
    const EXCURSIONES = 5;
    const RESERVAS_AGENCIAS = 6;
}
