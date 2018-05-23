<?php

use Kugel\Controllers\ApontamentosController;
use Kugel\Controllers\ConsultaNFeController;
use Kugel\Controllers\ESocialController;
use Kugel\Controllers\LiveController;
use Kugel\Controllers\PesquisaController;
use Kugel\Controllers\ProblemaController;
use Kugel\Controllers\ViewController;

// Views
$app->get('/', ViewController::class . ':viewIndex')->setName('index');
$app->get('/Novo', ViewController::class . ':viewNovo')->setName('novo');

$app->get('/Sobre', ViewController::class . ':viewSobre')->setName('sobre');

$app->get('/Problema/{id}', ViewController::class . ':viewProblema')->setName('problema');
$app->get('/ConsultaNFe', ViewController::class . ':viewConsultaNFe');
$app->get('/ConsultaNFeSE[/{mostrar}]', ViewController::class . ':viewConsultaNFeSE')->setName('consulta-nfe');
$app->get('/ConsultaJessicaSE[/{mostrar}]', ViewController::class . ':viewConsultaJessicaSE')->setName('consulta-jessica');
$app->get('/ConsultaJessica', ViewController::class . ':viewConsultaJessica');
$app->get('/NoticiasESocial', ViewController::class . ':viewNoticiasESocial');
$app->get('/NoticiasESocialSE[/{mostrar}]', ViewController::class . ':viewNoticiasESocialSE')->setName('consulta-esocial');
$app->get('/ConfirmarItem/ContAtiv/{id}', ConsultaNFeController::class . ':getConfirmContAtiv')->setName('conf-cont-ativ');
$app->get('/ConfirmarItem/ContAgend/{id}', ConsultaNFeController::class . ':getConfirmContAgend')->setName('conf-cont-agend');
$app->get('/ConfirmarItem/Informe/{id}', ConsultaNFeController::class . ':getConfirmInforme')->setName('conf-informe');
$app->get('/ConfirmarItem/DocDiv/{id}', ConsultaNFeController::class . ':getConfirmDocDiv')->setName('conf-doc-div');
$app->get('/ConfirmarItem/DocTec/{id}', ConsultaNFeController::class . ':getConfirmDocTec')->setName('conf-doc-tec');
$app->get('/ConfirmarItem/ESocial/{id}', ESocialController::class . ':getConfirmNoticia')->setName('conf-esocial');
$app->get('/Alterar/{id}', ViewController::class . ':viewAlterar')->setName('alterar');
$app->get('/Excluir/{id}', ViewController::class . ':viewExcluir')->setName('excluir');

// Métodos de inclusão/alteração/exclusão
$app->post('/Novo', ProblemaController::class . ':postNovo');
$app->post('/Alterar/{id}', ProblemaController::class . ':postAlterar');
$app->post('/Excluir/{id}', ProblemaController::class . ':postExcluir');

// Pesquisas
$app->get('/Pesquisar', PesquisaController::class . ':getPesquisaGeral')->setName('pesquisar');
$app->get('/Problemas/Tag/{id}', PesquisaController::class . ':getPesquisaTag')->setName('tag');
$app->get('/Problemas/Tabela/{id}', PesquisaController::class . ':getPesquisaTabela')->setName('tabela');
$app->get('/Problemas/Categoria/{id}', PesquisaController::class . ':getPesquisaCategoria')->setName('categoria');

// Live searchs
$app->get ('/livesearch/{termo}', LiveController::class . ':getLiveSearch');
$app->get ('/liveselect/{termo}', LiveController::class . ':getLiveSelect');

// Apontamentos
$app->get ('/Apontamentos[/{codFun}]', ApontamentosController::class . ':viewApontamentos')->setName('apontamentos');
