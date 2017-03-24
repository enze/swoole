<?php

include_once __DIR__ . '/../Swoole.php';

class LogServer {
	
	static public $server = null;
	
	static public function start() {
		self::$server = new xb\swoole\Server('log');
		$callback = [
			[
				'event' => 'receive',
				'callback' => ['LogServer', 'onReceive'],
			],
			[
				'event' => 'task',
				'callback' => ['LogServer', 'onTask'],
			],
			[
				'event' => 'finish',
				'callback' => ['LogServer', 'onFinish'],
			],
		];
		self::$server->boot($callback);
	}
	
	static public function onReceive($server, $fd, $fromId, $data) {
		$server->task($data);
		$server->close($fd);
	}
	
	static public function onTask($server, $taskId, $workerId, $data) {
		return self::log($server, $taskId, $workerId, $data);
	}
	
	static public function onFinish($server, $taskId, $data) {
		return 'finish';
	}
	
	static public function getLogkDir($dir, $format = 'Y') {
		$dir .= date($format, time());
		clearstatcache(true);
		if (false === file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		return $dir;
	}
	
	static public function log($server, $taskId, $workerId, $data) {
		$logDir = self::getLogkDir(self::$server->config['log']['log_dir']);
		$logFile = $logDir . DIRECTORY_SEPARATOR . self::$server->config['log']['log_prefix'] . 'log.' . date('m.d', time());
		
		$fp = fopen($logFile, "ab+");
		flock($fp, LOCK_EX);
		fputs($fp, '[' . $workerId . '#' . $taskId . '] ' . $data . "\r\n");
		flock($fp, LOCK_UN);
		fclose($fp);
		return true;
	}
}

LogServer::start();