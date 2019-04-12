<?php

namespace Kugel\Controllers;

use Kugel\Models\NFe;
use Kugel\Models\Problema;

use Kugel\Models\ContingenciaAtivada;
use Kugel\Models\ContingenciaAgendada;
use Kugel\Models\HeaderMail;
use Kugel\Models\Informe;
use Kugel\Models\DocumentoDiverso;
use Kugel\Models\DocumentoTecnico;
use Kugel\Models\ESocial;
use Kugel\Models\Vaga;

use Kugel\Utils\ESocialUtils;
use Kugel\Utils\SefazUtils;
use Kugel\Utils\VagasUtils;

class ViewController extends Controller {
    /*
    * Página inicial
    */
    public function viewIndex($request, $response) {
        $problemas = Problema::orderBy('updated_at', 'desc')->paginate(5)->appends($request->getParams());
        return $this->view->render($response, 'index.twig', compact('problemas'));
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
            return $response->withRedirect($this->router->pathFor('index'));
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
            return $response->withRedirect($this->router->pathFor('index'));
        }
        if (strpos($p->situacao, "<br>") !== FALSE) {
            $p->situacao = str_replace("<br>", "\r\n", $p->situacao);
        }
        if (strpos($p->solucao, "<br>") !== FALSE) {
            $p->solucao = str_replace("<br>", "\r\n", $p->solucao);
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
            return $response->withRedirect($this->router->pathFor('index'));
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
                    'visto' => 'S',
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
                    'visto' => 'S',
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
                    'texto' => $item['texto'],
                    'visto' => 'S',
                    'endereco' => $item['endereco'],
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
                    'visto' => 'S',
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
                    'visto' => 'S',
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
                    $mensagem .= '<li><a target="_blank" href="' . $item->endereco . '">' . $item->texto . '</a></li>';
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
            $mensagem .= '</p></body></html>';

            mail($email, $assunto, $mensagem, $cabecalhos);
        }

        //$dataSite = SefazUtils::getConsultaMDFe();

        return "OK";
    }

    public function viewNoticiasESocialSE($request, $response) {
        $dataSite = ESocialUtils::getNoticias();
        $data = [];
        $mostrar = $request->getAttribute('mostrar');

        if ($mostrar == '') {
            $mostrar = 'naovistos';
        }

        foreach ($dataSite as $item) {
            $result = ESocial::where('url', $item['url'])->first();
            if (!$result) {
                if (strlen($item['title']) > 100) {
                    $item['title'] = substr($item['title'], 0, 100);
                }
                $c = ESocial::create([
                    'titulo'       => $item['title'],
                    'url'          => $item['url'],
                    'texto_url'    => $item['url_text'],
                    'descricao'    => $item['description'],
                    'publicado_em' => $item['when'],
                    'publicado_as' => $item['at'],
                    'visto'        => 'N',
                ]);
                array_push($data, $c);
            }
            else {
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data, $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
                    array_push($data, $result);
                }
            }
        }

        return $this->view->render($response, 'consultaesocial.twig', compact('data'));
    }

    public function viewConsultaJessicaSE($request, $response) {
        $dataSite = VagasUtils::getVagas();

        /*
        if (TRUE) {
            return $response->withJson($dataSite);
        }
        */

        $data = [];
        $mostrar = $request->getAttribute('mostrar');

        if ($mostrar == '') {
            $mostrar = 'naovistos';
        }

        foreach ($dataSite as $item) {
            $result = Vaga::where('urlVaga', $item['urlVaga'])->first();
            if (!$result) {
                try {
                    $v = Vaga::create([
                        'nomeVaga'       => $item['nomeVaga'],
                        'nomeEmpresa'    => $item['nomeEmpresa'],
                        'tipoVaga'       => $item['tipoVaga'],
                        'miniTextoVaga'  => $item['miniTextoVaga'],
                        'dataPublicacao' => $item['dataPublicacao'],
                        'urlVaga'        => $item['urlVaga'],
                        'visto'          => 'N',
                    ]);
                    array_push($data, $v);
                }
                catch (\Illuminate\Database\QueryException $e) {
                    $v = Vaga::create([
                        'nomeVaga'       => $item['nomeVaga'],
                        'nomeEmpresa'    => $item['nomeEmpresa'],
                        'tipoVaga'       => $item['tipoVaga'],
                        'miniTextoVaga'  => utf8_encode($item['miniTextoVaga']),
                        'dataPublicacao' => $item['dataPublicacao'],
                        'urlVaga'        => $item['urlVaga'],
                        'visto'          => 'N',
                    ]);
                    array_push($data, $v);
                }
            }
            else {
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data, $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
                    array_push($data, $result);
                }
            }
        }

        return $this->view->render($response, 'consultajessica.twig', compact('data'));
    }

    public function viewConsultaJessica($request, $response) {
        $dataSite = VagasUtils::getVagas();

        $data = [];
        $mostrar = $request->getAttribute('mostrar');

        if ($mostrar == '') {
            $mostrar = 'naovistos';
        }

        foreach ($dataSite as $item) {
            $result = Vaga::where('urlVaga', $item['urlVaga'])->first();
            if (!$result) {
                try {
                    $v = Vaga::create([
                        'nomeVaga'       => $item['nomeVaga'],
                        'nomeEmpresa'    => $item['nomeEmpresa'],
                        'tipoVaga'       => $item['tipoVaga'],
                        'miniTextoVaga'  => $item['miniTextoVaga'],
                        'dataPublicacao' => $item['dataPublicacao'],
                        'urlVaga'        => $item['urlVaga'],
                        'visto'          => 'S',
                        'origem'         => $item['origem'],
                    ]);
                    $v->isRH = $item['isRH'];
                    array_push($data, $v);
                }
                catch (\Illuminate\Database\QueryException $e) {
                    die('Erro no SQL!');
                }
                
            }
            else {
                $result->isRH = $item['isRH'];
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data, $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
                    array_push($data, $result);
                }
            }
        }

        /* Envia o e-mail com os novos registros */
        $enviarEmailJessica = FALSE;
        $enviarEmailGerson = FALSE;

        foreach ($data as $item) {
            if ($item->isRH) {
                $enviarEmailGerson = TRUE;
            }
            else {
                $enviarEmailJessica = TRUE;
            }
        }

        if ($enviarEmailJessica) {
            // Enviar e-mail
            $email = "Cláudia Campos <camposdellagrazia@gmail.com>";
            $from = "Vagas Por E-mail <vagasporemail@gmail.com>";
            $comCopiaPara = "Cláudia Montania <lotusmontania@gmail.com>, Ricardo Campos <ricardompcampos@gmail.com>";
            $assunto = "Novos anúncios de vagas de emprego!";

            $cabecalhos =
                "MIME-Version: 1.0" . "\r\n".
                "Content-type: text/html; charset=utf-8" . "\r\n".
                "To: _DESTINATARIO_ " . "\r\n".
                "From: {$from}" . "\r\n".
                "Cc: {$comCopiaPara}" . "\r\n".
                "Reply-To: {$from}" . "\r\n".
                "X-Mailer: PHP/".phpversion() . "\r\n";

            $cabecalhos = str_replace("_DESTINATARIO_", $email, $cabecalhos);

            // imagens
            $seqImg = 0;
            $hm = HeaderMail::first();
            if (!$hm) {
                $seqImg++;
                $hm = HeaderMail::create([
                    'ultimo' => $seqImg
                ]);
            } else {
                $hm->ultimo = $hm->ultimo + 1;
                if ($hm->ultimo == 10) {
                    $hm->ultimo = 1;
                }
                $hm->save();
                $seqImg = $hm->ultimo;
            }
            $imageHeader = 'header' . $seqImg . '.jpg';
            $logos = [
                'Joinville Vagas' => 'joinvillevagas.png',
                'SINE Joinville' => 'sine.png',
                'Indeed' => 'indeed.png',
                'Info Jobs' => 'infojobs.png',
            ];

            $mensagem  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
            $mensagem .= '<html><head>';
            $mensagem .= '<meta name="viewport" content="width=device-width"/>';
            $mensagem .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
            $mensagem .= '<title>Vagas por E-mail</title>';
            $mensagem .= '<style>';
            $mensagem .= '* {margin:0; padding:0;}';
            $mensagem .= '* {font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;}';
            $mensagem .= 'img {max-width: 100%;}';
            $mensagem .= '.collapse {margin:0;padding:0;}';
            $mensagem .= 'body {-webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%;}';
            $mensagem .= 'a {color: #2BA6CB;}';
            $mensagem .= '.btn {text-decoration:none;color: #FFF;background-color: #666;padding:10px 16px;font-weight:bold;margin-right:10px;text-align:center;cursor:pointer;display: inline-block;}';
            $mensagem .= 'p.callout {padding:15px;background-color:#ECF8FF;margin-bottom: 15px;}';
            $mensagem .= '.callout a {font-weight:bold;color: #2BA6CB;}';
            $mensagem .= 'table.social {background-color: #ebebeb;}';
            $mensagem .= '.social .soc-btn {padding: 3px 7px;font-size:12px;margin-bottom:10px;text-decoration:none;color: #FFF;font-weight:bold;display:block;text-align:center;}';
            $mensagem .= 'a.fb { background-color: #3B5998!important; }';
            $mensagem .= 'a.tw { background-color: #1daced!important; }';
            $mensagem .= 'a.gp { background-color: #DB4A39!important; }';
            $mensagem .= 'a.ms { background-color: #000!important; }';
            $mensagem .= '.sidebar .soc-btn {display:block;width:100%;}';
            $mensagem .= 'table.head-wrap { width: 100%;}';
            $mensagem .= '.header.container table td.logo { padding: 15px; }';
            $mensagem .= '.header.container table td.label { padding: 15px; padding-left:0px;}';
            $mensagem .= 'table.body-wrap { width: 100%;}';
            $mensagem .= 'table.footer-wrap { width: 100%;clear:both!important;}';
            $mensagem .= '.footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}';
            $mensagem .= '.footer-wrap .container td.content p {font-size:10px;font-weight: bold;}';
            $mensagem .= 'h1,h2,h3,h4,h5,h6 {font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;}';
            $mensagem .= 'h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }';
            $mensagem .= 'h1 {font-weight:200; font-size: 44px;}';
            $mensagem .= 'h2 {font-weight:200; font-size: 37px;}';
            $mensagem .= 'h3 {font-weight:500; font-size: 27px;}';
            $mensagem .= 'h4 {font-weight:500; font-size: 23px;}';
            $mensagem .= 'h5 {font-weight:900; font-size: 17px;}';
            $mensagem .= 'h6 {font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}';
            $mensagem .= '.collapse { margin:0!important;}';
            $mensagem .= 'p, ul { margin-bottom: 10px; font-weight: normal; font-size:14px; line-height:1.6;}';
            $mensagem .= 'p.lead { font-size:17px; }';
            $mensagem .= 'p.last { margin-bottom:0px;}';
            $mensagem .= 'ul li {margin-left:5px;list-style-position: inside;}';
            $mensagem .= 'ul.sidebar {background:#ebebeb; display:block;list-style-type: none;}';
            $mensagem .= 'ul.sidebar li { display: block; margin:0;}';
            $mensagem .= 'ul.sidebar li a {text-decoration:none;color: #666;padding:10px 16px;margin-right:10px;cursor:pointer;border-bottom: 1px solid #777777;border-top: 1px solid #FFFFFF;display:block;margin:0;}';
            $mensagem .= 'ul.sidebar li a.last { border-bottom-width:0px;}';
            $mensagem .= 'ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}';
            $mensagem .= '.container {display:block!important;max-width:600px!important;margin:0 auto!important; clear:both!important;}';
            $mensagem .= '.content {padding:15px;max-width:600px;margin:0 auto;display:block;}';
            $mensagem .= '.content {padding:15px;max-width:600px;margin:0 auto;display:block;}';
            $mensagem .= '.column {width: 300px;float:left;}';
            $mensagem .= '.column tr td { padding: 15px; }';
            $mensagem .= '.column-wrap { padding:0!important; margin:0 auto; max-width:600px!important;}';
            $mensagem .= '.column table { width:100%;}';
            $mensagem .= '.social .column {width: 280px;min-width: 279px;float:left;}';
            $mensagem .= '.clear { display: block; clear: both; }';
            $mensagem .= '@media only screen and (max-width: 600px) {';
            $mensagem .= 'a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}';
            $mensagem .= 'div[class="column"] { width: auto!important; float:none!important;}';
            $mensagem .= 'table.social div[class="column"] {width:auto!important;}';
            $mensagem .= '}';
            $mensagem .= '</style></head>';
            $mensagem .= '<body bgcolor="#FFFFFF" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">';
            $mensagem .= '<table class="head-wrap" bgcolor="#999999">';
            $mensagem .= '<tr>';
            $mensagem .= '<td></td>';
            $mensagem .= '<td class="header container">';
            $mensagem .= '<div class="content">';
            $mensagem .= '<table bgcolor="#999999">';
            $mensagem .= '<tr>';
            $mensagem .= '<td><img src="https://vagas-por-email.000webhostapp.com/images/logo.png"/></td>';
            $mensagem .= '<td align="right"><h6 class="collapse">Novas vagas!</h6></td>';
            $mensagem .= '</tr></table></div></td><td></td></tr></table>';
            $mensagem .= '<table class="body-wrap"><tr><td></td><td class="container" bgcolor="#FFFFFF">';
            $mensagem .= '<div class="content"><table><tr><td>';
            $mensagem .= '<h1>Coleta das ' . date('H') . 'hs</h1>';
            $mensagem .= '<p><img src="https://vagas-por-email.000webhostapp.com/images/'.$imageHeader.'"/></p>';
            $mensagem .= '<p>Estas são as vagas encontradas, recém publicadas!</p>';
            $mensagem .= '</td></tr></table></div>';

            foreach ($data as $item) {
                if ($item->isRH) continue;
                $mensagem .= '<div class="content"><table>';
                $mensagem .= '<tr><td class="small" width="20%" style="vertical-align: top; padding-right:10px;">';
                
                $logoOrigem = 'joinvillevagas.png';
                if (isset($logos[$item->origem])) {
                    $logoOrigem = $logos[$item->origem];
                }
                
                $mensagem .= '<img src="https://vagas-por-email.000webhostapp.com/images/'.$logoOrigem.'" alt="Vaga encontrada no site '.$item->origem.'"/>';
                $mensagem .= '</td><td>';
                $mensagem .= '<h4>' . $item->nomeVaga . '</h4>';

                $txtVaga = $item->miniTextoVaga;
                if ($item->nomeEmpresa != 'Não informado') {
                    $txtVaga .= ' Empresa: ' . $item->nomeEmpresa;
                }
                if ($item->dataPublicacao != 'Não informado') {
                    $txtVaga .= ' Publicado em: ' . $item->dataPublicacao;
                }

                $mensagem .= '<p>' . $txtVaga . '</p>';
                $mensagem .= '<a href="'.$item->urlVaga.'" target="_blank" title="Abrir link no navegador" class="btn">Ver vaga &raquo;</a>';
                $mensagem .= '</td></tr></table></div>';
            }
            $mensagem .= '</ul></p>';

            $mensagem .= '<div class="content"><table><tr><td><table class="social" width="100%">';
            $mensagem .= '<tr><td><div class="column"><table align="left"><tr><td>';
            $mensagem .= '<h5>Conecte-se conosco:</h5><p>';
            $mensagem .= '<a href="https://www.facebook.com/profile.php?id=100028102323218" target="_blank" class="soc-btn fb">Facebook</a>';
            $mensagem .= '<a href="https://www.linkedin.com/in/montania/" target="_blank" class="soc-btn tw">Linkedin</a>';
            $mensagem .= '</p></td></tr></table></div><div class="column"><table align="left"><tr>';
            $mensagem .= '<td><h5>Contato:</h5><p>Fone: <strong>(47) 99169-9982</strong><br/>';
            $mensagem .= 'Email: <strong><a href="emailto:vagasporemail@gmail.com">vagasporemail@gmail.com</a></strong>';
            $mensagem .= '</p></td></tr></table></div><div class="clear"></div></td></tr></table>';
            $mensagem .= '</td></tr></table></div></td><td></td></tr></table></body></html>';

            mail($email, $assunto, $mensagem, $cabecalhos);
        }

        if ($enviarEmailGerson && FALSE) {
            // Enviar e-mail
            $email = "Ricardo Campos <ricardompcampos@gmail.com>";
            $from = "Ricardo Montania <ricardo.montania@gmail.com>";
            $comCopiaPara = "Geiso <gjrmacedo@gmail.com>";
            $assunto = "Novas vagas de RH!";

            $cabecalhos =
                "MIME-Version: 1.0" . "\r\n".
                "Content-type: text/html; charset=utf-8" . "\r\n".
                "To: _DESTINATARIO_ " . "\r\n".
                "From: {$from}" . "\r\n".
                "Cc: {$comCopiaPara}" . "\r\n".
                "Reply-To: {$from}" . "\r\n".
                "X-Mailer: PHP/".phpversion() . "\r\n";

            $cabecalhos = str_replace("_DESTINATARIO_", $email, $cabecalhos);

            $mensagem = '<html><body><p style="font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">Fala Geiso!</p>'.
                '<br><p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">Encontrei novas vagas de trabalho que podem servir!</p>'.
                '<br>';

            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
            $mensagem .= 'São elas:';
            $mensagem .= '<ul>';

            foreach ($data as $item) {
                if (!$item->isRH) continue;
                $mensagem .= '<li><a target="_blank" href="' . $item->urlVaga . '">' . $item->nomeVaga . '</a> ('.$item->origem.') - Empresa: '. $item->nomeEmpresa .', Tipo da vaga: '. $item->tipoVaga .', Descrição: '. $item->miniTextoVaga .', Publicado em: '. $item->dataPublicacao .'</li>';
            }
            $mensagem .= '</ul></p>';

            // Lista dos sites buscados
            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">';
            $mensagem .= 'Sites consultados:';
            $mensagem .= '<li><a target="_blank" href="https://www.joinvillevagas.com.br/">Joinville Vagas</a></li>';
            $mensagem .= '<li><a target="_blank" href="https://www.sine.com.br/vagas-empregos-em-joinville-sc">SINE Joinville</a></li>';
            $mensagem .= '<li><a target="_blank" href="https://www.indeed.com.br/empregos?q=&l=Joinville%2C+SC"Indeed</a></li>';
            $mensagem .= '<li><a target="_blank" href="https://www.infojobs.com.br/empregos-em-joinville,-sc.aspx"Infojobs</a></li>';
            $mensagem .= '<li><a target="_blank" href="https://www.rhbrasil.com.br/site/vagas_unidade.php?cd_empresa=1&vagas=170&titulo=VAGAS+EM:+JOINVILLE">RH Brasil</a></li>';
            $mensagem .= '<li><a target="_blank" href="https://www.manager.com.br/empregos-cidade-joinville-sc-123-4">Manager</a></li>';
            $mensagem .= '</ul></p>';

            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 20px; color: rgb(33, 33, 33); margin-bottom: 10px;">Atenciosamente,<br></p>';
            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 12px; margin-bottom: 10px;">';
            $mensagem .= '<span style="font-weight: bold; color: rgb(33, 33, 33); display: inline;" class="txt signature_companyname-target sig-hide">Bot Caça Vagas do Ricardo!</span>';
            $mensagem .= '<span class="company-sep break" style="display: inline;"></span>';
            $mensagem .= '</p></body></html>';

            mail($email, $assunto, $mensagem, $cabecalhos);
        }

        return "OK";
    }

    public function viewTest($request, $response) {
        // Enviar e-mail
        $email = "Ricardo Campos <ricardo@kugel.com.br>";
        $from = "Vagas Por E-mail <vagasporemail@gmail.com>";
        $comCopiaPara = "Ricardo Campos <ricardompcampos@gmail.com>";
        $assunto = "Novos anúncios de vagas de emprego!";

        $cabecalhos =
            "MIME-Version: 1.0" . "\r\n".
            "Content-type: text/html; charset=utf-8" . "\r\n".
            "To: _DESTINATARIO_ " . "\r\n".
            "From: {$from}" . "\r\n".
            "Cc: {$comCopiaPara}" . "\r\n".
            "Reply-To: {$from}" . "\r\n".
            "X-Mailer: PHP/".phpversion() . "\r\n";

        $cabecalhos = str_replace("_DESTINATARIO_", $email, $cabecalhos);

        $mensagem = "<html>";
        $mensagem .= "<body>";
        $mensagem .= "<h1>Teste ok</h1>";
        $mensagem .= "</body>";
        $mensagem .= "</html>";

        if (mail($email, $assunto, $mensagem, $cabecalhos)) {
            return $response->withJson("OK");
        } else {
            return $response->withJson("Oops");
        }
    }

    public function viewNoticiasESocial($request, $response) {
        $dataSite = ESocialUtils::getNoticias();
        $data = [];
        $mostrar = $request->getAttribute('mostrar');

        if ($mostrar == '') {
            $mostrar = 'naovistos';
        }

        foreach ($dataSite as $item) {
            $result = ESocial::where('url', $item['url'])->first();
            if (!$result) {
                $c = ESocial::create([
                    'titulo'       => $item['title'],
                    'url'          => $item['url'],
                    'texto_url'    => $item['url_text'],
                    'descricao'    => $item['description'],
                    'publicado_em' => $item['when'],
                    'publicado_as' => $item['at'],
                    'visto'        => 'S',
                ]);
                array_push($data, $c);
            }
            else {
                if ($result->visto == 'N') {
                    array_push($data, $result);
                }
            }
        }

        /* Envia o e-mail com os novos registros */
        $enviarEmail = count($data) > 0;

        if ($enviarEmail) {
            $email = "ricardo@kugel.com.br";
            $from = "Ricardo Montania <ricardo.montania@gmail.com>";
            $comCopiaPara = "Sigi <sigi@kugel.com.br>";
            $assunto = "Novas noticias no portal eSocial!";
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

            $mensagem =
                '<html>'.
                '  <body>'.
                '   <p style="font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">'.
                '     Olá!'.
                '   </p>'.
                '   <br>'.
                '   <p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 12px; color: rgb(33, 33, 33); margin-bottom: 10px;">'.
                '     Novas notícias foram publicadas no portal do eSocial!'.
                '   </p>'.
                '   <br>';

                foreach ($data as $item) {
                    $mensagem .=
                '   <p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: rgb(33, 33, 33); margin-bottom: 10px;">'.
                '     Título: ' . (empty($item->titulo)? 'Não informado' : $item->titulo) . '<br>'.
                '     URL: <a target="_blank" href="'.$item->url.'">'.$item->texto_url.'</a><br>'.
                '     Descrição: ' . $item->descricao . '<br>'.
                '     Publicado em: ' . $item->publicado_em . ' às ' . $item->publicado_as .
                '   </p>';
                }

            // Rodapé
            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 20px; color: rgb(33, 33, 33); margin-bottom: 10px;">Atenciosamente,<br></p>';
            $mensagem .= '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 12px; margin-bottom: 10px;">';
            $mensagem .= '<span style="font-weight: bold; color: rgb(33, 33, 33); display: inline;" class="txt signature_companyname-target sig-hide">Bot Kugel Info!</span>';
            $mensagem .= '<span class="company-sep break" style="display: inline;"></span>';
            $mensagem .= '</p></body></html>';

            mail($email, $assunto, $mensagem, $cabecalhos);
        }

        return "OK";
    }

    public function viewConsultaNFeSE($request, $response) {
        $dataSite = SefazUtils::getConsultaNFe();
        $mostrar = $request->getAttribute('mostrar');

        if ($mostrar == '') {
            $mostrar = 'naovistos';
        }

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
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data['contAtivList'], $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
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
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data['contAgendList'], $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
                    array_push($data['contAgendList'], $result);
                }
            }
        }

        // Informe
        foreach ($dataSite['informeList'] as $item) {
            $result = Informe::where('texto', $item['texto'])->first();
            if (!$result) {
                $c = Informe::create([
                    'texto' => $item['texto'],
                    'visto' => 'N',
                    'endereco' => $item['endereco'],
                ]);
                array_push($data['informeList'], $c);
            }
            else {
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data['informeList'], $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
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
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data['docDiversosList'], $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
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
                if ($mostrar == 'vistos' && $result->visto == 'S') {
                    array_push($data['docNotaTecList'], $result);
                }
                else if ($mostrar == 'naovistos' && $result->visto == 'N') {
                    array_push($data['docNotaTecList'], $result);
                }
            }
        }

        return $this->view->render($response, 'consultanfe.twig', compact('data'));
    }
}
