<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        // The user requested exactly: $res = queryArray("SELECT * FROM playlist"); echo json_encode($res);
        // Using Eloquent: Playlist::all() returns a collection which serializes to JSON array of objects.
        // This matches the requested output format.
        return response()->json(Playlist::all());
    }
}
