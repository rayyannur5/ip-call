<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OxiMonitorLog;
use Illuminate\Http\Request;

class OxiMonitorController extends Controller
{
    public function index()
    {
        $logs = OxiMonitorLog::orderBy('timestamp', 'desc')->paginate(10);
        return view('admin.oximonitor.index', compact('logs'));
    }
}
