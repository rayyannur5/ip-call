<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OxiMonitorStatus extends Model
{
    protected $table = 'oximonitor_status';
    public $timestamps = false;
    protected $fillable = ['id', 'flow_rate', 'updated_at'];
}
