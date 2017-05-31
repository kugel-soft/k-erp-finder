<?php

namespace Kugel\Controllers;

use Kugel\Models\Categoria;
use Kugel\Models\ProblemaTabela;
use Kugel\Models\ProblemaTag;
use Kugel\Models\Problema;
use Kugel\Models\Tabela;
use Kugel\Models\Tag;

class HomeController extends Controller {
    public function getPesquisar($request, $response) {
        $termo = $request->getParam('termo');
        
        $problemas = Problema::where('titulo', 'like', '%'.$termo.'%')
            ->orWhere('situacao', 'like', '%'.$termo.'%')
            ->orWhere('solucao', 'like', '%'.$termo.'%')
            ->orderBy('titulo')
            ->paginate(5)
            ->appends($request->getParams());
        
        return $this->view->render($response, 'pesquisa.twig', compact('problemas', 'termo'));
    }
    
    public function getProblemasTag($request, $response) {
        $ids = ProblemaTag::where('tag_id', $request->getAttribute('id'))->get(['problema_id']);
        $list = [];
        foreach ($ids as $id) {
            array_push($list, $id->problema_id);
        }
        $problemas = Problema::find($list);
        return $this->view->render($response, 'pesquisa.twig', compact('problemas'));
    }
    
    public function getProblemasTabela($request, $response) {
        $ids = ProblemaTabela::where('tabela_id', $request->getAttribute('id'))->get(['problema_id']);
        $list = [];
        foreach ($ids as $id) {
            array_push($list, $id->problema_id);
        }
        $problemas = Problema::find($list);
        return $this->view->render($response, 'pesquisa.twig', compact('problemas'));
    }
    
    public function getProblemasCategoria($request, $response) {
        $problemas = Problema::where('categoria_id', $request->getAttribute('id'))->get();
        return $this->view->render($response, 'pesquisa.twig', compact('problemas'));
    }
}