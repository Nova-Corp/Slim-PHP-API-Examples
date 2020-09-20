<?php

use App\Helpers\Helper;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([     //	Auth method added. (Ex. Basic-Auth).
    'secure' => false,             //	It will handle the http and https.
    'path' => [
        '/users/create',
        '/users/login'
    ],    // 	Multiple auth route added here. This route will be authenticated by Auth Method. (Ex. 'path' => ['/jwt-auth', 'new-route']).
    'users' => [
        'user' => 'admin',
        $container->get('basic')['username'] => $container->get('basic')['password']    //	This is Basic-Auth username and password structure. Container keys accessed like this.
    ],
    'error' => function ($response, $arguments) {
        $helper = new Helper();
        $data = [
            'status' => 'false',
            'message' => $arguments['message']
        ];
        return $helper->toJSON($response, $data, 401);
    }
]));

$app->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $container->get('secret'),
    'path' => ['/'],
    'ignore' => [
        '/users/create',
        '/users/login'
    ],
    'error' => function ($response, $arguments) {
        $helper = new Helper();
        $data = [
            'status' => 'false',
            'message' => $arguments['message']
        ];
        return $helper->toJSON($response, $data, 401);
    }
]));