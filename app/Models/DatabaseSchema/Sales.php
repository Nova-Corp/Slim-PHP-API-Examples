<?php

namespace App\Models\DatabaseSchema;

class Sales extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'sales';
    protected $fillable = [
        'product_id',
        'offer',
        'price',
        'order_id',
        'quantity',
    ];
    public $timestamps = true;
}
