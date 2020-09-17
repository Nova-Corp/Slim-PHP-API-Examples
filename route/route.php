<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Welcome to brand new our library!!!");
    return $response;
});

/*
	All books and details would be listed here.
*/

$app->group('/books', function ($app){
    $app->get('', 'BooksController:listAllBooks');
    $app->get('/{id}', 'BooksController:retriveBook');
    $app->get('/author/{id}', 'BooksController:retriveBookForAuthor');
});

$app->get('/authors', 'BooksController:listAuthors');