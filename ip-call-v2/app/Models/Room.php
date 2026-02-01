<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'room';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id', 'type', 'name', 'running_text', 'type_bed', 'bed_separator', 'serial_number', 'bypass'];
    protected $casts = [
        'id' => 'string',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class, 'room_id');
    }

    public function toilets()
    {
        return $this->hasMany(Toilet::class, 'room_id');
    }
}
