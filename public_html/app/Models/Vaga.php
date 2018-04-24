<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class Vaga extends Model {
    protected $table = 'vagas';

    protected $fillable = [
        'nomeVaga',
	    'nomeEmpresa',
        'tipoVaga',
        'miniTextoVaga',
        'dataPublicacao',
        'urlVaga',
        'visto',
    ];
}