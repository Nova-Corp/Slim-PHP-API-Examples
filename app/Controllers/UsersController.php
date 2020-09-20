<?php

/*
    UsersController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Users;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class UsersController extends Helper
{
    function __construct()
    {
        
    }
    public function createUser(Request $request, Response $response)
    {
        $rules =
        [
            'first_name' => [
                'rules' => V::length(3, 30)->alpha(),
                'message' => 'This field must have a length between 6 and 25 characters and contain only letters and digits'
            ],
            'last_name' => [
                'rules' => V::length(1, 30)->alpha(),
                'message' => 'Please enter the lastname.'
            ],
            'email' => [
                'rules' => V::length(6)->email(),
                'message' => 'Please enter valid email.'
            ],
            'password' => [
                'rules' => V::length(6, 25)->noWhitespace()->stringType(),
                'message' => 'Password should contain 6 digit alpha numeric without white space.'
            ],
            'is_admin' => [
                'rules' => V::length(1)->boolVal(),
                'message' => 'Please verify user type.'
            ],
        ];
        
        $val = new Validator();
        $validator = $val->validate($request, $rules);
        
        $data = $request->getParsedBody();

        if ($val->isValid()) {
            $sanitized = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'is_admin' => $data['is_admin'],
            ];
            try {
                Users::create($sanitized);
                return $this->toJSON($response, [
                    'status' => true,
                    'message' => 'Successfully created.'
                ], 200);
            } catch (\Throwable $error) {
                if ($error->errorInfo[1] == 1062) {
                    return $this->toJSON($response, [
                        'status' => true,
                        'message' => 'This email already registered.'
                    ], 200);
                } else {
                    return $this->toJSON($response, [
                        'status' => true,
                        'message' => $error->errorInfo[2]
                    ], 200);
                }
            }
        } else {
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }
    }

    public function loginUser(Request $request, Response $response)
    {
        $rules =
            [
                'email' => [
                    'rules' => V::length(6)->email(),
                    'message' => 'Please enter valid email.'
                ],
                'password' => [
                    'rules' => V::length(6, 25)->noWhitespace()->stringType(),
                    'message' => 'Password should contain 6 digit alpha numeric.'
                ]
            ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        if ($val->isValid()) {
            $data = $request->getParsedBody();
            $user = Users::where('email', $data['email']);
            if (password_verify($data['password'], $user->value('password'))) {
                $token = $this->generateToken($data);
                return $this->toJSON($response, [
                    'status' => true,
                    'message' => 'Successfully loggedin.',
                    'is_admin' => $user->value('is_admin'),
                    'token' => $token
                ], 200);
            } else {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => 'Unauthorised.'
                ], 401);
            }
        } else {
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }
    }
}