<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistItem extends Model
{
    protected $table = 'playlist_item';
    // Composite PK in DB: id, ord. Eloquent doesn't support composite PKs natively well for updates/find.
    // But for reading/listing it's fine.
    public $timestamps = false;
    public $incrementing = false;
    // Note: Primary key is composite (id, ord). Use careful where clauses.
    protected $fillable = ['id', 'ord', 'path'];
}
