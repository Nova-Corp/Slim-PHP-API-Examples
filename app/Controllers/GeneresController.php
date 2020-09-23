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
        $all_generes = Generes::get();
        foreach ($all_generes as $genere) {
            $genere->image = Media::where('collection_name', 'genere_image')
            ->where('row_id', $genere->id)
            ->value('filename');
        }
        return $this->toJSON($response, [
            'status' => true,
            'message' => $all_generes
        ], 200);
    }

    public function retriveGenere(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $generes = Generes::leftJoin('media', 'media.row_id', '=', 'generes.id')
        ->select('generes.*', 'media.filename as image')
        ->where('generes.id', $id)
        ->first();
        if (!is_null($generes)) {
            $generes->image = Media::where('collection_name', 'genere_image')
            ->where('row_id', $id)
            ->value('filename');

            return $this->toJSON($response, [
                'status' => true,
                'message' => $generes
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
                $mimeType = array('gif', 'png', 'jpg', 'jpeg');

                if (empty($uploadedFiles->getError())) {
                    if ($this->validateInputMedia($mimeType, $uploadedFiles)) {
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

            if (isset($request->getUploadedFiles()['genere_image'])) {
                $uploadedFiles = $request->getUploadedFiles()['genere_image'];
                $mimeType = array('gif', 'png', 'jpg', 'jpeg');

                if (empty($uploadedFiles->getError())) {
                    if ($this->validateInputMedia($mimeType, $uploadedFiles)) {
                        Generes::where('id', $id)->update($sanitized);
                        $file_info = $this->moveUploadedFile($this->media_path, $uploadedFiles);

                        $mediaData = [
                            'filename' => $file_info['filename'] . '.' . $file_info['extension'],
                            'row_id' => $id,
                            'type' => $file_info['extension'],
                            'table_name' => 'generes',
                            'collection_name' => 'genere_image'
                        ];

                        Media::where(function ($query) use ($id, $mediaData) {
                            $row = $query->where('row_id', $id);
                            if ($row->exists()) {
                                unlink($this->media_path . '/' . $row->value('filename'));
                                $row->update($mediaData);
                            } else {
                                $query->create($mediaData);
                            }
                        });
                        return $this->toJSON($response, [
                            'status' => true,
                            'message' => 'Successfully updated.'
                        ], 200);

                    }else {
                        return $this->toJSON($response, [
                            'status' => false,
                            'message' => 'Please choose valid image.'
                        ], 401);
                    }
                } else {
                    Media::where(function ($query) use ($id) {
                        $row = $query->where('row_id', $id);
                        if ($row->exists()) {
                            unlink($this->media_path . '/' . $row->value('filename'));
                            $row->delete();
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
