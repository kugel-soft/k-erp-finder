<?php

use Kugel\View\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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