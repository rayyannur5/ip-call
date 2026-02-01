<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterSound;
use Illuminate\Http\Request;

class SoundController extends Controller
{
    public function index()
    {
        $data = MasterSound::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
