test = playlist("/home/nursecallserver/ip-call/ip-call-v2/public/playlist/test.m3u", mode="normal", reload_mode="watch")

adzan_playlist = playlist("/home/nursecallserver/ip-call/ip-call-v2/public/playlist/adzan.m3u")
adzan_subuh_playlist = playlist("/home/nursecallserver/ip-call/ip-call-v2/public/playlist/adzan_subuh.m3u")

# Gabungkan dengan fallback
source = fallback(track_sensitive=false, [
  switch([
    #=#
        adzan
    #=#
   ({ 12h00m-23h00m }, amplify(1, test)),

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
