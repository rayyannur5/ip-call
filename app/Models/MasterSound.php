<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSound extends Model
{
    protected $table = 'mastersound';
    public $timestamps = false;
    protected $fillable = ['name', 'source'];
}
