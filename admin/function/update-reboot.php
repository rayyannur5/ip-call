<?php

require_once('../config.php');

$beds = queryArray("SELECT * FROM bed");

$pjsip = fopen("/etc/asterisk/pjsip.conf", "w") or die("Unable to open file!");
$extensions = fopen("/etc/asterisk/extensions.conf", "w") or die("Unable to open file!");

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
    $bed_id = $bed['id'];
    $bed_name = $bed['username'];

    if ($bed['tw'] == 1) {
        $txt = $txt . "
[$bed_id](endpoint_basic)
auth=$bed_id
aors=$bed_id
callerid=\"$bed_name\" <$bed_id>
[$bed_id](authentication)
password=$bed_id
username=$bed_id
[$bed_id](aor_template)

";
        $txt_extensions = $txt_extensions . "
exten => $bed_id,1,Set(datetime=\${STRFTIME(\${EPOCH},,%Y%m%d-%H%M%S)})
same => n,Set(recording_file=/opt/lampp/htdocs/records/\${datetime}.wav)
same => n,MixMonitor(\${recording_file})
same => n,Dial(PJSIP/$bed_id,10)
same => n,Hangup()     
    ";
    }
}

fwrite($pjsip, $txt);
fwrite($extensions, $txt_extensions);
fclose($pjsip);
fclose($extensions);


$output = exec('reboot');
echo $output;
