<?php

$data = [
	'type' => 'captcha',
	'phone' => '15902143887',
	'message' => '1234',
];

$message = json_encode($data);

/*
 * 创建UDP连接
 */
$fp = stream_socket_client('tcp://127.0.0.1:8223', $errno, $errstr);
if (!$fp) {
	echo "ERROR: $errno - $errstr<br />\n";
} else {
	/*
	 * 简单发送日志信息
	 */
	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);
}