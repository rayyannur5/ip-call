<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'log';
    public $timestamps = false; // Only has timestamp col which is auto
    protected $fillable = ['category_log_id', 'value', 'device_id', 'time', 'nurse_presence'];
    
    public function category()
    {
        return $this->belongsTo(CategoryLog::class, 'category_log_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'device_id', 'id');
    }
}
