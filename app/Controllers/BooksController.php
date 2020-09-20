<?php

/**
 * BooksController.php
 */

namespace App\Controllers;

use App\Models\DatabaseSchema\Books;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Helpers\Helper;
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
			'generes.type as genere_type'
		)
		->get();
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
		$book = Books::join('author', 'author.id', '=', 'all_books.author')
		->join('generes', 'generes.id', 'all_books.genere')
		->select(
			'all_books.id',
			'all_books.name',
			'author.name as author_name',
			'generes.type as genere_type'
		)
		->where('author.id', $id)
		->first();
		if (!is_null($book)) {
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
			];
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
			];
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
		if (Books::where('id', $id)->delete()) {
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