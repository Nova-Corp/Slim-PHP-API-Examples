<?php

namespace App\Models\DatabaseSchema;

class Media extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'media';
    protected $fillable = [
        'filename',
        'type',
        'row_id',
        'type',
        'table_name',
        'collection_name'
    ];
    public $timestamps = true;
}
