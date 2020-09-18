<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class ESocial extends Model {
    protected $table = 'esocial';

    protected $fillable = [
        'titulo',
        'url',
        'texto_url',
        'descricao',
        'publicado_em',
        'publicado_as',
        'visto',
    ];
}