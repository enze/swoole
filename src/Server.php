<?php
namespace xb\swoole;

class Server extends \Swoole\Server {
	
	const SWOOLE_MODE_PROCESS = 'process';
	const SWOOLE_MODE_BASE = 'base';
	
	const SWOOLE_SOCKET_TCP = 'tcp';
	const SWOOLE_SOCKET_TCP6 = 'tcp6';
	const SWOOLE_SOCKET_UDP = 'udp';
	const SWOOLE_SOCKET_UDP6 = 'udp6';
	const SWOOLE_SOCKET_DGRAM = 'dgram';
	const SWOOLE_SOCKET_STREAM = 'stream';
	
	public $config = [];
	
	public $service = '';
	
	public function __construct($service = false, $ini = false) {

		$this->service = $service;
		
		$this->init($ini);
		
		if (true === empty($service)) {
			parent::__construct($this->config['global']['host'], $this->config['global']['port'], $this->config['global']['mode'], $this->config['global']['socket']);
			foreach ($this->config as $key => $value) {
				if ('global' === $key || true === empty($value)) {
					continue 1;
				}
				$this->addListener($this->config[$key]['host'], $this->config[$key]['port'], $this->config[$key]['socket']);
			}
		} else if (true === isset($this->config[$service])) {
			parent::__construct($this->config[$service]['host'], $this->config[$service]['port'], $this->config[$service]['mode'], $this->config[$service]['socket']);
		} else {
			throw new \Exception('service was error');
		}
	}
	
	public function init($ini) {
		$config = parse_ini_file(false === $ini ? __DIR__ . '/../etc/server.ini' : $ini, true);
		
		if (false === empty($this->service) && true === isset($this->config[$this->service])) {
			$this->config[$this->service] = array_merge($config['global'], $config[$this->service]);
		}
		foreach ($config as $key => $value) {
			if (true === empty($value)) {
				continue 1;
			}
			if (self::SWOOLE_MODE_PROCESS == $value['mode']) {
				$value['mode'] = SWOOLE_PROCESS;
			} else {
				$value['mode'] = SWOOLE_BASE;
			}
			
			switch ($value['socket']) {
				case self::SWOOLE_SOCKET_TCP6:
					$value['socket'] = SWOOLE_TCP6;
					break;
				case self::SWOOLE_SOCKET_UDP:
					$value['socket'] = SWOOLE_UDP;
					break;
				case self::SWOOLE_SOCKET_UDP6:
					$value['socket'] = SWOOLE_UDP6;
					break;
				case self::SWOOLE_SOCKET_DGRAM:
					$value['socket'] = SWOOLE_UNIX_DGRAM;
					break;
				case self::SWOOLE_SOCKET_STREAM:
					$value['socket'] = SWOOLE_UNIX_STREAM;
					break;
				case self::SWOOLE_SOCKET_TCP:
				default:
					$value['socket'] = SWOOLE_TCP;
					break;
			}
			$this->config[$key] = $value;
		}
	}
	
	public function setting($setting = []) {
		
		if (false === empty($this->service) && true === isset($this->config[$this->service])) {
			$this->config[$this->service] = array_merge($this->config['global'], $this->config[$this->service]);
			$setting = $this->config[$this->service];
			unset($setting['host'], $setting['port'], $setting['mode'], $setting['socket']);
			parent::set($setting);
		} else {
			foreach ($this->config as $key => $value) {
				unset($value['host'], $value['port'], $value['mode'], $value['socket']);
				$setting[$key] = $value;
				parent::set($setting[$key]);
			}
		}
		
	}
	
	protected function _bind(array $callback) {
		array_map(function ($cb) {
			parent::on($cb['event'], $cb['callback']);
		}, $callback);
	}
	
	public function boot(array $callback) {
		$this->setting();
		$this->_bind($callback);
		parent::start();
	}
}