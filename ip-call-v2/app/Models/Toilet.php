<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toilet extends Model
{
    protected $table = 'toilet';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id', 'room_id', 'username', 'serial_number', 'bypass'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
