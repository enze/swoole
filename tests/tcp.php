<?php

$data = [
	'type' => 'notify',
	'phone' => 'xxxxx',
	'message' => '1234',
];

$message = json_encode($data);

/*
$fp = stream_socket_client('tcp://127.0.0.1:8223', $errno, $errstr);
if (!$fp) {
	echo "ERROR: $errno - $errstr<br />\n";
} else {
	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);
}
*/