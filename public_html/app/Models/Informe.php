<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class Informe extends Model {
    protected $table = 'informes';

    protected $fillable = [
        'texto',
        'visto',
    ];
}