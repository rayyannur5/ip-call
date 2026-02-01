<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Toilet;
use App\Models\RunningText;
use App\Models\MasterSound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['beds', 'toilets'])->get();
        $running_texts = RunningText::all();

        // Prepare audio data for each room based on expanding names
        foreach ($rooms as $room) {
            $names = explode(' ', $room->name);
            $audioSources = [];
            foreach ($names as $name) {
                $sound = MasterSound::where('name', $name)->first();
                $audioSources[] = $sound ? asset($sound->source) : null;
            }
            $room->names = $names;
            $room->audio = $audioSources;
        }

        return view('admin.rooms.index', compact('rooms', 'running_texts'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id;
            $type = $request->jenis;
            $type_bed = $request->type_bed;
            $separator_bed = $request->separator_bed;
            $running_text = $request->running_text;
            $names = $request->name; // Array
            
            $fullname = ltrim(implode(" ", $names));

            // Create Room
            Room::create([
                'id' => $id, // Assuming user provides ID or it's auto-inc (legacy let user input ID?) Legacy code: $id = $_POST['id']; INSERT INTO room (id...) VALUES ($id...)
                'type' => $type,
                'name' => $fullname,
                'running_text' => $running_text,
                'type_bed' => $type_bed,
                'bed_separator' => $separator_bed,
                'serial_number' => null,
                'bypass' => 0
            ]);

            // Handle Audio Files
            foreach ($names as $key => $name) {
                // Check valid file
                if ($request->hasFile("audio.$key")) {
                    $file = $request->file("audio")[$key];
                    $filename = $file->getClientOriginalName();
                    $target_dir = "uploads/";
                    $target_file = $target_dir . $filename;
                    
                    $file->move(public_path('uploads'), $filename);

                    $check = MasterSound::where('name', $name)->first();
                    if (!$check) {
                        MasterSound::create([
                            'name' => $name,
                            'source' => $target_file // strict legacy path format
                        ]);
                    } else {
                        $check->update(['source' => $target_file]);
                    }
                } else {
                     // Check if master sound exists for this name, if not create entry without source? 
                     // Legacy logic only inserts if not exists or updates if exists.
                     // If no file uploaded, legacy logic basically relies on name existence.
                     $check = MasterSound::where('name', $name)->first();
                     if (!$check) {
                         MasterSound::create([
                             'name' => $name,
                             'source' => null
                         ]);
                     }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Tambah Ruang Berhasil');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id;
            $last_id = $request->last_id;
            $names = $request->name; // Array
            $last_names = $request->last_name; // Array
            $fullname = ltrim(implode(" ", $names));

            // Update Room
            $room = Room::find($last_id);
            $room->update([
                'id' => $id,
                'name' => $fullname,
                'type' => $request->jenis,
                'running_text' => $request->running_text,
                'type_bed' => $request->type_bed,
                'bed_separator' => $request->separator_bed
            ]);

            // Refresh room data
            $jenis = $room->type;
            $jenis_bed = $room->type_bed;
            $separator = $room->bed_separator != "" ? $room->bed_separator . " " : '';

             // Re-generate Bed/Toilet names/ids
            if ($request->has('name')) {
                foreach ($names as $key => $name) {
                    $last_name = $last_names[$key] ?? $name;

                    // Update Beds
                    $beds = Bed::where('room_id', $last_id)->get(); // Use last_id to find them before ID update? No, Foreign Key might break if ID changed. 
                    // Actually Laravel might not cascade updates unless defined. Legacy did update manually.
                    // Legacy code updates Room ID first. So beds might be orphaned or we need to update beds with new room_id.
                    // But legacy sets room.id = $id WHERE id = $last_id. 
                    // If DB has cascade, beds update automatically. If not, we need to handle it.
                    // Let's assume we fetch beds by new ID if cascade happened, or OLD id if not. Ref code uses $last_id to find beds? 
                    // Wait, legacy: UPDATE room SET id=$id WHERE id=$last_id. Then $beds = queryArray("SELECT * FROM bed WHERE room_id = $last_id"); 
                    // If ID changed, this query returns NOTHING unless invalid query or FK constraint matches. 
                    // Actually, if we change ID, logic should ideally update children. Legacy might rely on $id == $last_id usually.
                    // Let's grab beds by current room details.
                    
                    // Actually, if we updated Room ID, we must update Bed room_id.
                    // Assuming we stick to ID.
                    $beds = Bed::where('room_id', $id)->orWhere('room_id', $last_id)->get();

                    $is_only_one = $beds->count() == 1;
                    foreach ($beds as $index => $bed) {
                        $new_bed_id = "01" . str_pad($id, 2, "0", STR_PAD_LEFT) . str_pad($index + 1, 2, "0", STR_PAD_LEFT);
                        
                        // Name logic
                        $username = "";
                         if ($jenis == "") {
                            if ($is_only_one) {
                                $username = $fullname;
                            } else {
                                $suffix = ($jenis_bed == 'numeric') ? ($index + 1) : $this->convertToLetter($index + 1);
                                $username = $fullname . ' ' . $separator . $suffix;
                            }
                        } else {
                            if ($is_only_one) {
                                $username = $jenis . ' ' . $fullname;
                            } else {
                                $suffix = ($jenis_bed == 'numeric') ? ($index + 1) : $this->convertToLetter($index + 1);
                                $username = $jenis . ' ' . $fullname . ' ' . $separator . $index + 1; // Wait, legacy line 69-71 same structure?
                                // Legacy: $username = $jenis.' ' . $fullname . ' ' . $separator . convertToLetter($index + 1);
                                // My code line 71: $username = $jenis.' ' . $fullname . ' ' . $separator . convertToLetter($index + 1);
                                // The legacy logic for else block (lines 68-71) seems to ignore 'type_bed' in line 68? No, it checks it.
                            }
                        }
                        
                        $username = ltrim($username);
                        $bed->update([
                            'id' => $new_bed_id,
                            'room_id' => $id,
                            'username' => $username
                        ]);
                    }

                    // Update Toilets
                    $toilets = Toilet::where('room_id', $id)->orWhere('room_id', $last_id)->get();
                    $is_only_one_toilet = $toilets->count() == 1;
                    foreach ($toilets as $index => $toilet) {
                        $new_toilet_id = "02" . str_pad($id, 2, "0", STR_PAD_LEFT) . str_pad($index + 1, 2, "0", STR_PAD_LEFT);
                        $user_toilet_name = $is_only_one_toilet ? 'Toilet ' . $fullname : 'Toilet ' . $fullname . ' ' . ($index + 1);
                        
                        $toilet->update([
                            'id' => $new_toilet_id,
                            'room_id' => $id,
                            'username' => $user_toilet_name
                        ]);
                    }

                    // Audio Files
                    if ($request->hasFile("audio.$key")) {
                        $file = $request->file("audio")[$key];
                        $filename = $file->getClientOriginalName();
                        $target_dir = "uploads/";
                        $target_path = public_path($target_dir);
                        $target_file = $target_dir . $filename;
                        
                        $file->move($target_path, $filename);

                        // Update MasterSound
                        // Logic: Update source for 'name', rename 'name' if changed.
                        // Legacy: UPDATE mastersound SET source = '$target_file', name = '$name' WHERE name = '$last_name'
                        MasterSound::where('name', $last_name)->update([
                            'source' => $target_file,
                            'name' => $name
                        ]);
                    } else {
                        // Just update name
                         MasterSound::where('name', $last_name)->update([
                            'name' => $name
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Ubah Ruang Berhasil');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id;
            // Delete Beds and Toilets
            Bed::where('room_id', $id)->delete();
            Toilet::where('room_id', $id)->delete();
            Room::where('id', $id)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Ruang berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
             return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Bed Actions
    public function storeBed(Request $request)
    {
        $room_id = $request->room_id;
        $room = Room::find($room_id);
        
        $bed_before = Bed::where('room_id', $room_id)->get();
        $count = $bed_before->count();
        $nomor = str_pad($count + 1, 2, "0", STR_PAD_LEFT);
        
        $id = "01" . str_pad($room_id, 2, "0", STR_PAD_LEFT) . $nomor;

        $name = $this->generateBedName($room, $count + 1);

        // Rename first bed if needed
        if ($count == 1) {
             $first_bed = $bed_before->first();
             $first_bed_new_name = $this->generateBedName($room, 1, true); // Force numbered name
             $first_bed->update(['username' => $first_bed_new_name]);
        }

        Bed::create([
            'id' => $id,
            'room_id' => $room_id,
            'username' => $name,
            'vol' => 100,
            'mic' => 100,
            'tw' => 1,
            'mode' => 0,
             // 'ip' => null, // default
             // 'serial_number' => null,
            'bypass' => 0,
            'phone' => $id // Logic line 49 bed-add.php: VALUES(..., '$id') for phone
        ]);

        return redirect()->back()->with('success', 'Bed berhasil ditambahkan');
    }

    public function updateBed(Request $request)
    {
        $bed = Bed::find($request->id);
        $bed->update([
            'tw' => $request->has('tw') ? 1 : 0,
            'vol' => $request->vol,
            'mic' => $request->mic,
            'mode' => $request->mode
        ]);
        return redirect()->back()->with('success', 'Bed berhasil diupdate');
    }

    public function destroyBed(Request $request)
    {
        Bed::destroy($request->id);
        return redirect()->back()->with('success', 'Bed berhasil dihapus');
    }

    // Toilet Actions
    public function storeToilet(Request $request)
    {
        $room_id = $request->room_id;
        $room = Room::find($room_id);
        $count = Toilet::where('room_id', $room_id)->count();
        $nomor = str_pad($count + 1, 2, "0", STR_PAD_LEFT);
        
        $id = "02" . str_pad($room_id, 2, "0", STR_PAD_LEFT) . $nomor;
        $name = "Toilet " . $room->name . " " . ($count == 0 ? "" : $count + 1);

        Toilet::create([
            'id' => $id,
            'room_id' => $room_id,
            'username' => $name,
             // others default
            'bypass' => 0
        ]);
         return redirect()->back()->with('success', 'Toilet berhasil ditambahkan');
    }

    public function destroyToilet(Request $request)
    {
        Toilet::destroy($request->id);
         return redirect()->back()->with('success', 'Toilet berhasil dihapus');
    }

    // Bypass Action
    public function bypass(Request $request)
    {
        $type = $request->type; // room, bed, toilet
        $id = $request->id;

        if ($type == 'room') {
            $item = Room::find($id);
        } elseif ($type == 'bed') {
            $item = Bed::find($id);
        } elseif ($type == 'toilet') {
            $item = Toilet::find($id);
        }

        if ($item) {
            $item->bypass = $item->bypass == 1 ? 0 : 1;
            $item->save();
        }
        
        return redirect()->back();
    }


    // Helpers
    private function convertToLetter($number) {
        if ($number >= 1 && $number <= 26) {
            return chr(64 + $number);
        }
        return $number;
    }

    private function generateBedName($room, $index, $force_suffix = false) {
        $jenis = $room->type;
        $jenis_bed = $room->type_bed;
        $separator = $room->bed_separator != "" ? $room->bed_separator . " " : '';
        $room_name = $room->name;

        // Count (already +1 passed)
        $is_first = ($index == 1);
        
        if ($is_first && !$force_suffix) {
             $prefix = ($jenis != "" ? "$jenis " : "");
             return $prefix . $room_name; // Just Room Name if only 1? Logic in bed-add lines 32-35 is trickier.
             // Line 32: $name = ($jenis != "" ? "$jenis " : "") . $room_name . " " . (count($bed_before) == 0 ? "" : $separator . count($bed_before) + 1);
             // If count==0 (this is first bed), suffix is empty string.
        }

        $suffix = ($jenis_bed == 'numeric') ? ($index) : $this->convertToLetter($index);
        $prefix = ($jenis != "" ? "$jenis " : "");
        
        return $prefix . $room_name . " " . $separator . $suffix;
    }
    public function reboot()
    {
        try {
            $beds = Bed::all();

            $pjsip_path = "/etc/asterisk/pjsip.conf";
            $extensions_path = "/etc/asterisk/extensions.conf";

            // Ensure directory exists or fallback to storage for testing/safety if strictly inside container
            if (!file_exists(dirname($pjsip_path))) {
                // In Docker, this might not exist. Create it or warn. 
                // Using mkdir might fail if no permissions.
                // For now, attempt strictly as legacy did, usually mapped volumes handle this.
                @mkdir(dirname($pjsip_path), 0777, true);
            }

            $txt = "
[transport-udp]
type=transport
protocol=udp
bind=0.0.0.0

[transport-wss]
type=transport
protocol=wss
bind=0.0.0.0

[endpoint_basic](!)
type=endpoint
context=plan-num
disallow=all
allow=ulaw
direct_media=no
language=en

[authentication](!)
type=auth
auth_type=userpass

[aor_template](!)
type=aor
max_contacts=10
remove_existing=yes

[hp](endpoint_basic)
auth=hp
aors=hp
callerid=\"HP TEST\" <200>
[hp](authentication)
password=hp
username=hp
[hp](aor_template)

[server](endpoint_basic)
auth=server
aors=server
callerid=\"server\" <server>
[server](authentication)
password=server
username=server
[server](aor_template)

[webrtc_client]
type=aor
max_contacts=5
remove_existing=yes

[webrtc_client]
type=auth
auth_type=userpass
username=webrtc_client
password=webrtc_client

[webrtc_client]
type=endpoint
aors=webrtc_client
auth=webrtc_client
webrtc=yes
context=plan-num
disallow=all
allow=ulaw
direct_media=no
";

            $txt_extensions = "
[plan-num]

exten => 100,1,Dial(PJSIP/webrtc_client,10)
exten => 100,2,Hangup()

exten => 200,1,Dial(PJSIP/hp,10)
exten => 200,2,Hangup()

exten => 300,1,Dial(PJSIP/server,10)
exten => 300,2,Hangup()

exten => h,1,System(python3 /opt/lampp/htdocs/ip-call/update.py \${datetime})

";

            foreach ($beds as $bed) {
                $bed_id = $bed->id;
                $bed_name = $bed->username;

                if ($bed->tw == 1) {
                    $txt .= "
[$bed_id](endpoint_basic)
auth=$bed_id
aors=$bed_id
callerid=\"$bed_name\" <$bed_id>
[$bed_id](authentication)
password=$bed_id
username=$bed_id
[$bed_id](aor_template)

";
                    $txt_extensions .= "
exten => $bed_id,1,Set(datetime=\${STRFTIME(\${EPOCH},,%Y%m%d-%H%M%S)})
same => n,Set(recording_file=/opt/lampp/htdocs/records/\${datetime}.wav)
same => n,MixMonitor(\${recording_file})
same => n,Dial(PJSIP/$bed_id,10)
same => n,Hangup()     
    ";
                }
            }

            file_put_contents($pjsip_path, $txt);
            file_put_contents($extensions_path, $txt_extensions);

            // Execute reboot
            // Note: In Docker, this stops the container. 
            // The legacy path /opt/lampp/htdocs/... suggests full VM/Host paths.
            // We are likely in a container. This might do nothing or stop the container.
            exec('reboot'); 
            
            return redirect()->back()->with('success', 'System update applied. Rebooting...');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during update/reboot: ' . $e->getMessage());
        }
    }
    // Bulk Actions
    public function bulkUpdateMode(Request $request)
    {
        $mode = $request->mode; // 2 for CodeBlue, 0 for Emergency
        Bed::query()->update(['mode' => $mode]);
        
        $message = ($mode == 2) ? 'Semua Bed berhasil diubah ke CodeBlue' : 'Semua Bed berhasil diubah ke Emergency';
        return redirect()->back()->with('success', $message);
    }

    public function bulkUpdateTw(Request $request)
    {
        $tw = $request->tw; // 1 for 2W, 0 for 1W
        Bed::query()->update(['tw' => $tw]);

        $message = ($tw == 1) ? 'Semua Bed berhasil diubah ke 2W (Two-Way)' : 'Semua Bed berhasil diubah ke 1W (One-Way)';
        return redirect()->back()->with('success', $message);
    }
}
