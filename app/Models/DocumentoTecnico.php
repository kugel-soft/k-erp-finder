<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoTecnico extends Model {
    protected $table = 'documentos_tecnicos';

    protected $fillable = [
        'texto',
        'visto',
    ];
}