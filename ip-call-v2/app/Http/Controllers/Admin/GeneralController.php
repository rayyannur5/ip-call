<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Util;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function index()
    {
        $utils = Util::all();
        return view('admin.general.index', compact('utils'));
    }

    public function update(Request $request)
    {
        $type = $request->input('type');
        $value = $request->input('value');

        Util::where('type', $type)->update([
            'value' => $value,
            'description' => $request->input('description')
        ]);

        return redirect()->back()->with('success', 'Setting updated successfully');
    }
}
