<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';
    public $timestamps = false; // Has custom timestamp col
    protected $fillable = ['bed_id', 'category_history_id', 'duration', 'record'];

    public function category()
    {
        return $this->belongsTo(CategoryHistory::class, 'category_history_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id', 'id');
    }
}
