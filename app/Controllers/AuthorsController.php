<?php

/*
    AuthorsController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Authors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthorsController extends Helper
{
    public function listAuthors(Request $request, Response $response)
    {
        $all_authors = Authors::get();
        return $this->toJSON($response, $all_authors, 200);
    }

    public function retriveAuthor(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $book = Authors::where('id', $id)->first();
        return $this->toJSON($response, $book, 200);
    }

    public function createAuthor(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $sanitized = [
            'name' => $data['name']
        ];
        Authors::create($sanitized);
        return $this->toJSON($response, 'Success', 200);
    }

    public function updateAuthor(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        $sanitized = [
            'name' => $data['name']
        ];
        Authors::where('id', $id)->update($sanitized);
        return $this->toJSON($response, 'Success', 200);
    }

    public function deleteAuthor(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (Authors::where('id', $id)->delete()) {
            return $this->toJSON($response, 'Success', 200);
        } else {
            return $this->toJSON($response, 'Failed', 200);
        }
    }
}
