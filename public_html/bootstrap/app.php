<?php

session_start();

/*
* Carrega as classes das bibliotecas
*/
require __DIR__ . '/../vendor/autoload.php';

/*
* Carrega o paginador
*/
require __DIR__ . '/../bootstrap/paginator.php';

/*
* Instancia um novo Slim app
*/
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'kugel',
            'username' => 'kugelbot',
            'password' => 'kugel123',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]
    ],
]);

/*
* Adquire o container para injeção de classes
*/
$container = $app->getContainer();

/*
* Injeção do eloquent (acesso ao BD)
*/
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['db'] = function($container) use ($capsule) {
    return $capsule;
};

/*
* Injeção do flash (para enviar mensagem às págians)
*/
$container['flash'] = function($container) {
    return new \Slim\Flash\Messages;
};

/*
* Injeção das tags globalmente para acesso em todas as páginas
*/
$container['tags'] = function($container) {
    return \Kugel\Models\Tag::orderBy('nome')->get();
};

/*
* Injeção das tabelas globalmente para acesso em todas as páginas
*/
$container['tabelas'] = function($container) {
    return \Kugel\Models\Tabela::orderBy('nome')->get();
};

/*
* Injeção das categorias globalmente para acesso em todas as páginas
*/
$container['categorias'] = function($container) {
    return \Kugel\Models\Categoria::orderBy('nome')->get();
};

/*
* Injeção do renderizador de páginas do projeto, neste caso, o Twig
*/
$container['view'] = function($container) {
    $view = \Kugel\View\Factory::getEngine();
    
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    
    /*
    * Adiciona em todas as views o acesso aos itens criados anteriormente
    */
    $view->getEnvironment()->addGlobal('flash', $container->flash);
    $view->getEnvironment()->addGlobal('tags', $container->tags);
    $view->getEnvironment()->addGlobal('tabelas', $container->tabelas);
    $view->getEnvironment()->addGlobal('categorias', $container->categorias);
    
    return $view;
};

/*
* Sobrescrita do tratamento de página não encontrada, para usar uma personalizada
*/
$container['notFoundHandler'] = function($container) {
    return function($request, $response) use ($container) {
        $container->view->render($response, 'errors/404.twig');
        return $response->withStatus(404);
    };
};

/*
* Injeção da biblioteca de validação
*/
$container['validator'] = function($container) {
    return new \Kugel\Validation\Validator;
};

/*
* Adição das classes Middleware para tratamento/interferência nas requisições
*/
$app->add(new \Kugel\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \Kugel\Middleware\OldInputMiddleware($container));

/*
* Usar o trecho a baixo para criar validações personalizadas na biblioteca
*/
//use Respect\Validation\Validator as v;
//v::with('Kugel\\Validation\\Rules\\');

/*
* Rotas do sistema
*/
require __DIR__ . '/../app/routes.php';
