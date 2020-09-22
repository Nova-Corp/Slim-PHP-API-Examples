<?php
/*
    GeneresController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Generes;
use App\Models\DatabaseSchema\Media;
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
        $all_generes = Generes::join('media', 'media.row_id', '=', 'generes.id', 'full outer')
        // ->select('generes.*', 'media.filename')
        // ->where('media.collection_name', 'genere_image')
        ->get();
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
            $sanitized = [
                'type' => $data['type']
            ];

            if (isset($request->getUploadedFiles()['genere_image'])) {
                $uploadedFiles = $request->getUploadedFiles()['genere_image'];
                $allowed = array('gif', 'png', 'jpg', 'jpeg');

                if (empty($uploadedFiles->getError())) {
                    if ($this->validateInputMedia($allowed, $uploadedFiles)) {
                        $genere = Generes::create($sanitized);
                        $file_info = $this->moveUploadedFile($this->media_path, $uploadedFiles);
                        Media::create([
                            'filename' => $file_info['filename'].'.'. $file_info['extension'],
                            'row_id' => $genere->id,
                            'type' => $file_info['extension'],
                            'table_name' => 'generes',
                            'collection_name' => 'genere_image'
                        ]);
                        return $this->toJSON($response, [
                            'status' => true,
                            'message' => 'Successfully created.'
                        ], 200);
                    } else {
                        return $this->toJSON($response, [
                            'status' => false,
                            'message' => 'Please choose valid image.'
                        ], 401);
                    }
                } 
            }

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
        $filename = Media::where('collection_name', 'genere_image')->where('row_id', $id)->value('filename');
        if (Generes::where('id', $id)->delete()) {
            Media::where('row_id', $id)->delete();
            if (!is_null($filename)) {
                unlink($this->media_path . '/' . $filename);
            }
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
