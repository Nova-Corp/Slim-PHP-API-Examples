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


class GeneresController extends Helper
{
    public function genereList(Request $request, Response $response)
    {
        $all_generes = Generes::leftJoin('media', 'media.row_id', '=', 'generes.id')
        ->select('generes.*', 'media.filename as image')
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

            $mimeType = array('image/png', 'image/jpg', 'image/gif', 'image/jpeg');
            $collectionName = 'genere_image';

            if (isset($request->getUploadedFiles()[$collectionName])) {

                $uploadedFiles = $request->getUploadedFiles()[$collectionName];

                if (empty($uploadedFiles->getError())) {
                    $file = $this->uploadMedia($collectionName, $mimeType, $response);
                    if ($file['uploaded']) {
                        $generes = Generes::create($sanitized);
                        $mediaData = [
                            'filename' => $file['response']->getNameWithExtension(),
                            'type' => $file['response']->getExtension(),
                            'row_id' => $generes->id,
                            'table_name' => 'generes',
                            'collection_name' => $collectionName
                        ];
                        Media::create($mediaData);
                        return $this->toJSON($response, [
                            'status' => true,
                            'message' => 'Successfully created.'
                        ], 200);
                    } else {
                        return $file['response'];
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

            $mimeType = array('image/png', 'image/jpg', 'image/gif', 'image/jpeg');
            $collectionName = 'genere_image';

            if (isset($request->getUploadedFiles()[$collectionName])) {

                $uploadedFiles = $request->getUploadedFiles()[$collectionName];

                if (empty($uploadedFiles->getError())) {
                    $file = $this->uploadMedia($collectionName, $mimeType, $response);
                    if ($file['uploaded']) {
                        $mediaData = [
                            'filename' => $file['response']->getNameWithExtension(),
                            'type' => $file['response']->getExtension(),
                            'row_id' => $id,
                            'table_name' => 'generes',
                            'collection_name' => $collectionName
                        ];
                        Media::where(function ($query) use ($id, $mediaData) {
                            if ($query->where('row_id', $id)->exists()) {
                                unlink($this->media_path . '/' . $query->where('row_id', $id)->value('filename'));
                                $query->where('row_id', $id)->update($mediaData);
                            }else {
                                $query->create($mediaData);
                            }
                        });
                        Generes::where('id', $id)->update($sanitized);
                        return $this->toJSON($response, [
                            'status' => true,
                            'message' => 'Successfully updated 1.'
                        ], 200);
                    } else {
                        return $file['response'];
                    }
                }else {
                    Media::where(function ($query) use ($id) {
                        if ($query->where('row_id', $id)->exists()) {
                            unlink($this->media_path . '/' . $query->where('row_id', $id)->value('filename'));
                            $query->where('row_id', $id)->delete();
                        }
                    });
                }
            }

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
