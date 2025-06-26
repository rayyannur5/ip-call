<?php
require_once('../../config.php');

session_start();

$playlists = queryArray("SELECT * FROM playlist");
foreach ($playlists as $playlist) {

    $id = $playlist['id'];
    $playlist['items'] = queryArray("SELECT * FROM playlist_item WHERE id = '$id'");

    $filename = "../../../playlist/" . str_replace(" ", "_", $playlist['name']) . ".m3u";

    $m3ufile = fopen($filename, "w");

    foreach ($playlist['items'] as $playlist_item) {
        fwrite($m3ufile, "/opt/lampp/htdocs/ip-call/playlist/music/" . $playlist_item['path'] . "\n");
    }

    fclose($m3ufile);

}

$txt = "";

foreach($playlists as $playlist) {
    $name = str_replace(" ", "_", $playlist['name']);
    $txt .= "$name = playlist(\"/opt/lampp/htdocs/ip-call/playlist/$name.m3u\", mode=\"normal\", reload_mode=\"watch\")" . "\n";
}

$txt .= "
adzan_playlist = playlist(\"/opt/lampp/htdocs/ip-call/playlist/adzan.m3u\")
adzan_subuh_playlist = playlist(\"/opt/lampp/htdocs/ip-call/playlist/adzan_subuh.m3u\")

# Gabungkan dengan fallback
source = fallback(track_sensitive=false, [
  switch([
    #=#
        adzan
    #=#
";

foreach($playlists as $playlist) {
    $name = str_replace(" ", "_", $playlist['name']);
    $start = new DateTime($playlist['start_time']);
    $start_formatted = $start->format('H\hi\m');
    $end = new DateTime($playlist['end_time']);
    $end_formatted = $end->format('H\hi\m');
    $volume = $playlist['volume']/100;
    $txt .= "   ({ $start_formatted-$end_formatted }, amplify($volume, $name)),\n";
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

$filename = "../../../liquidsoap/radio.liq";
$radioLiq = fopen($filename, "w");
fwrite($radioLiq, $txt);
fclose($radioLiq);

$_SESSION['flash-message'] = [
    'success' => true,
    'message' => 'Berhasil'
];
header('location: ../../setting_music.php');