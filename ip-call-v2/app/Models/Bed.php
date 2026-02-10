<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'bed';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id', 'room_id', 'username', 'vol', 'mic', 'tw', 'mode', 'ip', 'serial_number', 'bypass', 'phone', 'cable'];

    protected $casts = [
        'cable' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
