<?php

namespace App\Models\DatabaseSchema;

class Generes extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'generes';
    protected $fillable = ['type'];
    public $timestamps = false;
}
