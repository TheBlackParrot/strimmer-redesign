<?php
// -- Program settings --
$prog_title = "Strimmer";
$prog_title_short = "strimmer";
$prog_internal_url = "https://strimmer.example.com";
date_default_timezone_set("America/Chicago");

// -- Email settings --
$email['alerts_enabled'] = true;
$email['to'] = "strimmer@example.com";
$email['from'] = "no-reply@example.com";

// -- Registration settings --
$register['require_verification'] = true;

// -- Strimmer log settings --
$logging['enabled'] = true;
$logging['dir'] = "SYSTEM/logs";
$logging['compress_old_logs'] = true;

// -- Session settings --
ini_set('session.gc_maxlifetime', 21600);
session_set_cookie_params(21600);

// -- SQL --
// hostname to connect to
$sql['host'] = "localhost";
$sql['port'] = 3306;
// SQL credentials
$sql['user'] = "sqluser";
$sql['pass'] = "hackme";
// database that stores info for the cache list
$sql['db'] = "strimmer";
// defines the SQL connection
$mysqli = new mysqli($sql['host'], $sql['user'], $sql['pass'], $sql['db'], $sql['port']);

// -- Icecast --
$icecast['host'] = "localhost";
// public facing url
$icecast['public_url'] = "radio.example.us";
$icecast['port'] = 8000;
// source password
$icecast['pass'] = "source_password";
$icecast['admin_user'] = "admin";
$icecast['admin_pass'] = "hackme";
// ffmpeg compatible transcoder
$icecast['ffmpeg'] = "ffmpeg";
// ffprobe compatible stream information viewer
$icecast['ffprobe'] = "ffprobe";

// Stream Outputs
$stream['outputs'] = [];
// recommended to use -filter_complex with "asplit=%%STREAM_COUNT%%%%STREAM_OUTPUTS_FILTER%%" at the end
// use "-map '[%%MOUNT%%]'" if using asplit
$stream['filter'] = '-filter_complex "compand=0|0:0.2|0.2:-90/-900|-70/-70|-30/-9|0/-3:2:1.33:0:0,asplit=%%STREAM_COUNT%%%%STREAM_OUTPUTS_FILTER%%"';
$stream['outputs']['mp3q6.mp3'] = "-map '[%%MOUNT%%]' -codec:a libmp3lame -vn -strict -2 -q 6";
$stream['outputs']['mp3q6m.mp3'] = "-map '[%%MOUNT%%]' -codec:a libmp3lame -vn -strict -2 -ar 32000 -ac 1 -q 6";
$stream['outputs']['opus64.opus'] = "-map '[%%MOUNT%%]' -codec:a libopus -vn -strict -2 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a 64k -content_type 'audio/ogg'";
$stream['outputs']['opus48.opus'] = "-map '[%%MOUNT%%]' -codec:a libopus -vn -strict -2 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a 48k -content_type 'audio/ogg'";
$stream['outputs']['opus32.opus'] = "-map '[%%MOUNT%%]' -codec:a libopus -vn -strict -2 -ac 1 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a 32k -cutoff 12000 -content_type 'audio/ogg'";
$stream['outputs']['opus24.opus'] = "-map '[%%MOUNT%%]' -codec:a libopus -vn -strict -2 -ac 1 -vbr on -compression_level 0 -frame_duration 40 -packet_loss 5 -b:a 24k -cutoff 8000 -content_type 'audio/ogg'";
$stream['names'] = [];
$stream['names']['mp3q6.mp3'] = "MP3 VBR Q6";
$stream['names']['mp3q6m.mp3'] = "MP3 VBR Q6 (Mono)";
$stream['names']['opus64.opus'] = "Opus Audio 64k";
$stream['names']['opus48.opus'] = "Opus Audio 48k";
$stream['names']['opus32.opus'] = "Opus Audio 32k (Mono)";
$stream['names']['opus24.opus'] = "Opus Audio 24k (Mono)";

// Twurl
$twitter['enable'] = false;
$twitter['twurl_location'] = "/path/to/twurl/executable";

// API keys
// same key used in GET requests requiring client_id
$sc_api_key = "123ab4c5678de9f0ab1c23456789d0ef";
$jm_api_key = "ab0c1234";
$ma_api_key = "";