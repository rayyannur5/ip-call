<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Util;
use Illuminate\Http\Request;

class UtilController extends Controller
{
    public function index()
    {
        $data = Util::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
