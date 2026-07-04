<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adzan extends Model
{
    protected $table = 'adzan';
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['key', 'value'];
}
