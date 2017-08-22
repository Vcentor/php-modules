<?php
/**
 * utils
 * 
 * @package  Utils
 * @category Utils
 * @author   vcentor
 */

namespace Utils;
use Exception;

class HttpClientUtility {

	/**
	 * get
	 *
	 * @param string $url
	 * @param mixed $sendData
	 * @param array $header
	 * @return
	 */
	public function get($url, $sendData, $header = array()) {
		return $this->_send($url, strtoupper(__FUNCTION__), $sendData);

		
	}

    /**
	 * post
	 *
	 * @param string $url
	 * @param mixed $sendData
	 * @param array $header
	 * @return
	 */
	public function post($url, $sendData, $header = array()) {
		return $this->_send($url, strtoupper(__FUNCTION__), $sendData);
	}

	/**
	 * put
	 *
	 * @param string $url
	 * @param mixed $sendData
	 * @param array $header
	 * @return
	 */
	public function put($url, $sendData, $header = array()) {
		return $this->_send($url, strtoupper(__FUNCTION__), $sendData);
	}

	/**
	 * delete
	 *
	 * @param string $url
	 * @param mixed $sendData
	 * @param array $header
	 * @return
	 */
	public function delete($url, $sendData, $header = array()) {
		return $this->_send($url, strtoupper(__FUNCTION__), $sendData);
	}


	/**
	 * 发送http请求
	 *
	 * @param string $url
	 * @param string $method
	 * @param mixed $sendData
	 * @param array $header
	 * @return 
	 */
	private function _send($url, $method, $sendData = null, $header = array()) {
		$ch = curl_init();

		if ($method === 'GET' && !empty($sendData)) {
			if (strpos($url, '?') !== false) {
				if (is_string($sendData)) {
					$url .= '&' . $sendData;
				}
				if (is_array($sendData)) {
					$url .= '&' . http_build_query($sendData);
				}
			} else {
				if (is_string($sendData)) {
					$url .= '?' . $sendData;
				}
				if (is_array($sendData)) {
					$url .= '?' . http_build_query($sendData);
				}
			}
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);

		if ($method === 'POST' || $method === 'PUT') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
		}
		
		if (!empty($header)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
					
		$ret = curl_exec($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if (!$ret || $errno != 0) {
			throw new Exception("Request Service Failed!", $errno);
		}
		return $ret;
	}

}
