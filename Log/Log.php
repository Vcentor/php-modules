<?php
/**
 * Log system 
 *
 * @package  Log
 * @category Log
 * @author   vcentor
 */

namespace Log;

class Log {

	// log level
	const LOG_LEVEL_FATAL   = 0x01;
    const LOG_LEVEL_WARNING = 0x02;
    const LOG_LEVEL_NOTICE  = 0x04;
    const LOG_LEVEL_TRACE   = 0x08;
    const LOG_LEVEL_DEBUG   = 0x10;

    protected static $_arrLogLevels = array(
		self::LOG_LEVEL_FATAL 		=> 'fatal',
		self::LOG_LEVEL_WARNING		=> 'warning',
		self::LOG_LEVEL_NOTICE		=> 'notice',
		self::LOG_LEVEL_TRACE		=> 'trace',
		self::LOG_LEVEL_DEBUG		=> 'debug',
    );

	// log message
	protected $_messages = array();

	// log address
	protected $_directory;

	// log name
	protected $_filename;

	// 注册需要记录日志的级别，大于此level的线上不记录日志，比如debug日志
	protected $_level;

	protected static $_instance = NULL;

	/**
	 * GET THE SINGLETON INSTANCE
	 * 
	 * @param 	void
	 * @param 	object
	 */
	public static function instance() {
		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * 注册日志(日志地址和程序终止回调函数)
	 *
	 * @param 	string 	$directory
	 * @param 	int 	$level
	 * @return 	void
	 */
	public function initLog(string $directory, int $level = NULL) {
		$this->_level = $level;
		$this->directory($directory);
		register_shutdown_function(array(self::instance(), 'write'));
	}

	/**
	 * 处理日志文件路径
	 * 
	 * @param  string $directory
	 * @return void
	 */
	public function directory(string $directory) {
		$this->_directory = rtrim($directory, DIRECTORY_SEPARATOR);
		if ( ! is_dir($directory)) {
			mkdir($this->_directory, 02777, true);
			chmod($this->_directory, 02777);
		}
	}

	/**
	 * LOG ID
	 *
	 * @param  void
	 * @return integer
	 */
	public function genLogID() {
		$arr = gettimeofday();
		$logId = ((($arr['sec'] * 100000 + $arr['usec'] / 10) & 0x7FFFFFFF) | 0x80000000);

		return $logId;
	}


	/**
	 * GET COMMON LOG INFO
	 *
	 * @param 	string 	$message
	 * @return 	string
	 */
	protected function _log_info(string $message) {
		$request_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL;
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;

		$trace = $this->_debug_back_trace();
		$file = isset($trace[0]['file']) ? $trace[0]['file'] : NULL;
		$line = isset($trace[0]['line']) ? $trace[0]['line'] : NULL;
		$class = isset($trace[0]['class']) ? $trace[0]['class'] : NULL;
		$method = isset($trace[0]['function']) ? $trace[0]['function'] : NULL;

		$log_info  = '['.date('Y-m-d H:i:s').']';
		$log_info .= '[request_ip= '.$request_ip.']';
		$log_info .= '[request_uri= '.$request_uri.']';
		$log_info .= '[referer= '.$referer.']';
		$log_info .= '[cookie='.json_encode($_COOKIE).']';
		$log_info .= '[file='.$file.' line='.$line.' class='.$class.' method='.$method.'][msg='.$message.']';
		$log_info .= '[logid='.$this->genLogID().']';
		$log_info .= PHP_EOL;
		return $log_info;
	}


	/**
	 * DEBUG BACK TRACE
	 *
	 * @param 	void
	 * @return 	array
	 */
	protected function _debug_back_trace() {
		if ( ! defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
			$trace = array_map(function($item){
				unset($item['args']);
				return $item;
			}, array_slice(debug_backtrace(FALSE), 3));
		} else {
			$trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 3);
		}
		return $trace;
	}

	/**
	 * DEBUG
	 * 
	 * @param 	string 	$message
	 * @return 	void
	 */
	public static function debug(string $message) {
		$log_info = self::instance()->_log_info($message);
		self::instance()->_messages[self::LOG_LEVEL_DEBUG][] = $log_info;
	}

	/**
	 * NOTICE
	 * 
	 * @param 	string 	$message
	 * @return 	void
	 */
	public static function notice(string $message) {
		$log_info = self::instance()->_log_info($message);
		self::instance()->_messages[self::LOG_LEVEL_notice][] = $log_info;
	}

	/**
	 * WARNING
	 * 
	 * @param 	string 	$message
	 * @return 	void
	 */
	public static function warning(string $message) {
		$log_info = self::instance()->_log_info($message);
		self::instance()->_messages[self::LOG_LEVEL_WARNING][] = $log_info;
	}

	/**
	 * TRACE
	 * 
	 * @param 	string 	$message
	 * @return 	void
	 */
	public static function trace(string $message) {
		$log_info = self::instance()->_log_info($message);
		self::instance()->_messages[self::LOG_LEVEL_TRACE][] = $log_info;
	}

	/**
	 * FATAL
	 * 
	 * @param 	string 	$message
	 * @return 	void
	 */
	public static function fatal(string $message) {
		$log_info = self::instance()->_log_info($message);
		self::instance()->_messages[self::LOG_LEVEL_FATAL][] = $log_info;
	}

	/**
	 * 终止程序回调函数
     *
     * @param  void
     * @return void
     */
	public function write() {

		if (empty($this->_messages)) {
			return;
		}

		foreach ($this->_messages as $level => $message) {

			if ($level > $this->_level) {
				continue;
			}

			$filename = $this->_directory.DIRECTORY_SEPARATOR.date('YmdH').'.'.self::$_arrLogLevels[$level].'.log';

			if ( ! file_exists($filename)) {
				// create a log file
				file_put_contents($filename, date('Y-m-d H:i:s') . ' log begin'.PHP_EOL);
			}

			foreach ($message as $diff_level_info) {
				file_put_contents($filename, $diff_level_info, FILE_APPEND);
			}
		}

		unset($this->_messages);
	}
}