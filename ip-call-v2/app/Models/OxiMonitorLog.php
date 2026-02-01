<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OxiMonitorLog extends Model
{
    protected $table = 'oximonitor_log';
    public $timestamps = false;
    protected $fillable = ['volume', 'created_at'];
}
