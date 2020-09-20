<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Welcome to brand new our library!!!");
    return $response;
});

$container->set('BooksController', new \App\Controllers\BooksController);
$container->set('AuthorsController', new \App\Controllers\AuthorsController);
$container->set('GeneresController', new \App\Controllers\GeneresController);
$container->set('StocksController', new \App\Controllers\StocksController);
$container->set('SalesController', new \App\Controllers\SalesController);
$container->set('UsersController', new \App\Controllers\UsersController);

/*
	All books and details would be listed here.
*/

$app->group('/books', function ($app){
    $app->get('', 'BooksController:bookList');
    $app->get('/{id}', 'BooksController:retriveBook');
    $app->get('/author/{id}', 'BooksController:retriveBookForAuthor');

    $app->post('', 'BooksController:createBooks');
    $app->post('/{id}', 'BooksController:updateBooks');
    $app->delete('/{id}', 'BooksController:deleteBooks');
});

$app->group('/authors', function ($app) {
    $app->get('', 'AuthorsController:authorList');
    $app->get('/{id}', 'AuthorsController:retriveAuthor');

    $app->post('', 'AuthorsController:createAuthor');
    $app->post('/{id}', 'AuthorsController:updateAuthor');
    $app->delete('/{id}', 'AuthorsController:deleteAuthor');
});

$app->group('/generes', function ($app) {
    $app->get('', 'GeneresController:genereList');
    $app->get('/{id}', 'GeneresController:retriveGenere');

    $app->post('', 'GeneresController:createGenere');
    $app->post('/{id}', 'GeneresController:updateGenere');
    $app->delete('/{id}', 'GeneresController:deleteGenere');
});

$app->group('/stocks', function ($app) {
    $app->get('', 'StocksController:stockList');
    $app->get('/{id}', 'StocksController:retriveStock');

    $app->post('/{id}', 'StocksController:updateStock');
});

$app->group('/sales', function ($app) {
    $app->post('', 'SalesController:createSales');
    // $app->get('/{id}', 'SalesController:retriveStock');
});

$app->group('/users', function ($app) {
    $app->post('/create', 'UsersController:createUser');
    $app->post('/login', 'UsersController:loginUser');

});