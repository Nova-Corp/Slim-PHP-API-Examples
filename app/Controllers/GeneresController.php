<?php
/*
    GeneresController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Generes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GeneresController extends Helper
{
    public function genereList(Request $request, Response $response)
    {
        $all_generes = Generes::get();
        return $this->toJSON($response, [
            'status' => true,
            'message' => $all_generes
        ], 200);
    }

    public function retriveGenere(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $genere = Generes::where('id', $id)->first();
        return $this->toJSON($response, [
            'status' => true,
            'message' => $genere
        ], 200);
    }

    public function createGenere(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $sanitized = [
            'type' => $data['type']
        ];
        Generes::create($sanitized);
        return $this->toJSON($response, [
            'status' => true,
            'message' => 'Successfully created.'
        ], 200);
    }

    public function updateGenere(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        $sanitized = [
            'type' => $data['type']
        ];
        Generes::where('id', $id)->update($sanitized);
        return $this->toJSON($response, [
            'status' => true,
            'message' => 'Successfully updated.'
        ], 200);
    }

    public function deleteGenere(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (Generes::where('id', $id)->delete()) {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully deleted.'
            ], 200);
        } else {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Not found.'
            ], 200);
        }
    }
}
