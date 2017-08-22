<?php
/**
 * cookie
 *
 * @package 	Session
 * @category 	Cookie
 * @author 		vcentor
 */

namespace Session;

class Cookie {

	// Session instance
	protected static $_instance = NULL;

	// expire time
	protected $_expire = 0;

	// path
	protected $_path = '/';

	// domain
	protected $_domain = '';

	// secure
	protected $_secure = FALSE;

	// httponly
	protected $_http_only = FALSE;

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
	 * Sets expire
	 *
	 * @param 	int 	$expire
	 * @return 	$this
	 */
	public function expire(int $expire) {

		if ( ! empty($expire)) {
			$this->_expire = $expire;
		}

		return $this;
	}

	/**
	 * Sets path
	 * 
	 * @param 	string 	$path
	 * @return 	$this
	 */
	public function path(string $path) {

		if ( ! empty($path)) {
			$this->_path = rtrim($path, '/').'/';
		}

		return $this;
	}

	/**
	 * Sets domain
	 *
	 * @param 	string 	$domain
	 * @return 	$this
	 */
	public function domain(string $domain) {

		if ( ! empty($domain)) {
			$this->_domain = $domain;
		}

		return $this;
	}

	/**
	 * Sets secure
	 *
	 * @param 	boolean 	$secure
	 * @return 	$this
	 */
	public function secure(bool $secure) {

		$this->_secure = (bool) $secure;

		return $this;
	}

	/**
	 * Sets httponly
	 *
	 * @param 	boolean 	$http_only
	 * @return 	$this
	 */
	public function http_only(bool $http_only) {

		$this->_http_only = (bool) $http_only;

		return $this;
	}

	/**
	 * set cookie
	 *
	 * @param 	string 	$name
	 * @param 	string 	$value
	 * @return  boolean
	 */
	public function set(string $name, string $value = NULL) {
		return setcookie($name, $value, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_http_only);
	}

	/**
	 * delete cookie
	 * 
	 * @param 	string 	$name
	 * @return 	boolean
	 */
	public function delete(string $name) {
		return $this->expire(-1)->set($name);
	}
}