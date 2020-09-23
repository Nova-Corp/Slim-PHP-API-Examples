<?php

/**
 * BooksController.php
 */

namespace App\Controllers;

use App\Models\DatabaseSchema\Books;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Media;
use App\Models\DatabaseSchema\Stocks;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class BooksController extends Helper
{
	public function bookList(Request $request, Response $response)
	{
		$all_books = Books::join('author', 'author.id', '=', 'all_books.author')
		->join('generes', 'generes.id', 'all_books.genere')
		->select(
			'all_books.id',
			'all_books.name',
			'author.name as author_name',
			'generes.type as genere_type',
		)
		->get();

		foreach ($all_books as $book) {
			$book->images = Media::where('collection_name', 'books_image')
			->where('row_id', $book->id)
			->get('filename');
		}


		return $this->toJSON($response, [
			'status' => true,
			'message' => $all_books
		], 200);
	}

	public function retriveBook(Request $request, Response $response, $args)
	{
		$id = $args['id'];
		$book = Books::join('author', 'author.id', '=', 'all_books.author')
		->join('generes', 'generes.id', 'all_books.genere')
		->select(
			'all_books.id',
			'all_books.name',
			'author.name as author_name',
			'generes.type as genere_type'
		)
		->where('all_books.id', $id)
		->first();

		if (!is_null($book)) {
			$book->images = Media::where('collection_name', 'books_image')
			->where('row_id', $id)
			->get(['filename']);
			return $this->toJSON($response, [
				'status' => true,
				'message' => $book
			], 200);
		}else {
			return $this->toJSON($response, [
				'status' => true,
				'message' => 'Book not found.'
			], 200);
		}
	}

	public function retriveBookForAuthor(Request $request, Response $response, $args)
	{
		$id = $args['id'];
		$books = Books::join('author', 'author.id', '=', 'all_books.author')
		->join('generes', 'generes.id', 'all_books.genere')
		->select(
			'all_books.id',
			'all_books.name',
			'author.name as author_name',
			'generes.type as genere_type'
		)
		->where('author.id', $id)
		->get();
		if (!is_null($books)) {
			foreach ($books as $book) {
				$book->images = Media::where('collection_name', 'books_image')
				->where('row_id', $book->id)
				->get(['filename']);
			}

			return $this->toJSON($response, [
				'status' => true,
				'message' => $books
			], 200);
		}else {
			return $this->toJSON($response, [
				'status' => true,
				'message' => 'Book not found.'
			], 200);
		}
		
	}

	public function createBooks(Request $request, Response $response, $args)
	{
		$rules =
			[
				'name' => [
					'rules' => V::stringType()->notEmpty(),
					'message' => 'Please enter the book name.'
				],
				'author' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the author id.'
				],
				'genere' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the genere id.'
				],
				'price' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the price.'
				]
			];

		$val = new Validator();
		$validator = $val->validate($request, $rules);

		if ($validator->isValid()) {

			$data = $request->getParsedBody();
			$sanitized = [
				'name' => $data['name'],
				'author' => $data['author'] == '' ? null : $data['author'],
				'genere' => $data['genere'],
				'price' => $data['price']
			];

			$directory = $this->media_path;
			$uploadedFiles = $request->getUploadedFiles();
			$collectionName = 'books_image';

			$mimeType = array('gif', 'png', 'jpg', 'jpeg');

			if (isset($uploadedFiles[$collectionName])) {
				foreach ($uploadedFiles[$collectionName] as $file) {
					if ($file->getError() === UPLOAD_ERR_OK) {
						if (!$this->validateInputMedia($mimeType, $file)) {
							return $this->toJSON($response, [
								'status' => true,
								'message' => 'Please choose valid image.'
							], 401);
						}
					}
				}
				$book = Books::create($sanitized);
				$stock = [
					'product_id' => $book->id,
					'quantity' => 0
				];
				Stocks::create($stock);

				if ($file->getError() === UPLOAD_ERR_OK) {
					foreach ($uploadedFiles[$collectionName] as $uploadedFile) {
						$file_info = $this->moveUploadedFile($directory, $uploadedFile);
						Media::create([
							'filename' => $file_info['filename'] . '.' . $file_info['extension'],
							'row_id' => $book->id,
							'type' => $file_info['extension'],
							'table_name' => 'all_books',
							'collection_name' => $collectionName
						]);
					}
				}

				return $this->toJSON($response, [
					'status' => true,
					'message' => 'Successfully created.'
				], 200);
			}

			$book = Books::create($sanitized);
			$stock = [
				'product_id' => $book->id,
				'quantity' => 0
			];
			Stocks::create($stock);
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

	public function updateBooks(Request $request, Response $response, $args)
	{
		$rules =
			[
				'name' => [
					'rules' => V::stringType()->notEmpty(),
					'message' => 'Please enter the book name.'
				],
				'author' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the author id.'
				],
				'genere' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the genere id.'
				],
				'price' => [
					'rules' => V::numeric()->notEmpty(),
					'message' => 'Please enter the price.'
				]
			];

		$val = new Validator();
		$validator = $val->validate($request, $rules);

		if ($validator->isValid()) {
			$data = $request->getParsedBody();
			$id = $args['id'];
			$sanitized = [
				'name' => $data['name'],
				'author' => $data['author'] == '' ? null : $data['author'],
				'genere' => $data['genere'],
				'price' => $data['price']
			];

			$directory = $this->media_path;
			$uploadedFiles = $request->getUploadedFiles();
			$collectionName = 'books_image';

			$mimeType = array('gif', 'png', 'jpg', 'jpeg');

			if (isset($uploadedFiles[$collectionName])) {
				foreach ($uploadedFiles[$collectionName] as $file) {
					if ($file->getError() === UPLOAD_ERR_OK) {
						if (!$this->validateInputMedia($mimeType, $file)) {
							return $this->toJSON($response, [
								'status' => true,
								'message' => 'Please choose valid image.'
							], 401);
						}
					}
				}
				Books::where('id', $id)->update($sanitized);

				Media::where(function ($query) use ($id){
					$qrr = $query->where('row_id', $id);
					$files = $qrr->get('filename');
					foreach ($files as $file) {
						if (!is_null($file)) {
							unlink($this->media_path . '/' . $file['filename']);
						}
					}
					$qrr->delete();
				});
				if ($file->getError() === UPLOAD_ERR_OK) {
					foreach ($uploadedFiles[$collectionName] as $uploadedFile) {
						$file_info = $this->moveUploadedFile($directory, $uploadedFile);
						Media::create([
							'filename' => $file_info['filename'] . '.' . $file_info['extension'],
							'row_id' => $id,
							'type' => $file_info['extension'],
							'table_name' => 'all_books',
							'collection_name' => $collectionName
						]);
					}
				}
				return $this->toJSON($response, [
					'status' => true,
					'message' => 'Successfully updated.'
				], 200);
			}

			Books::where('id', $id)->update($sanitized);
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

	public function deleteBooks(Request $request, Response $response, $args)
	{
		$id = $args['id'];
		$filename = Media::where('collection_name', 'books_image')->where('row_id', $id)->get('filename');
		if (Books::where('id', $id)->delete()) {
			Media::where('row_id', $id)->delete();
			foreach ($filename as $file) {
				if (!is_null($file)) {
					unlink($this->media_path . '/' . $file['filename']);
				}
			}
			return $this->toJSON($response, [
				'status' => true,
				'message' => 'Successfully deleted.'
			], 200);
		}else{
			return $this->toJSON($response, [
				'status' => true,
				'message' => 'Not found.'
			], 200);
		}
	}
}