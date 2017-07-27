<?php

namespace Kugel\Controllers;

use Kugel\Models\NFe;
use Kugel\Models\Problema;

use Kugel\Models\ContingenciaAtivada;
use Kugel\Models\ContingenciaAgendada;
use Kugel\Models\Informe;
use Kugel\Models\DocumentoDiverso;
use Kugel\Models\DocumentoTecnico;

use Kugel\Utils\SefazUtils;

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

    /*
    * Página de consulta NFe na receita
    */
    public function viewConsultaNFe($request, $response) {
        $dataSite = SefazUtils::getConsultaNFe();

        $data = [
            'contAtivList' => [],
            'contAgendList' => [],
            'informeList' => [],
            'docDiversosList' => [],
            'docNotaTecList' => []
        ];

        // ContingenciaAtivada
        foreach ($dataSite['contAtivList'] as $item) {
            $result = ContingenciaAtivada::where('texto', $item)->first();
            if (!$result) {
                $c = ContingenciaAtivada::create([
                    'texto' => $item,
                    'visto' => 'N',
                ]);
                array_push($data['contAtivList'], $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data['contAtivList'], $result);
                }
            }
        }

        // ContingenciaAgendada
        foreach ($dataSite['contAgendList'] as $item) {
            $result = ContingenciaAgendada::where('texto', $item)->first();
            if (!$result) {
                $c = ContingenciaAgendada::create([
                    'texto' => $item,
                    'visto' => 'N',
                ]);
                array_push($data['contAgendList'], $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data['contAgendList'], $result);
                }
            }
        }



        // Informe
        foreach ($dataSite['informeList'] as $item) {
            $result = Informe::where('texto', $item)->first();
            if (!$result) {
                $c = Informe::create([
                    'texto' => $item,
                    'visto' => 'N',
                ]);
                array_push($data['informeList'], $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data['informeList'], $result);
                }
            }
        }

        // DocumentoDiverso
        foreach ($dataSite['docDiversosList'] as $item) {
            $result = DocumentoDiverso::where('texto', $item)->first();
            if (!$result) {
                $c = DocumentoDiverso::create([
                    'texto' => $item,
                    'visto' => 'N',
                ]);
                array_push($data['docDiversosList'], $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data['docDiversosList'], $result);
                }
            }
        }

        // DocumentoTecnico;
        foreach ($dataSite['docNotaTecList'] as $item) {
            $result = DocumentoTecnico::where('texto', $item)->first();
            if (!$result) {
                $c = DocumentoTecnico::create([
                    'texto' => $item,
                    'visto' => 'N',
                ]);
                array_push($data['docNotaTecList'], $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data['docNotaTecList'], $result);
                }
            }
        }

        /* Envia o e-mail com os novos registros */
        $enviarEmail =
            count($data['contAtivList']) > 0 ||
            count($data['contAgendList']) > 0 ||
            count($data['informeList']) > 0 ||
            count($data['docDiversosList']) > 0 ||
            count($data['docNotaTecList']) > 0;

        if ($enviarEmail) {
            // Enviar e-mail
            $email = "ricardo@kugel.com.br";
            $from = "Ricardo Montania <ricardo.montania@gmail.com>";
            $comCopiaPara = "Sigi <sigi@kugel.com.br>; Valdecir <valdecir@kugel.com.br>";
            $assunto = "Novos dados no portal da Sefaz NFe!";
            $destinatario = "Ricardo Montania <$email>";

            $cabecalhos =
                "MIME-Version: 1.0" . "\r\n".
                "Content-type: text/html; charset=utf-8" . "\r\n".
                "To: _DESTINATARIO_ " . "\r\n".
                "From: {$from}" . "\r\n".
                "Cc: {$comCopiaPara}" . "\r\n".
                "Reply-To: {$from}" . "\r\n".
                "X-Mailer: PHP/".phpversion() . "\r\n";

            $cabecalhos = str_replace("_DESTINATARIO_", $destinatario, $cabecalhos);

            $mensagem = '<html><body><p style="font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">Olá!</p>'.
                '<br><p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">Existem novos dados no portal da Sefaz NFe!</p>'.
                '<br>';

            if (count($data['contAtivList']) > 0) {
                $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
                $mensagem .= 'Serviço(s) em contingência ativada:';
                $mensagem .= '<ul>';

                foreach ($data['contAtivList'] as $item) {
                    $mensagem .= '<li>' . $item->texto . '</li>';
                }
                $mensagem .= '</ul></p>';
            }

            if (count($data['contAgendList']) > 0) {
                $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
                $mensagem .= 'Serviço(s) em contingência agendada:';
                $mensagem .= '<ul>';

                foreach ($data['contAgendList'] as $item) {
                    $mensagem .= '<li>' . $item->texto . '</li>';
                }
                $mensagem .= '</ul></p>';
            }

            if (count($data['informeList']) > 0) {
                $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
                $mensagem .= 'Informes:';
                $mensagem .= '<ul>';

                foreach ($data['informeList'] as $item) {
                    $mensagem .= '<li>' . $item->texto . '</li>';
                }
                $mensagem .= '</ul></p>';
            }

            if (count($data['docDiversosList']) > 0) {
                $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
                $mensagem .= 'Documentos :: Diversos:';
                $mensagem .= '<ul>';

                foreach ($data['docDiversosList'] as $item) {
                    $mensagem .= '<li>' . $item->texto . '</li>';
                }
                $mensagem .= '</ul></p>';
            }

            if (count($data['docNotaTecList']) > 0) {
                $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
                $mensagem .= 'Documentos :: Notas Técnicas:';
                $mensagem .= '<ul>';

                foreach ($data['docNotaTecList'] as $item) {
                    $mensagem .= '<li>' . $item->texto . '</li>';
                }
                $mensagem .= '</ul></p>';
            }

            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 20px; color: rgb(33, 33, 33); margin-bottom: 10px;">Atenciosamente,<br></p>';
            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 12px; margin-bottom: 10px;">';
            $mensagem .= '<span style="font-weight: bold; color: rgb(33, 33, 33); display: inline;" class="txt signature_companyname-target sig-hide">Bot Kugel Info!</span>';
            $mensagem .= '<span class="company-sep break" style="display: inline;"></span>';
            $mensagem .= '<br>';
            $mensagem .= '<a class="link signature_website-target sig-hide" target="_blank" href="http://www.secasando.com.br" style="color: rgb(71, 124, 204); text-decoration: none; display: inline;">http://kinfo-kugel.local/ConsultaNFe</a>';
            $mensagem .= '</p></body></html>';

            mail($email, $assunto, $mensagem, $cabecalhos);
        }

        return $this->view->render($response, 'consultanfe.twig', compact('data'));
    }
}