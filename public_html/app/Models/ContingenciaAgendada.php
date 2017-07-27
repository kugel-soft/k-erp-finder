<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class ContingenciaAgendada extends Model {
    protected $table = 'contingencias_agendadas';

    protected $fillable = [
        'texto',
        'visto',
    ];
}