<?php

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemaTag extends Model {
    protected $table = 'problemas_tags';
    
    protected $fillable = [
        'problema_id',
        'tag_id',
    ];
}