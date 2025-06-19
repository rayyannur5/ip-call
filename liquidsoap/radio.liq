tes = playlist("/opt/lampp/htdocs/ip-call/playlist/tes.m3u", mode="normal", reload_mode="watch")

adzan_playlist = once(playlist("/opt/lampp/htdocs/ip-call/playlist/adzan.m3u"))
adzan_subuh_playlist = once(playlist("/opt/lampp/htdocs/ip-call/playlist/adzan_subuh.m3u"))

# Gabungkan dengan fallback
source = fallback(track_sensitive=false, [
  switch([
    #=#
            ({ 04h12m }, amplify(0.10, id="adzan_subuh_amplify", adzan_subuh_playlist)), #subuh
            ({ 11h27m }, amplify(0.10, id="adzan_amplify", adzan_playlist)), #dhuhur
            ({ 14h48m }, amplify(0.10, id="adzan_amplify", adzan_playlist)), #ashar
            ({ 17h18m }, amplify(0.10, id="adzan_amplify", adzan_playlist)), #maghrib
            ({ 21h12m }, amplify(0.10, id="adzan_amplify", adzan_playlist)), #isya
        #=#
   ({ 20h10m-22h10m }, amplify(0.03, tes)),

  ]),
  blank()
])

# Output ke Icecast
output.icecast(%mp3,
  host = "localhost",
  port = 8000,
  password = "hackme",
  mount = "stream.mp3",
  name = "My Stream",
  source
)
