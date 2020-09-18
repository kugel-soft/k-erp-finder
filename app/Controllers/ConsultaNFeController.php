<?php

namespace Kugel\Controllers;

use Kugel\Models\ContingenciaAtivada;
use Kugel\Models\ContingenciaAgendada;
use Kugel\Models\Informe;
use Kugel\Models\DocumentoDiverso;
use Kugel\Models\DocumentoTecnico;

class ConsultaNFeController extends Controller {
    public function getConfirmContAtiv($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = ContingenciaAtivada::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Item $id em Contingência Ativada marcado como visto!");
        }
        catch (\Excepion $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-nfe'));
    }

    public function getConfirmContAgend($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = ContingenciaAgendada::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Item $id em Contingência Agendada marcado como visto!");
        }
        catch (\Excepion $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-nfe'));
    }

    public function getConfirmInforme($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = Informe::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Item $id em Informes marcado como visto!");
        }
        catch (\Excepion $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-nfe'));
    }

    public function getConfirmDocDiv($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = DocumentoDiverso::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Item $id em Documentos Diversos marcado como visto!");
        }
        catch (\Excepion $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-nfe'));
    }

    public function getConfirmDocTec($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = DocumentoTecnico::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Item $id em Documentos Técnicos marcado como visto!");
        }
        catch (\Excepion $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-nfe'));
    }
}