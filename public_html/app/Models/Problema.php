<?php

namespace Kugel\Models;

use \DateTime;
use Illuminate\Database\Eloquent\Model;

class Problema extends Model {
    protected $table = 'problemas';

    protected $fillable = [
        'titulo',
        'situacao',
        'solucao',
        'criador',
        'categoria_id',
    ];

    public function getCreatedAtAttribute($input) {
        $dt = new DateTime($input);
        return $dt->format('d/m/Y à\s H:i:s');
    }

    public function getUpdatedAtAttribute($input) {
        $dt = new DateTime($input);
        return $dt->format('d/m/Y à\s H:i:s');
    }

    public function getCriadorAttribute($input) {
        if ($input == 'ademar') return 'Ademar';
        if ($input == 'andre') return 'André';
        if ($input == 'daniel') return 'Daniel';
        if ($input == 'gustavo') return 'Gustavo';
        if ($input == 'izaura') return 'Izaura';
        if ($input == 'renato') return 'Renato';
        if ($input == 'ricardo') return 'Ricardo';
        if ($input == 'rodrigob') return 'Rodrigo de Bona';
        if ($input == 'rodrigoc') return 'Rodrigo Cruz';
        if ($input == 'sieghard') return 'Sieghard';
        if ($input == 'valdecir') return 'Valdecir';
        return 'Usuário não cadastrado';
    }

    public function tagsProblema() {
        return $this->hasMany('Kugel\Models\ProblemaTag', 'problema_id', 'id');
    }

    public function tabelasProblema() {
        return $this->hasMany('Kugel\Models\ProblemaTabela', 'problema_id', 'id');
    }

    public function categoria() {
        return $this->hasOne('Kugel\Models\Categoria', 'id', 'categoria_id');
    }

    public function tags() {
        $list = $this->tagsProblema()->get();
        foreach ($list as $l) {
            $t = Tag::find($l->tag_id);
            $l->nome = $t->nome;
        }
        return $list;
    }

    public function tabelas() {
        $list = $this->tabelasProblema()->get();
        //dump($list);
        foreach ($list as $l) {
            $t = Tabela::find($l->tabela_id);
            $l->nome = $t->nome;
        }
        return $list;
    }

    public function getTagsInput() {
        $r = '';
        $t = $this->tags();
        $c = 0;
        foreach ($t as $i) {
            if ($c > 0) {
                $r .= ', ';
            }
            $r .= $i->nome;
            $c++;
        }
        return $r;
    }

    public function getTabelasInput() {
        $r = '';
        $t = $this->tabelas();
        $c = 0;
        foreach ($t as $i) {
            if ($c > 0) {
                $r .= ', ';
            }
            $r .= $i->nome;
            $c++;
        }
        return $r;
    }

    public function getSituacaoTexto() {
        $s = $this->situacao;
        $s = str_replace('<br>', '', $s);
        if (strlen($s) > 80) {
            return substr($s, 0, 80) . '...';
        }
        return $s;
    }

    public function getSolucaoTexto() {
        $s = $this->solucao;
        $s = str_replace('<br>', '', $s);
        if (strlen($s) > 80) {
            return substr($s, 0, 80) . '...';
        }
        return $s;
    }
    
    public function mostrarBotaoVerMais() {
        return strlen($this->solucao) > 80;
    }
}