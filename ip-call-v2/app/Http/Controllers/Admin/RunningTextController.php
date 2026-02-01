<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RunningText;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    public function index()
    {
        $texts = RunningText::all();
        return view('admin.running_text.index', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|unique:running_text,topic|max:255',
            'speed' => 'required|integer',
            'brightness' => 'required|integer',
        ]);

        RunningText::create($request->all());

        return redirect('/admin/running-text')->with('success', 'Running Text created successfully.');
    }

    public function update(Request $request, $topic)
    {
        $request->validate([
            'speed' => 'required|integer',
            'brightness' => 'required|integer',
        ]);

        $text = RunningText::where('topic', $topic)->firstOrFail();
        $text->update($request->all());

        return redirect('/admin/running-text')->with('success', 'Running Text updated successfully.');
    }

    public function destroy($topic)
    {
        $text = RunningText::where('topic', $topic)->firstOrFail();
        $text->delete();

        return redirect('/admin/running-text')->with('success', 'Running Text deleted successfully.');
    }
}
