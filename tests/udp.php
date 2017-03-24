<?php

$message = 'hello world!';
/*
 * 创建UDP连接
 */
$fp = stream_socket_client('udp://127.0.0.1:29001', $errno, $errstr);
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