<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Welcome to brand new our library!!!");
    return $response;
});

/*
	All books and details would be listed here.
*/

$app->get('/list-all-books', 'BooksController:index');
