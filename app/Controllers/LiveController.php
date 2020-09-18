<?php

namespace Kugel\Controllers;

use Kugel\Models\Categoria;
use Kugel\Models\Problema;

class LiveController extends Controller {
    /*
    * Live search para a pesquisa principal
    */
    public function getLiveSearch($request, $response) {
        $termo = $request->getAttribute('termo');
        
        $problemas = Problema::where('titulo', 'like', '%'.$termo.'%')
            ->orWhere('situacao', 'like', '%'.$termo.'%')
            ->orWhere('solucao', 'like', '%'.$termo.'%')
            ->orderBy('titulo')
            ->get();
            
        if (count($problemas) == 0) {
            return '<ul class="list-group"><li class="list-group-item">Nenhum resultado!</li></ul>';
        }
        else {
            $data = '<ul class="list-group">';
            foreach ($problemas as $p) {
                $data .= '<li class="list-group-item"><a href="/Problema/' . $p->id . '">' . $p->titulo . '</a></li>';
            }
            $data .= '</ul>';
            return $data;
        }
        
    }
    
    /*
    * Live search para a categoria, era pra ser um select, mas ficou um input normal
    */
    public function getLiveSelect($request, $response) {
        $termo = $request->getAttribute('termo');
        
        $categorias = Categoria::where('nome', 'like', '%'.$termo.'%')
            ->orderBy('nome')
            ->get();
            
        if (count($categorias) == 0) {
            return '<ul class="list-group"><li class="list-group-item"><a href="javascript:selectItem(\'' . ucfirst($termo) . '\')">Novo item: ' . ucfirst($termo) . '</a></li></ul>';
        }
        else {
            $data = '<ul class="list-group">';
            foreach ($categorias as $c) {
                $data .= '<li class="list-group-item"><a href="javascript:selectItem(\'' . $c->nome . '\')">' . $c->nome . '</a></li>';
            }
            $data .= '</ul>';
            return $data;
        }
        
    }
}