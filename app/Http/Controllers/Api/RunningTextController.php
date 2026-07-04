<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RunningText;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    public function index(Request $request)
    {
        // $id = $_GET['id'];
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['error' => 'Missing id parameter'], 400);
        }

        $runningText = RunningText::where('topic', $id)->first();

        if ($runningText) {
             return response()->json($runningText);
        } else {
             return response()->json([], 200); // Or 404, but legacy might return empty or error. Legacy did [0] which implies fetch one. If not found, legacy logic might error or return null.
        }
    }
}
