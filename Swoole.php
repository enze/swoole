<?php

defined('XB_PATH') or define('XB_PATH', __DIR__);

class Swoole {
	
	static public $classMap = [];
	
	static public function autoload($className) {
		if (true === isset(static::$classMap[$className])) {
			$classFile = static::$classMap[$className];
		}
		
		include_once $classFile;
	}
}

spl_autoload_register(['Swoole', 'autoload'], true, true);
Swoole::$classMap = include_once(__DIR__ . '/classmap.php');