<?php

/**
 * BooksController.php
 */

namespace App\Controllers;

use App\Models\DatabaseSchema\Books;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Helpers\Helper;

class BooksController extends Helper
{
	public function listAllBooks(Request $request, Response $response)
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
		return $this->toJSON($response, $all_books, 200);
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
		return $this->toJSON($response, $book, 200);
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
		return $this->toJSON($response, $book, 200);
	}

	public function createBooks(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		$sanitized = [
			'name' => $data['name'],
			'author' => $data['author']==''?null: $data['author'],
			'genere' => $data['genere'],
		];
		Books::create($sanitized);
		return $this->toJSON($response, 'Success', 200);
	}

	public function updateBooks(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		$id = $args['id'];
		$sanitized = [
			'name' => $data['name'],
			'author' => $data['author'] == '' ? null : $data['author'],
			'genere' => $data['genere'],
		];
		Books::where('id', $id)->update($sanitized);
		return $this->toJSON($response, 'Success', 200);
	}

	public function deleteBooks(Request $request, Response $response, $args)
	{
		$id = $args['id'];
		if (Books::where('id', $id)->delete()) {
			return $this->toJSON($response, 'Success', 200);
		}else{
			return $this->toJSON($response, 'Failed', 200);
		}
	}
}