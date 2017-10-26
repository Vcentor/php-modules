<?php
/**
 * Mysqli Database result.
 *
 * @package  Dao\Mysql\Result\Mysqli
 * @category Result
 * @author 	 vcentor
 */
namespace Dao\Mysql\Result\Mysqli;
use Dao\Mysql\Result\Result;

class MySQLiResult extends Result {

	protected $_internal_row = 0;

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL) {
		parent::__construct($result, $sql, $as_object, $params);

		$this->_total_rows = $result->num_rows;
	}

	public function __destruct() {
		if (is_resource($this->_result)) {
			$this->_result->free();
		}
	}

	/**
	 * Implements SeekableIterator::seek
	 * @param  integer $offset
	 * @return boolean
	 */
	public function seek($offset) {
		if ($this->offsetExists($offset) AND $this->_result->data_seek($offset)) {
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
			return $this->_result->fetch_object();
		}

		if (is_string($this->_as_object)) {
			return $this->_result->fetch_object($this->_as_object, (array) $this->_object_params);
		}

		return $this->_result->fetch_assoc();
	}
}