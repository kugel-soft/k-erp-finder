<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class AvisoMDFe extends Model {
    protected $table = 'avisos_mdfe';

    protected $fillable = [
        'titulo',
        'descricao',
        'publicado_em',
        'visto',
    ];
}