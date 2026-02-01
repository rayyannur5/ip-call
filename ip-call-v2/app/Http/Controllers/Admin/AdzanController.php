<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adzan;
use Illuminate\Http\Request;

class AdzanController extends Controller
{
    public function index()
    {
        $adzan_active = \App\Models\Util::find('adzan_active');
        $adzan_auto = \App\Models\Util::find('adzan_auto');

        $active = $adzan_active ? $adzan_active->value : 0;
        $auto = $adzan_auto ? $adzan_auto->value : 0;
        $adzans = [];

        if ($auto == 1) {
            // Read from Liquidsoap file
            $filePath = '/mnt/24DE6914DE68E012/Projects/ip-call/liquidsoap/radio.liq'; 

            if (file_exists($filePath) && is_readable($filePath)) {
                $string = file_get_contents($filePath);
                
                // Matches #=# ... #=#
                preg_match('/#=#(.*?)#=#/s', $string, $matches);

                if (isset($matches[1])) {
                    $blokJadwal = trim($matches[1]);
                    $barisJadwal = explode("\n", $blokJadwal);

                    foreach ($barisJadwal as $baris) {
                        // Regex matching: ({ XXhYYm } ..., #nama_sholat)
                        preg_match('/\(\{\s*(\d{2}h\d{2}m)\s*\}\s*,\s*amplify\(.*?\)\),\s*#(\w+)/', trim($baris), $detail);
                        
                        if (count($detail) === 3) {
                            $waktu = str_replace(['h', 'm'], [':', ''], $detail[1]);
                            $namaSholat = ucfirst($detail[2]);
                            
                            // Temporary structure for display
                            $adzans[] = (object) [
                                'key' => strtolower($detail[2]), // e.g., subuh
                                'name' => $namaSholat,
                                'value' => $waktu 
                            ];
                        }
                    }
                }
            }
        } else {
            // Read from Database
            $adzans = Adzan::orderBy('value')->get();
            // Transform for consistent view access if needed, though Adzan model uses 'key' and 'value' matches above object structure partly.
            // Adjusting view loop to use ->value for time and ->key for name/label might be needed.
            // Model: key, value. above object: key, name, value.
            // Let's ensure consistency:
            foreach ($adzans as $adzan) {
                $adzan->name = ucfirst($adzan->key);
            }
        }

        return view('admin.adzan.index', compact('adzans', 'active', 'auto'));
    }

    public function update(Request $request)
    {
        // Update Toggles
        \App\Models\Util::updateOrCreate(
            ['type' => 'adzan_active'],
            ['value' => $request->has('adzan_active') ? '1' : '0']
        );

        $auto = $request->has('adzan_auto') ? '1' : '0';
        \App\Models\Util::updateOrCreate(
            ['type' => 'adzan_auto'],
            ['value' => $auto]
        );

        // Update Times if Manual
        if ($auto == '0') {
            $inputs = $request->except(['_token', 'adzan_active', 'adzan_auto']);
            foreach ($inputs as $key => $value) {
                // Key expected to be 'subuh', 'dhuhur', etc. matches database keys
                Adzan::where('key', $key)->update(['value' => $value]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan Adzan berhasil disimpan');
    }
}
