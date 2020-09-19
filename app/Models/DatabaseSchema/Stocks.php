<?php

namespace App\Models\DatabaseSchema;

class Stocks extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'stocks';
    protected $fillable = [
        'product_id',
        'quantity'
    ];
    public $timestamps = true;
}
