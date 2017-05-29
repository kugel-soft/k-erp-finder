<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemaTabela extends Model {
    protected $table = 'problemas_tabelas';
    
    protected $fillable = [
        'problema_id',
        'tabela_id',
    ];
}