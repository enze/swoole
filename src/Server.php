<?php
/**
 * swoole server
 *
 * @category php
 * @package xb.swoole
 * @author enze.wei <[enzewei@gmail.com]>
 * @copyright 2017 xbsoft
 * @license http://xbsoft.net/licenses/mit.php MIT License
 * @version Develop 1.0.0
 * @link http://swoole.xbsoft.net
 */
namespace xb\swoole;

/**
 * swoole server
 *
 */
class Server extends \Swoole\Server {
	
	/*
	 * swoole server 运行模式
	 */
	const SWOOLE_MODE_PROCESS = 'process';
	const SWOOLE_MODE_BASE = 'base';
	
	/*
	 * socket方式
	 */
	const SWOOLE_SOCKET_TCP = 'tcp';
	const SWOOLE_SOCKET_TCP6 = 'tcp6';
	const SWOOLE_SOCKET_UDP = 'udp';
	const SWOOLE_SOCKET_UDP6 = 'udp6';
	const SWOOLE_SOCKET_DGRAM = 'dgram';
	const SWOOLE_SOCKET_STREAM = 'stream';
	
	/*
	 * 配置数组
	 */
	public $config = [];
	
	/*
	 * 当前运行的服务
	 */
	public $service = '';
	
	/**
	 * swoole server开启服务
	 *
	 * @param mix $service [string|false] 默认运行所有配置的服务，可指定运行某一个服务
	 * @param mix $ini [string|false] 配置文件路径，默认启用系统自定义配置文件
	 *
	 * @return void
	 */
	public function __construct($service = false, $ini = false) {

		/*
		 * 设置服务
		 */
		$this->service = $service;
		
		/*
		 * 初始化配置文件
		 */
		$this->init($ini);
		
		/*
		 * 未指定服务
		 */
		if (true === empty($service)) {
			/*
			 * 开启global的swoole server
			 */
			parent::__construct($this->config['global']['host'], $this->config['global']['port'], $this->config['global']['mode'], $this->config['global']['socket']);
			
			/*
			 * 增加其他任意已配置类型的服务
			 */
			foreach ($this->config as $key => $value) {
				/*
				 * 不含已经开启的global以及未指定配置的服务
				 */
				if ('global' === $key || true === empty($value)) {
					continue 1;
				}
				/*
				 * 增加监听
				 */
				$this->addListener($this->config[$key]['host'], $this->config[$key]['port'], $this->config[$key]['socket']);
			}
		} else if (true === isset($this->config[$service])) {
			/*
			 * 指定运行的服务
			 */
			parent::__construct($this->config[$service]['host'], $this->config[$service]['port'], $this->config[$service]['mode'], $this->config[$service]['socket']);
		} else {
			throw new \Exception('service was error');
		}
	}
	
	/**
	 * 初始化server参数配置
	 *
	 * @param mix $ini [string|false]
	 *
	 * @return void
	 */
	public function init($ini) {
		/*
		 * 是否使用自定义配置
		 */
		$config = parse_ini_file(false === $ini ? __DIR__ . '/../etc/server.ini' : $ini, true);
		
		/*
		 * 如果指定服务，则合并该服务下的配置与全局配置
		 */
		if (false === empty($this->service) && true === isset($this->config[$this->service])) {
			$this->config[$this->service] = array_merge($config['global'], $config[$this->service]);
		}
		
		/*
		 * 设置运行模式与socket方式
		 */
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
	
	/**
	 * 设置配置信息
	 *
	 * @param array $setting 缺省
	 *
	 * @return void
	 */
	public function setting($setting = []) {
		
		/*
		 * 指定某一个服务运行仅设置该服务下的配置
		 */
		if (false === empty($this->service) && true === isset($this->config[$this->service])) {
			$this->config[$this->service] = array_merge($this->config['global'], $this->config[$this->service]);
			$setting = $this->config[$this->service];
			unset($setting['host'], $setting['port'], $setting['mode'], $setting['socket']);
			parent::set($setting);
		} else {
			/*
			 * 设置所有配置信息
			 */
			foreach ($this->config as $key => $value) {
				unset($value['host'], $value['port'], $value['mode'], $value['socket']);
				$setting[$key] = $value;
				parent::set($setting[$key]);
			}
		}
		
	}
	
	/**
	 * 绑定回调事件
	 *
	 * @param array $callback ['event' => '', 'callback' => mix] 4种合法的php回调写法
	 *
	 * @return void
	 */
	protected function _bind(array $callback) {
		array_map(function ($cb) {
			parent::on($cb['event'], $cb['callback']);
		}, $callback);
	}
	
	/**
	 * 启动并加载服务
	 *
	 * @param array $callback [@see _bind]
	 *
	 * @return void
	 */
	public function boot(array $callback) {
		/*
		 * 设置配置信息
		 */
		$this->setting();
		/*
		 * 绑定回调事件
		 */
		$this->_bind($callback);
		/*
		 * 开启服务
		 */
		parent::start();
	}
}