<?php

/*
create table vagas (
	id int auto_increment,
	nomeVaga varchar(600) NOT NULL,
	nomeEmpresa varchar(100) NOT NULL,
	tipoVaga varchar(30) NOT NULL,
	miniTextoVaga  varchar(300) NOT NULL,
	dataPublicacao varchar(30) NOT NULL,
	urlVaga varchar(300) NOT NULL,
    visto char(1) not null,
    origem varchar(30) NULL DEFAULT NULL,
	created_at timestamp,
	updated_at timestamp NULL DEFAULT NULL,
	primary key(id)
);
*/

namespace Kugel\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderMail extends Model {
    protected $table = 'header_mail';
    protected $primaryKey  = 'ultimo';
    public $incrementing = false;

    protected $fillable = [
        'ultimo',
    ];
}