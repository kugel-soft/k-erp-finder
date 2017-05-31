<?php

namespace Kugel\Controllers;

use Kugel\Models\Problema;

class ViewController extends Controller {
    /*
    * Página inicial
    */
    public function viewIndex($request, $response) {
        $problemas = Problema::orderBy('updated_at', 'desc')->paginate(5)->appends($request->getParams());
        return $this->view->render($response, 'pesquisa.twig', compact('problemas'));
    }
    
    /*
    * Página sobre
    */
    public function viewSobre($request, $response) {
        return $this->view->render($response, 'sobre.twig', compact('problemas'));
    }
    
    /*
    * Página novo item
    */
    public function viewNovo($request, $response) {
        return $this->view->render($response, 'novo.twig', compact('problemas'));
    }
    
    /*
    * Página de visualização do item
    */
    public function viewProblema($request, $response) {
        $problema = Problema::find($request->getAttribute('id'));
        if (!$problema) {
            $this->flash->addMessage('error', 'Item não encontrado! Tente a pesquisa!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'problema.twig', compact('problema'));
    }
    
    /*
    * Página de alteração do item
    */
    public function viewAlterar($request, $response) {
        $id = $request->getAttribute('id');
        $p = Problema::find($id);
        if (!$p) {
            $this->flash->addMessage('error', 'Item não encontrado para alteração!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'alterar.twig', compact('p'));
    }
    
    /*
    * Página de exclusão do item
    */
    public function viewExcluir($request, $response) {
        $id = $request->getAttribute('id');
        $p = Problema::find($id);
        if (!$p) {
            $this->flash->addMessage('error', 'Item não encontrado para exclusão!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'excluir.twig', compact('p'));
    }
}