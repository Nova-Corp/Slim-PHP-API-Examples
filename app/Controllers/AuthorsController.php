<?php

/*
    AuthorsController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Authors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class AuthorsController extends Helper
{
    public function authorList(Request $request, Response $response)
    {
        $all_authors = Authors::get();
        return $this->toJSON($response, [
            'status' => true,
            'message' => $all_authors
        ], 200);
    }

    public function retriveAuthor(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $author = Authors::where('id', $id)->first();
        if (!is_null($author)) {
            return $this->toJSON($response, [
                'status' => true,
                'message' => $author
            ], 200);
        }else {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Author not found.'
            ], 200);
        }
        
    }

    public function createAuthor(Request $request, Response $response, $args)
    {
        $rules =
        [
            'name' => [
                'rules' => V::stringType()->notEmpty(),
                'message' => 'Please enter the author name.'
            ]
        ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        if ($validator->isValid()) {
            $data = $request->getParsedBody();
            $sanitized = [
                'name' => $data['name']
            ];
            Authors::create($sanitized);
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully created.'
            ], 200);
        }else{
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }
    }

    public function updateAuthor(Request $request, Response $response, $args)
    {

        $rules =
        [
            'name' => [
                'rules' => V::stringType()->notEmpty(),
                'message' => 'Please enter the author name.'
            ]
        ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        if ($validator->isValid()) {
            $data = $request->getParsedBody();
            $id = $args['id'];
            $sanitized = [
                'name' => $data['name']
            ];
            Authors::where('id', $id)->update($sanitized);
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully updated.'
            ], 200);
        }else{
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }
    }

    public function deleteAuthor(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (Authors::where('id', $id)->delete()) {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully deleted.'
            ], 200);
        } else {
            return $this->toJSON($response, [
                'status' => false,
                'message' => 'Not Found.'
            ], 200);
        }
    }
}
