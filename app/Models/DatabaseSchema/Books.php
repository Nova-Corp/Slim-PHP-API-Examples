<?php  
namespace App\Models\DatabaseSchema;

class Books extends \Illuminate\Database\Eloquent\Model {  
  protected $table = 'all_books';
  protected $fillable = ['name', 'author', 'genere', 'price'];
  public $timestamps = false;
}