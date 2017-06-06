<?php

namespace Kugel\Controllers;

use Kugel\Models\Categoria;
use Kugel\Models\ProblemaTabela;
use Kugel\Models\ProblemaTag;
use Kugel\Models\Problema;
use Kugel\Models\Tabela;
use Kugel\Models\Tag;
use Kugel\Utils\StringUtils;
use Respect\Validation\Validator as v;

class ProblemaController extends Controller {
    /*
    * Método que salva um novo item
    */
    public function postNovo($request, $response) {
        $validation = $this->validator->validate($request, [
            'titulo' => v::notEmpty()->stringType()->length(1, 255),
            'tags' => v::notEmpty()->stringType(),
            'situacao' => v::notEmpty()->stringType()->length(10, 1000),
            'solucao' => v::notEmpty()->stringType()->length(10, 5000),
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
                'titulo'       => trim($request->getParam('titulo')),
                'situacao'     => trim($request->getParam('situacao')),
                'solucao'      => trim($request->getParam('solucao')),
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
            if (!StringUtils::isEmpty($request->getParam('tabelas'))) {
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

    /*
    * Método que salva a alteração de um item
    */
    public function postAlterar($request, $response) {
        $validation = $this->validator->validate($request, [
            'titulo' => v::notEmpty()->stringType()->length(10, 255),
            'situacao' => v::notEmpty()->stringType()->length(10, 1000),
            'solucao' => v::notEmpty()->stringType()->length(10, 5000),
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

            $problema->titulo = trim($request->getParam('titulo'));
            $problema->situacao = trim(preg_replace("/\r\n|\r|\n/", '<br>', $request->getParam('situacao')));
            $problema->solucao = trim(preg_replace("/\r\n|\r|\n/", '<br>', $request->getParam('solucao')));
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
            if (!StringUtils::isEmpty($request->getParam('tabelas'))) {
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

    /*
    * Método que exclui um item
    */
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
}