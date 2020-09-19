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

class SalesController extends Helper
{
    public function createSales(Request $request, Response $response, $args)
    {
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
    }
}