<?php

namespace App\Models\DatabaseSchema;

class Users extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_admin',
    ];
    public $timestamps = true;
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
