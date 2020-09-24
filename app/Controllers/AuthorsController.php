<?php

/*
    AuthorsController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Authors;
use App\Models\DatabaseSchema\Media;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class AuthorsController extends Helper
{
    public function authorList(Request $request, Response $response)
    {
        $all_authors = Authors::get();
        foreach ($all_authors as $author) {
            $author->image = Media::where('collection_name', 'genere_image')
            ->where('row_id', $author->id)
                ->value('filename');
        }
        return $this->toJSON($response, [
            'status' => true,
            'message' => $all_authors
        ], 200);
    }

    public function retriveAuthor(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $author = Authors::leftJoin('media', 'media.row_id', '=', 'author.id')
        ->select('author.*', 'media.filename as image')
        ->where('author.id', $id)
        ->first();
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

            $collectionName = 'author_image';

            if (isset($request->getUploadedFiles()[$collectionName])) {
                $uploadedFiles = $request->getUploadedFiles()[$collectionName];
                $mimeType = array('gif', 'png', 'jpg', 'jpeg');

                if (empty($uploadedFiles->getError())) {
                    if ($this->validateInputMedia($mimeType, $uploadedFiles)) {
                        $author = Authors::create($sanitized);
                        $file_info = $this->moveUploadedFile($this->media_path, $uploadedFiles);

                        Media::create([
                            'filename' => $file_info['filename'] . '.' . $file_info['extension'],
                            'row_id' => $author->id,
                            'type' => $file_info['extension'],
                            'table_name' => (new Authors())->getTable(),
                            'collection_name' => $collectionName
                        ]);

                        return $this->toJSON($response, [
                            'status' => true,
                            'message' => 'Successfully created.'
                        ], 200);
                    }
                }
            }
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

            if (!Authors::where('id', $id)->exists()) {
                return $this->toJSON($response, [
                    'status' => false,
                    'message' => 'No authors found.'
                ], 401);
            }

            $sanitized = [
                'name' => $data['name']
            ];

            $collectionName = 'author_image';

            if (isset($request->getUploadedFiles()[$collectionName])) {
                $uploadedFiles = $request->getUploadedFiles()[$collectionName];
                $mimeType = array('gif', 'png', 'jpg', 'jpeg');

                if (empty($uploadedFiles->getError())) {
                    if ($this->validateInputMedia($mimeType, $uploadedFiles)) {
                        Authors::where('id', $id)->update($sanitized);
                        $file_info = $this->moveUploadedFile($this->media_path, $uploadedFiles);

                        $mediaData = [
                            'filename' => $file_info['filename'] . '.' . $file_info['extension'],
                            'row_id' => $id,
                            'type' => $file_info['extension'],
                            'table_name' => (new Authors())->getTable(),
                            'collection_name' => $collectionName
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
                    } else {
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
        $filename = Media::where('collection_name', 'genere_image')->where('row_id', $id)->value('filename');
        if (Authors::where('id', $id)->delete()) {
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
                'status' => false,
                'message' => 'Not Found.'
            ], 200);
        }
    }
}
