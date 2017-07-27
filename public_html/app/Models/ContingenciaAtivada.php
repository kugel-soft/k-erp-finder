<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class ContingenciaAtivada extends Model {
    protected $table = 'contingencias_ativadas';

    protected $fillable = [
        'texto',
        'visto',
    ];
}