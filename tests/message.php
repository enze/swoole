<?php

include_once __DIR__ . '/../Swoole.php';

//use xb\format\Data as FormatData;

class MessageServer {
	
	static public $server = null;
	static public $format = null;
	
	static public function start() {
		self::$format = new FormatData;
		self::$server = new xb\swoole\Server('message');
		$callback = [
			[
				'event' => 'receive',
				'callback' => ['MessageServer', 'onReceive'],
			],
			[
				'event' => 'task',
				'callback' => ['MessageServer', 'onTask'],
			],
			[
				'event' => 'finish',
				'callback' => ['MessageServer', 'onFinish'],
			],
		];
		self::$server->boot($callback);
	}
	
	static public function onReceive($server, $fd, $fromId, $data) {
		$server->task($data);
		$server->close($fd);
	}
	
	static public function onTask($server, $taskId, $workerId, $data) {
		return self::sendMessage($server, $taskId, $workerId, $data);
	}
	
	static public function onFinish($server, $taskId, $data) {
		return 'finish';
	}
	
	static public function sendMessage($server, $taskId, $workerId, $data) {
		//$data = self::$format->decode($data);
		var_dump($data);
	}
}

MessageServer::start();