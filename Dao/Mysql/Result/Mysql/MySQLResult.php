<?php
/**
 * Mysql Database Result.
 *
 * @package  Dao\Mysql\Result\Mysql
 * @category Result
 * @author 	 vcentor
 */

namespace Dao\Mysql\Result\Mysql;
use Dao\Mysql\Result\Result;

class MySQLResult extends Result {

	protected $_internal_row = 0;

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL) {
		parent::__construct($result, $sql, $as_object, $params);

		$this->_total_rows = mysql_num_rows($result);
	}

	public function __destruct() {
		if (is_resource($this->_result)) {
			mysql_free_result($this->_result);
		}
	}

	/**
	 * Implements SeekableIterator::seek
	 * @param  integer $offset
	 * @return boolean
	 */
	public function seek($offset) {
		if ($this->offsetExists($offset) AND mysql_data_seek($this->_result, $offset)) {
			$this->_current_row = $this->_internal_row = $offset;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Implements Iterator::current
	 * move pointer by seek
	 * @return
	 */
	public function current() {
		if ($this->_current_row !== $this->_internal_row AND ! $this->seek($this->_current_row)) {
			return NULL;
		}

		$this->_internal_row++;

		if ($this->_as_object === TRUE) {
			return mysql_fetch_object($this->_result);
		}

		if (is_string($this->_as_object)) {
			if ($this->_object_params !== NULL) {
				return mysql_fetch_object($this->_result, $this->_as_object, $this->_object_params);
			}
			return mysql_fetch_object($this->_result, $this->_as_object);
		}

		return mysql_fetch_assoc($this->_result);
	}
}
