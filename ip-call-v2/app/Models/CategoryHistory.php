<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryHistory extends Model
{
    protected $table = 'category_history';
    public $timestamps = false;
    protected $fillable = ['name'];
}
