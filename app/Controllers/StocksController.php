<?php
/*
    StocksController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Stocks;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class StocksController extends Helper
{
    public function stockList(Request $request, Response $response)
    {
        $all_stocks = Stocks::join('all_books', 'all_books.id', '=', 'stocks.product_id')
            ->join('generes', 'generes.id', 'all_books.genere')
            ->join('author', 'author.id', 'all_books.author')
            ->select(
                'all_books.id',
                'all_books.name',
                'author.name as author_name',
                'generes.type as genere_type',
                'stocks.quantity'
            )
            ->get();
        return $this->toJSON($response, [
            'status' => true,
            'message' => $all_stocks
        ], 200);
    }

    public function retriveStock(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $book = Stocks::join('all_books', 'all_books.id', '=', 'stocks.product_id')
            ->join('generes', 'generes.id', 'all_books.genere')
            ->join('author', 'author.id', 'all_books.author')
            ->select(
                'all_books.id',
                'all_books.name',
                'author.name as author_name',
                'generes.type as genere_type',
                'stocks.quantity'
            )
            ->where('all_books.id', $id)
            ->first();

        if (!is_null($book)) {
            return $this->toJSON($response, [
                'status' => true,
                'message' => $book
            ], 200);
        } else {
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Product not found.'
            ], 200);
        }
    }

    public function updateStock(Request $request, Response $response, $args)
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
            $sanitized = [
                'quantity' => $data['quantity']
            ];
            Stocks::where('product_id', $id)->update($sanitized);
            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully stock updated.'
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
}