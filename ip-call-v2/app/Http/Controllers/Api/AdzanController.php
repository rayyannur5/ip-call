<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adzan;
use Illuminate\Http\Request;

class AdzanController extends Controller
{
    public function index()
    {
        $data = Adzan::all();
        return response()->json($data);
    }
}
