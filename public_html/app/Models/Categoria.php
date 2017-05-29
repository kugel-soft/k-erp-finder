<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model {
    protected $table = 'categorias';
    
    protected $fillable = [
        'nome',
    ];
}