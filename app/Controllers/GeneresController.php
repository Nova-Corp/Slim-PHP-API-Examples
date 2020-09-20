<?php
/*
    GeneresController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Generes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;
use Exception;

use Psr\Http\Message\UploadedFileInterface;


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
        if (!is_null($genere)) {
            return $this->toJSON($response, [
                'status' => true,
                'message' => $genere
            ], 200);
        } else {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'No genere found.'
            ], 200);
        }
    }

    public function createGenere(Request $request, Response $response, $args)
    {


        // $uploadedFiles = $request->getUploadedFiles();

        // $uploadedFile = $uploadedFiles['genere_image'];
        // if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        //     $filename = $this->moveUploadedFile($this->media_path, $uploadedFile);
        //     return $this->toJSON($response, [
        //         'status' => true,
        //         'message' => $filename.' successfully created.'
        //     ], 200);
        // }else{
        //     return $this->toJSON($response, [
        //         'status' => true,
        //         'message' => 'Not uploaded.'
        //     ], 200);
        // }

        $rules =
        [
            'type' => [
                'rules' => V::stringType()->notEmpty(),
                'message' => 'Please enter the genere type.'
            ]
        ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        // $uploadedFiles = $request->getUploadedFiles();

        // if (empty($uploadedFiles['genere_image'])) {
        //     throw new Exception('Invalid image');
        // }

        // $uploadedFile = $uploadedFiles['genere_image'];

        // $fileValidationResult = V::image()->validate($uploadedFile->getStream());

        // return $this->toJSON($response, [
        //     'status' => true,
        //     'message' => $fileValidationResult
        // ], 200);

        if ($validator->isValid()) {
            $data = $request->getParsedBody();
            $sanitized = [
                'type' => $data['type']
            ];
            Generes::create($sanitized);
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully created.'
            ], 200);
        }else {
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }   
    }

    public function updateGenere(Request $request, Response $response, $args)
    {
        $rules =
        [
            'type' => [
                'rules' => V::stringType()->notEmpty(),
                'message' => 'Please enter the genere type.'
            ]
        ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        if ($validator->isValid()) {
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

        }else {
            $errors = $validator->getErrors();
            foreach ($errors as $error) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => $error[0]
                ], 401);
            }
        }
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
