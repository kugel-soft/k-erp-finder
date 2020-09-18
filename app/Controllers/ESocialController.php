<?php

namespace Kugel\Controllers;

use Kugel\Models\ESocial;

class ESocialController extends Controller {
    public function getConfirmNoticia($request, $response) {
        try {
            $id = $request->getAttribute('id');

            $c = ESocial::find($id);
            if ($c) {
                $c->visto = 'S';
                $c->save();
            }

            $this->flash->addMessage('success', "Notícia eSocial $id marcada como visto!");
        }
        catch (\Exception $e) {
            $this->flash->addMessage('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
        return $response->withRedirect($this->router->pathFor('consulta-esocial'));
    }
}