<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoDiverso extends Model {
    protected $table = 'documentos_diversos';

    protected $fillable = [
        'texto',
        'visto',
    ];
}