<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Log;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Statistics Cards
        $today = Carbon::today();
        
        $totalCallsToday = History::whereDate('timestamp', $today)->count();
        $totalMessagesToday = Log::whereDate('timestamp', $today)->count();
        $totalRooms = Room::count();
        $activeNurses = Log::whereDate('timestamp', $today)->where('nurse_presence', true)->count(); // Example metric

        // 2. Chart Data (Last 7 Days)
        $dates = collect();
        $callsData = collect();
        $messagesData = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $formattedDate = $date->format('d M');
            $dates->push($formattedDate);

            $callsData->push(History::whereDate('timestamp', $date)->count());
            $messagesData->push(Log::whereDate('timestamp', $date)->count());
        }

        // 3. Recent Activity (Merged Calls and Logs)
        $recentCalls = History::with(['category', 'bed'])->latest('timestamp')->take(5)->get()->map(function($item) {
            $item->type = 'call';
            return $item;
        });
        
        $recentLogs = Log::with(['category', 'bed'])->latest('timestamp')->take(5)->get()->map(function($item) {
            $item->type = 'message';
            return $item;
        });

        $recentActivity = $recentCalls->concat($recentLogs)->sortByDesc('timestamp')->take(8);

        return view('admin.dashboard', compact(
            'totalCallsToday', 
            'totalMessagesToday', 
            'totalRooms', 
            'dates',
            'callsData',
            'messagesData',
            'recentActivity'
        ));
    }
}
