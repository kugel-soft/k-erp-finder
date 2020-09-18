<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class Tabela extends Model {
    protected $table = 'tabelas';
    
    protected $fillable = [
        'nome',
    ];
}