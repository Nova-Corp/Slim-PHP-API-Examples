<?php

namespace App\Models\DatabaseSchema;

class Authors extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'author';
    protected $fillable = ['name'];
    public $timestamps = false;
}
