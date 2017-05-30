<?php

namespace Kugel\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Kugel\Models\Categoria;
use Kugel\Models\ProblemaTabela;
use Kugel\Models\ProblemaTag;
use Kugel\Models\Problema;
use Kugel\Models\Tabela;
use Kugel\Models\Tag;
use Slim\Views\Twig as View;
use Respect\Validation\Validator as v;

class HomeController extends Controller {
    public function index($request, $response) {
        $problemas = Problema::orderBy('updated_at', 'desc')->paginate(5)->appends($request->getParams());
        return $this->view->render($response, 'pesquisa.twig', compact('problemas'));
    }
    
    public function getSobre($request, $response) {
        return $this->view->render($response, 'sobre.twig', compact('problemas'));
    }
    
    public function getNovo($request, $response) {
        return $this->view->render($response, 'novo.twig', compact('problemas'));
    }
    
    public function postNovo($request, $response) {
        $validation = $this->validator->validate($request, [
            'titulo' => v::notEmpty()->stringType()->length(1, 255),
            'tags' => v::notEmpty()->stringType(),
            'situacao' => v::notEmpty()->stringType()->length(10, 1000),
            'solucao' => v::notEmpty()->stringType()->length(10, 1000),
            'criador' => v::notEmpty()->stringType()->length(1, 30),
        ]);
        
        if ($validation->failed()) {
            $this->flash->addMessage('error', 'Dados inválidos!');
            return $response->withRedirect($this->router->pathFor('novo'));
        }
        
        try {
            $this->db->getConnection()->getPdo()->beginTransaction();
        
            // categoria
            $categoriaId = NULL;
            $categoria = Categoria::where('nome', $request->getParam('categoria'))->first();
            if ($categoria) {
                $categoriaId = $categoria->id;
            }
            else {
                $novaCategoria = Categoria::create([
                    'nome' => $request->getParam('categoria')
                ]);
                $categoriaId = $novaCategoria->id;
            }
            
            // problema
            $problema = Problema::create([
                'titulo'       => $request->getParam('titulo'),
                'situacao'     => $request->getParam('situacao'),
                'solucao'      => $request->getParam('solucao'),
                'criador'      => $request->getParam('criador'),
                'categoria_id' => $categoriaId,
            ]);
            
            // tags
            $tags = explode(",", $request->getParam('tags'));
            foreach ($tags as $t) {
                $tag = Tag::where('nome', 'like', '%'.trim($t).'%')->first();
                if (!$tag) {
                    $tag = Tag::create([
                        'nome' => trim($t),
                    ]);
                    
                    // relacionamento
                    $pt = ProblemaTag::create([
                        'problema_id' => $problema->id,
                        'tag_id' => $tag->id,
                    ]);
                }
            }
            
            // tabelas
            $tabelas = explode(",", $request->getParam('tabelas'));
            foreach ($tabelas as $t) {
                $tabela = Tabela::where('nome', 'like', '%'.trim($t).'%')->first();
                if (!$tabela) {
                    $tabela = Tabela::create([
                        'nome' => trim($t),
                    ]);
                    
                    // relacionamento
                    $pt = ProblemaTabela::create([
                        'problema_id' => $problema->id,
                        'tabela_id' => $tabela->id,
                    ]);
                }
            }
            
            $this->db->getConnection()->getPdo()->commit();
            $this->flash->addMessage('success', 'Item adicionado com sucesso!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        catch (Excepion $e) {
            $this->db->getConnection()->getPdo()->rollback();
            $this->flash->addMessage('error', 'Erro ao cadastrar imóvel: ' . $e->getMessage());
        }
    }
    
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
    
    public function getProblema($request, $response) {
        $problema = Problema::find($request->getAttribute('id'));
        return $this->view->render($response, 'problema.twig', compact('problema'));
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
    
    public function getAlterar($request, $response) {
        $id = $request->getAttribute('id');
        $p = Problema::find($id);
        if (!$p) {
            $this->flash->addMessage('error', 'Item não encontrado para alteração!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'alterar.twig', compact('p'));
    }
    
    public function postAlterar($request, $response) {
        $validation = $this->validator->validate($request, [
            'titulo' => v::notEmpty()->stringType()->length(10, 255),
            'situacao' => v::notEmpty()->stringType()->length(10, 1000),
            'solucao' => v::notEmpty()->stringType()->length(10, 1000),
        ]);
        
        if ($validation->failed()) {
            $this->flash->addMessage('error', 'Dados inválidos!');
            return $response->withRedirect($this->router->pathFor('alterar', ['id' => $request->getAttribute('id')]));
        }
        
        try {
            $this->db->getConnection()->getPdo()->beginTransaction();
        
            // problema
            $problema = Problema::find($request->getAttribute('id'));
            if (!$problema) {
                $this->flash->addMessage('error', 'Item não encontrado para alteração!');
                return $response->withRedirect($this->router->pathFor('alterar', ['id' => $request->getAttribute('id')]));
            }
            
            $problema->titulo = $request->getParam('titulo');
            $problema->situacao = $request->getParam('situacao');
            $problema->solucao = $request->getParam('solucao');
            $problema->save();
            
            // tags
            ProblemaTag::where('problema_id', $problema->id)->delete();
            $tags = explode(",", $request->getParam('tags'));
            foreach ($tags as $t) {
                $tag = Tag::where('nome', trim($t))->first();
                if (!$tag) {
                    $tag = Tag::create([
                        'nome' => trim($t),
                    ]);
                }
                
                // relacionamento
                $pt = ProblemaTag::create([
                    'problema_id' => $problema->id,
                    'tag_id' => $tag->id,
                ]);
            }
            // Limpar tags
            //delete from tags where id not in (select tag_id from problemas_tags join problemas on (problemas.id = problemas_tags.problema_id));
            $tagsId = ProblemaTag::join('problemas', 'problemas.id', '=', 'problemas_tags.problema_id')->select(['tag_id'])->get();
            $tagsIn = [];
            foreach ($tagsId as $t) {
                array_push($tagsIn, $t->tag_id);
            }
            $tagsCadastro = Tag::get();
            foreach ($tagsCadastro as $tc) {
                if (!in_array($tc->id, $tagsIn)) {
                    $tc->delete();
                }
            }
            
            // tabelas
            ProblemaTabela::where('problema_id', $problema->id)->delete();
            $tabelas = explode(",", $request->getParam('tabelas'));
            foreach ($tabelas as $t) {
                $tabela = Tabela::where('nome', trim($t))->first();
                if (!$tabela) {
                    $tabela = Tabela::create([
                        'nome' => trim($t),
                    ]);
                }
                
                // relacionamento
                $pt = ProblemaTabela::create([
                    'problema_id' => $problema->id,
                    'tabela_id' => $tabela->id,
                ]);
            }
            
            // Limpar tabelas
            //delete from tabelas where id not in (select tabela_id from problemas_tabelas join problemas on (problemas.id = problemas_tabelas.problema_id));
            $tabelasId = ProblemaTabela::join('problemas', 'problemas.id', '=', 'problemas_tabelas.problema_id')->select(['tabela_id'])->get();
            $tabelasIn = [];
            foreach ($tabelasId as $t) {
                array_push($tabelasIn, $t->tabela_id);
            }
            $tabelasCadastro = Tabela::get();
            foreach ($tabelasCadastro as $tc) {
                if (!in_array($tc->id, $tabelasIn)) {
                    $tc->delete();
                }
            }
            
            $this->db->getConnection()->getPdo()->commit();
            $this->flash->addMessage('success', 'Item alterado com sucesso!');
            return $response->withRedirect($this->router->pathFor('problema', ['id' => $request->getAttribute('id')]));
        }
        catch (Excepion $e) {
            $this->db->getConnection()->getPdo()->rollback();
            $this->flash->addMessage('error', 'Erro ao cadastrar imóvel: ' . $e->getMessage());
        }
    }
    
    public function getExcluir($request, $response) {
        $id = $request->getAttribute('id');
        $p = Problema::find($id);
        if (!$p) {
            $this->flash->addMessage('error', 'Item não encontrado para exclusão!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'excluir.twig', compact('p'));
    }
    
    public function postExcluir($request, $response) {
        $id = $request->getAttribute('id');
        $problema = Problema::find($id);
        if (!$problema) {
            $this->flash->addMessage('error', 'Item não encontrado para exclusão!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        
        try {
            $this->db->getConnection()->getPdo()->beginTransaction();
        
            // tags
            ProblemaTag::where('problema_id', $problema->id)->delete();
            
            // Limpar tags
            //delete from tags where id not in (select tag_id from problemas_tags join problemas on (problemas.id = problemas_tags.problema_id));
            $tagsId = ProblemaTag::join('problemas', 'problemas.id', '=', 'problemas_tags.problema_id')->select(['tag_id'])->get();
            $tagsIn = [];
            foreach ($tagsId as $t) {
                array_push($tagsIn, $t->tag_id);
            }
            $tagsCadastro = Tag::get();
            foreach ($tagsCadastro as $tc) {
                if (!in_array($tc->id, $tagsIn)) {
                    $tc->delete();
                }
            }
            
            // tabelas
            ProblemaTabela::where('problema_id', $problema->id)->delete();
            
            // Limpar tabelas
            //delete from tabelas where id not in (select tabela_id from problemas_tabelas join problemas on (problemas.id = problemas_tabelas.problema_id));
            $tabelasId = ProblemaTabela::join('problemas', 'problemas.id', '=', 'problemas_tabelas.problema_id')->select(['tabela_id'])->get();
            $tabelasIn = [];
            foreach ($tabelasId as $t) {
                array_push($tabelasIn, $t->tabela_id);
            }
            $tabelasCadastro = Tabela::get();
            foreach ($tabelasCadastro as $tc) {
                if (!in_array($tc->id, $tabelasIn)) {
                    $tc->delete();
                }
            }
            
            $problema->delete();
            
            // Limpar categorias
            // select * from categorias where id not in (select distinct categoria_id from problemas);
            $categoriasList = Categoria::get();
            foreach ($categoriasList as $categoriaBd) {
                $deletar = Problema::where('categoria_id', $categoriaBd->id)->count() === 0;
                if ($deletar) {
                    $categoriaBd->delete();
                }
            }
            
            $this->db->getConnection()->getPdo()->commit();
            $this->flash->addMessage('success', 'Item excluído com sucesso!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
        catch (Excepion $e) {
            $this->db->getConnection()->getPdo()->rollback();
            $this->flash->addMessage('error', 'Erro ao cadastrar imóvel: ' . $e->getMessage());
        }
    }
    
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