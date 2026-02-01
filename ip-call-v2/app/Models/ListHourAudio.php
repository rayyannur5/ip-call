<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListHourAudio extends Model
{
    use HasFactory;

    protected $table = 'list_hour_audio';
    public $timestamps = false;

    protected $fillable = [
        'time',
        'vol',
    ];
}
