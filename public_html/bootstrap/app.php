<?php

use Kugel\View\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Respect\Validation\Validator as v;

session_start();

require __DIR__ . '/../vendor/autoload.php';

LengthAwarePaginator::viewFactoryResolver(function() {
    return new Factory;
});

LengthAwarePaginator::defaultView('pagination/bootstrap.twig');

Paginator::currentPathResolver(function() {
    return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
});

Paginator::currentPageResolver(function() {
    return $_GET['page'] ?? 1;
});

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'kugel',
            'username' => 'root',
            'password' => 'admin',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]
    ],
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {
    return $capsule;
};

$container['flash'] = function($container) {
    return new \Slim\Flash\Messages;
};

$container['tags'] = function($container) {
    return \Kugel\Models\Tag::orderBy('nome')->get();
};

$container['tabelas'] = function($container) {
    return \Kugel\Models\Tabela::orderBy('nome')->get();
};

$container['categorias'] = function($container) {
    return \Kugel\Models\Categoria::orderBy('nome')->get();
};

$container['view'] = function($container) {
    $view = Factory::getEngine();
    
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    
    $view->getEnvironment()->addGlobal('flash', $container->flash);
    
    $view->getEnvironment()->addGlobal('tags', $container->tags);
    $view->getEnvironment()->addGlobal('tabelas', $container->tabelas);
    $view->getEnvironment()->addGlobal('categorias', $container->categorias);
    
    return $view;
};

$container['validator'] = function($container) {
    return new \Kugel\Validation\Validator;
};

$container['HomeController'] = function($container) {
    return new \Kugel\Controllers\HomeController($container);
};

$app->add(new \Kugel\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \Kugel\Middleware\OldInputMiddleware($container));

v::with('Kugel\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
