<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $table = 'playlist';
    public $timestamps = false;
    protected $fillable = ['name', 'volume', 'start_time', 'end_time'];

    public function items()
    {
        return $this->hasMany(PlaylistItem::class, 'id', 'id')->orderBy('ord');
    }
}
