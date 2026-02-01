<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users'; // or 'user' if sticking strictly to legacy table name but migration created 'users'
    // Migration created 'users' (plural), so keeping 'users'.
    // If I used 'user' in migration, I should change this. 
    // I put Schema::create('users'...) in migration step 63. So here it is 'users'.

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
}
