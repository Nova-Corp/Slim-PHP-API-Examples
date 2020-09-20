<?php
/*
    SalesController.php
*/

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\DatabaseSchema\Sales;
use App\Models\DatabaseSchema\Stocks;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as V;
use Awurth\SlimValidation\Validator;

class SalesController extends Helper
{
    public function createSales(Request $request, Response $response, $args)
    {
        $rules =
        [
            'product_id' => [
                'rules' => V::numeric()->notEmpty(),
                'message' => 'Please enter the product_id in numeric.'
            ],
            'price' => [
                'rules' => V::numeric()->notEmpty(),
                'message' => 'Please enter the price in numeric.'
            ],
            'order_id' => [
                'rules' => V::numeric()->notEmpty(),
                'message' => 'Please enter the order id in numeric.'
            ],
            'quantity' => [
                'rules' => V::numeric()->notEmpty(),
                'message' => 'Please enter the quantity in numeric.'
            ]
        ];

        $val = new Validator();
        $validator = $val->validate($request, $rules);

        if ($validator->isValid()) {
            $orders = $request->getParsedBody();

            foreach ($orders as $order) {
                $unique_id = time() . mt_rand();
                $sanitized = [
                    'product_id' => $order['product_id'],
                    'price' => $order['price'],
                    'order_id' => $unique_id,
                    'quantity' => $order['quantity']
                ];
                Sales::create($sanitized);
                Stocks::where(function ($query) use ($order) {
                    $stock = $query->where('product_id', $order['product_id'])->value('quantity');
                    $query->where('product_id', $order['product_id'])->update([
                        'quantity' => $stock - $order['quantity']
                    ]);
                });
            }

            return $this->toJSON($response, [
                'status' => true,
                'message' => 'Successfully order placed.'
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