<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Welcome to brand new our library!!!");
    return $response;
});

$container = $app->getContainer();

$container->set('BooksController', new \App\Controllers\BooksController);
$container->set('AuthorsController', new \App\Controllers\AuthorsController);

/*
	All books and details would be listed here.
*/

$app->group('/books', function ($app){
    $app->get('', 'BooksController:listAllBooks');
    $app->get('/{id}', 'BooksController:retriveBook');
    $app->get('/author/{id}', 'BooksController:retriveBookForAuthor');

    $app->post('', 'BooksController:createBooks');
    $app->post('/{id}', 'BooksController:updateBooks');
    $app->delete('/{id}', 'BooksController:deleteBooks');
});

$app->group('/authors', function ($app) {
    $app->get('', 'AuthorsController:listAuthors');
    $app->get('/{id}', 'AuthorsController:retriveAuthor');

    $app->post('', 'AuthorsController:createAuthor');
    $app->post('/{id}', 'AuthorsController:updateAuthor');
    $app->delete('/{id}', 'AuthorsController:deleteAuthor');
});