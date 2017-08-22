<?php
/**
 * Session to redis class
 *
 * @package  Session
 * @category Session
 * @author   vcentor
 */

namespace Session;
use SessionHandlerInterface;
use Session\SessionException;

class Session implements SessionHandlerInterface {

	// Session instance
	protected static $_instance = NULL;

	// redis client
	protected $_redis;

	// expire time
	protected $_expire = 3600;

	/**
     * start session
     *
     * @param 	object 	$redis
     * @param 	int 	$expire
     * @return 	void
     */
	public function initSession($redis, $expire = NULL) {

		if ( ! is_object($redis)) {
			throw new SessionException("Invalid param redis!", SessionException::INVALID_PARAM);
		}

		$this->_redis = $redis;

		if ($expire !== NULL) {
			$this->_expire = $expire;
		}

		if (PHP_VERSION >= '5.4.0') {
			session_set_save_handler($this, true);
		} else {
			session_set_save_handler(
				array($this, 'open'), 
				array($this, 'close'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destory'),
				array($this, 'gc'),
			);
			register_shutdown_function("session_write_close");
		}

		session_start();
	}

	/**
	 * Get the singleton instance
	 *
	 * @param  void
	 * @return void
	 */
	public static function instance() {

		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * open
	 *
	 * @param 	string 	$save_path
	 * @param 	string 	$session_name
	 * @return 	boolean
	 */
	public function open(string $save_path, string $session_name) {
		return true;
	}

	/**
	 * read
	 *
	 * @param 	string 	$session_id
	 * @return 	string 
	 */
	public function read(string $session_id) {

		if ($value = $this->_redis->get($session_id)) {
			return $value;
		} else {
			return "";
		}
	}

	/**
	 * write
	 * 
	 * @param 	string 	$session_id
	 * @param 	string 	$data
	 * @return 	boolean
	 */
	public function write(string $session_id, string $data) {
		return $this->_redis->setex($session_id, $this->_expire, $data);
	}

	/**
	 * close
	 *
	 * @param  void
	 * @return boolean
	 */
	public function close() {
		return true;
	}

	/**
     * gc
     *
     * @param 	integer 	$max_life_time
     * @return 	boolean
     */
	public function gc(int $max_life_time) {
		return true;
	}

	/**
	 * destroy
	 *
	 * @param 	string 	$session_id
	 * @return 	boolean
	 */
	public function destroy(string $session_id) {
		return $this->_redis->delete($session_id);
	}
}