<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSound; // Assuming this model exists
use Illuminate\Http\Request;

class AudioController extends Controller
{
    public function index()
    {
        $list = \App\Models\ListHourAudio::orderBy('time', 'asc')->get();
        return view('admin.audio.index', compact('list'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'time' => 'required',
            'vol' => 'required|numeric',
        ]);

        \App\Models\ListHourAudio::create([
            'time' => $request->time,
            'vol' => $request->vol,
        ]);

        return redirect()->back()->with('success', 'Audio setting added successfully');
    }

    public function destroy($id)
    {
        $audio = \App\Models\ListHourAudio::find($id);
        if ($audio) {
            $audio->delete();
            return redirect()->back()->with('success', 'Audio setting deleted successfully');
        }
        return redirect()->back()->with('error', 'Audio setting not found');
    }
}
