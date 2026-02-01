<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Util extends Model
{
    protected $table = 'utils';
    protected $primaryKey = 'type';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['type', 'value'];
}
