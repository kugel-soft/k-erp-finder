<?php

// Views
$app->get('/',
    \Kugel\Controllers\ViewController::class . ':viewIndex')
        ->setName('home');

$app->get('/Sobre',
    \Kugel\Controllers\ViewController::class . ':viewSobre')
        ->setName('sobre');

$app->get('/Novo',
    \Kugel\Controllers\ViewController::class . ':viewNovo')
        ->setName('novo');

$app->get('/Problema/{id}',
    \Kugel\Controllers\ViewController::class . ':viewProblema')
        ->setName('problema');

$app->get('/ConsultaNFe',
    \Kugel\Controllers\ViewController::class . ':viewConsultaNFe')
        ->setName('consulta-nfe');

$app->get('/ConfirmarItem/ContAtiv/{id}',
    \Kugel\Controllers\ConsultaNFeController::class . ':getConfirmContAtiv')
        ->setName('conf-cont-ativ');

$app->get('/ConfirmarItem/ContAgend/{id}',
    \Kugel\Controllers\ConsultaNFeController::class . ':getConfirmContAgend')
        ->setName('conf-cont-agend');

$app->get('/ConfirmarItem/Informe/{id}',
    \Kugel\Controllers\ConsultaNFeController::class . ':getConfirmInforme')
        ->setName('conf-informe');

$app->get('/ConfirmarItem/DocDiv/{id}',
    \Kugel\Controllers\ConsultaNFeController::class . ':getConfirmDocDiv')
        ->setName('conf-doc-div');

$app->get('/ConfirmarItem/DocTec/{id}',
    \Kugel\Controllers\ConsultaNFeController::class . ':getConfirmDocTec')
        ->setName('conf-doc-tec');

$app->get('/Alterar/{id}',
    \Kugel\Controllers\ViewController::class . ':viewAlterar')
        ->setName('alterar');

$app->get('/Excluir/{id}',
    \Kugel\Controllers\ViewController::class . ':viewExcluir')
        ->setName('excluir');

// Métodos de inclusão/alteração/exclusão
$app->post('/Novo',                     \Kugel\Controllers\ProblemaController::class . ':postNovo'   );
$app->post('/Alterar/{id}',             \Kugel\Controllers\ProblemaController::class . ':postAlterar');
$app->post('/Excluir/{id}',             \Kugel\Controllers\ProblemaController::class . ':postExcluir');

// Pesquisas
$app->get('/Pesquisar',                \Kugel\Controllers\PesquisaController::class . ':getPesquisaGeral'    )->setName('pesquisar');
$app->get('/Problemas/Tag/{id}',       \Kugel\Controllers\PesquisaController::class . ':getPesquisaTag'      )->setName('tag');
$app->get('/Problemas/Tabela/{id}',    \Kugel\Controllers\PesquisaController::class . ':getPesquisaTabela'   )->setName('tabela');
$app->get('/Problemas/Categoria/{id}', \Kugel\Controllers\PesquisaController::class . ':getPesquisaCategoria')->setName('categoria');

// Live searchs
$app->get ('/livesearch/{termo}',       \Kugel\Controllers\LiveController::class . ':getLiveSearch');
$app->get ('/liveselect/{termo}',       \Kugel\Controllers\LiveController::class . ':getLiveSelect');
