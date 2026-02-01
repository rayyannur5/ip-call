<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunningText extends Model
{
    protected $table = 'running_text';
    protected $primaryKey = 'topic';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['topic', 'speed', 'brightness'];
}
