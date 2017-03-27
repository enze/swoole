<?php
/**
 * swoole Client
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
 * swoole client
 *
 */
class Client extends \Swoole\Client {

	/*
	 * socket方式
	 */
	const SWOOLE_SOCKET_TCP = 'tcp';
	const SWOOLE_SOCKET_TCP6 = 'tcp6';
	const SWOOLE_SOCKET_UDP = 'udp';
	const SWOOLE_SOCKET_UDP6 = 'udp6';

	const SWOOLE_SOCKET_SYNC = 'sync';
	const SWOOLE_SOCKET_ASYNC = 'async';
	
	/*
	 * 应用长连接的key，如果为random则无效，否则可以复用
	 */
	const SWOOLE_RANDOM_KEY = 'random';
	const SWOOLE_STATIC_KEY = 'static';
	
	/*
	 * 仅在swoole_random_key模式下有效
	 */
	const SWOOLE_KEY_PREFIX = 'swoole_';
	
	/*
	 * client配置文件中的开关
	 */
	const SWOOLE_CONFIG_ON = 'on';
	const SWOOLE_CONFIG_OFF = 'off';
	
	/*
	 * 配置数组
	 */
	public $config = [];
	
	/*
	 * 当前需要连接的服务
	 */
	public $service = '';

	
	/**
	 * swoole client连接服务
	 *
	 * @param string $service 连接指定配置的服务
	 * @param mix $ini [string|false] 配置文件路径，默认启用系统自定义配置文件
	 *
	 * @return void
	 */
	public function __construct($service, $ini = false) {

		/*
		 * 设置服务
		 */
		$this->service = $service;
		
		/*
		 * 初始化配置文件
		 */
		$this->init($ini);
		
		/*
		 * 连接指定的服务
		 */
		if (true === isset($this->config[$service])) {
			parent::__construct($this->config[$service]['socket'], $this->config[$service]['sync'], $this->config[$service]['key']);
		} else {
			throw new \Exception('client was error');
		}
	}
	
	/**
	 * 初始化client参数配置
	 *
	 * @param mix $ini [string|false]
	 *
	 * @return void
	 */
	public function init($ini) {
		/*
		 * 是否使用自定义配置
		 */
		$config = parse_ini_file(false === $ini ? __DIR__ . '/../etc/client.ini' : $ini, true);

		$config[$this->service] = array_merge($config['global'], $config[$this->service]);
		unset($config['global']);
		
		/*
		 * 设置运行模式与socket方式
		 */
		foreach ($config as $key => $value) {
			if (true === empty($value)) {
				continue 1;
			}
			if (self::SWOOLE_SOCKET_SYNC == $value['sync']) {
				$value['sync'] = SWOOLE_SOCK_SYNC;
			} else {
				$value['sync'] = SWOOLE_SOCK_ASYNC;
				$value['timeout'] = 0;
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
				case self::SWOOLE_SOCKET_TCP:
				default:
					$value['socket'] = SWOOLE_TCP;
					break;
			}
			
			/*
			 * 设置连接的key
			 */
			if (self::SWOOLE_RANDOM_KEY == $value[$key . '.key']) {
				$value['key'] = uniqId(self::SWOOLE_KEY_PREFIX, true);
			} else {
				$value['key'] = $value['host'] . ':' . $value['port'];
			}
			
			/*
			 * 设置on off
			 */
			
			array_walk($value, function (& $item, $key) {
				$item = strtolower($item);
				if (self::SWOOLE_CONFIG_ON == $item) {
					$item = true;
				} else if (self::SWOOLE_CONFIG_OFF == $item) {
					$item = false;
				}
			});
			
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
		$setting = $this->config[$this->service];
		unset($setting['host'], $setting['port'], $setting['sync'], $setting['socket']);
		parent::set($setting);
	}
	
	/**
	 * 绑定回调事件
	 *
	 * @param array $callback ['event' => '', 'callback' => mix] 4种合法的php回调写法
	 *
	 * @return void
	 */
	protected function _bind(array $callback) {
		if (true === empty($callback)) {
			return false;
		}
		array_map(function ($cb) {
			parent::on($cb['event'], $cb['callback']);
		}, $callback);
	}
	
	public function conn() {
		return $this->connect(
			$this->config[$this->service]['host'], 
			$this->config[$this->service]['port'], 
			$this->config[$this->service]['timeout'], 
			$this->config[$this->service]['flag']
		);
	}
	
	/**
	 * 启动并连接服务
	 *
	 * @param array $callback [@see _bind]
	 *
	 * @return void
	 */
	public function boot(array $callback = []) {
		/*
		 * 设置配置信息
		 */
		$this->setting();
		/*
		 * 绑定回调事件
		 */
		$this->_bind($callback);
		
		return $this->connect(
			$this->config[$this->service]['host'], 
			$this->config[$this->service]['port'], 
			$this->config[$this->service]['timeout'], 
			$this->config[$this->service]['flag']
		);
	}
}