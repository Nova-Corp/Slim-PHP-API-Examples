<?php

/**
 * BooksController.php
 */

namespace App\Controllers;

use App\Models\DatabaseSchema\AllBooks;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Helpers\Helper;

class BooksController extends Helper
{
	public function index(Request $request, Response $response)
	{
		return $this->toJSON($response, AllBooks::get(), 200);
	}
}