<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryLog extends Model
{
    protected $table = 'category_log';
    public $timestamps = false;
    protected $fillable = ['name'];
}
