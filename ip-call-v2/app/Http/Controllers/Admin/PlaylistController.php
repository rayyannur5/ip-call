<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with('items')->get();
        // Since no relationship defined in model yet, we might need to fetch items manually or define relationship
        // Let's define relationship in Playlist model later or fetch here manually if needed via loop
        // But better to define relationship. For now let's assume simple fetch.
        // Relationship 'items' is now defined in model and accessed via eager loading.

        return view('admin.playlist.index', compact('playlists'));
    }

    public function store(Request $request)
    {
        Playlist::create([
            'name' => $request->name,
            'volume' => $request->volume,
            'start_time' => $request->start,
            'end_time' => $request->end,
        ]);
        return redirect()->back()->with('success', 'Playlist berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        Playlist::where('id', $request->id)->update([
            'name' => $request->name,
            'volume' => $request->volume,
            'start_time' => $request->start,
            'end_time' => $request->end,
        ]);
        return redirect()->back()->with('success', 'Playlist berhasil diubah');
    }

    public function destroy($id)
    {
        Playlist::where('id', $id)->delete();
        PlaylistItem::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Playlist berhasil dihapus');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:mp3,wav,ogg',
            'playlist_id' => 'required'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            // Move to standard legacy path: /opt/lampp/htdocs/ip-call/playlist/music/
            // Or project path if applicable. Legacy used: /opt/lampp/htdocs/ip-call/playlist/music/
            // We should use a relative path if possible, but liquidsoap might expect absolute.
            // Let's stick to the path structure seen in legacy code or project root/playlist/music
            
            $destinationPath = base_path('../playlist/music'); // Going up from ip-call-v2
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);

            // Determine order (ord)
            $lastItem = PlaylistItem::where('id', $request->playlist_id)->orderBy('ord', 'desc')->first();
            $ord = $lastItem ? $lastItem->ord + 1 : 1;

            PlaylistItem::create([
                'id' => $request->playlist_id,
                'ord' => $ord,
                'path' => $filename
            ]);
        }

        return redirect()->back()->with('success', 'Item berhasil ditambahkan');
    }

    public function destroyItem($playlist_id, $ord)
    {
        PlaylistItem::where('id', $playlist_id)->where('ord', $ord)->delete();
        return redirect()->back()->with('success', 'Item berhasil dihapus');
    }

    public function writeConfig()
    {
        $playlists = Playlist::all();
        
        // 1. Write .m3u files
        $playlistPath = base_path('../playlist'); // Adjust relative path
        if (!file_exists($playlistPath)) mkdir($playlistPath, 0777, true);

        foreach ($playlists as $playlist) {
            $items = PlaylistItem::where('id', $playlist->id)->orderBy('ord')->get();
            $filename = $playlistPath . '/' . str_replace(" ", "_", $playlist->name) . ".m3u";
            
            $content = "";
            foreach ($items as $item) {
                // Legacy path used in m3u: /opt/lampp/htdocs/ip-call/playlist/music/
                // Use absolute path for liquidsoap relevance
                // We should probably check where the base dir is.
                // Assuming standard path: /mnt/24DE6914DE68E012/Projects/ip-call/playlist/music/
                $content .= "/mnt/24DE6914DE68E012/Projects/ip-call/playlist/music/" . $item->path . "\n";
            }
            file_put_contents($filename, $content);
        }

        // 2. Write radio.liq
        $txt = "";
        foreach($playlists as $playlist) {
            $name = str_replace(" ", "_", $playlist->name);
            // Verify path to m3u in liq
            $m3uPath = "/mnt/24DE6914DE68E012/Projects/ip-call/playlist/$name.m3u";
            $txt .= "$name = playlist(\"$m3uPath\", mode=\"normal\", reload_mode=\"watch\")" . "\n";
        }

        $txt .= "
adzan_playlist = playlist(\"/mnt/24DE6914DE68E012/Projects/ip-call/playlist/adzan.m3u\")
adzan_subuh_playlist = playlist(\"/mnt/24DE6914DE68E012/Projects/ip-call/playlist/adzan_subuh.m3u\")

# Gabungkan dengan fallback
source = fallback(track_sensitive=false, [
  switch([
    #=#
        adzan
    #=#
";

        foreach($playlists as $playlist) {
            $name = str_replace(" ", "_", $playlist->name);
            // Format time HH:MM to HHhMMm
            $start = \Carbon\Carbon::parse($playlist->start_time)->format('H\hi\m');
            $end = \Carbon\Carbon::parse($playlist->end_time)->format('H\hi\m');
            $volume = $playlist->volume / 100;
            
            $txt .= "   ({ $start-$end }, amplify($volume, $name)),\n";
        }

        $txt .= "
  ]),
  blank()
])

# Output ke Icecast
output.icecast(%mp3,
  host = \"localhost\",
  port = 8000,
  password = \"hackme\",
  mount = \"stream.mp3\",
  name = \"My Stream\",
  source
)
";

        $liqPath = base_path('../liquidsoap/radio.liq');
        file_put_contents($liqPath, $txt);

        return redirect()->back()->with('success', 'Konfigurasi berhasil disimpan');
    }
}
