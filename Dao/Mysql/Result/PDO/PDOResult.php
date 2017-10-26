<?php
/**
 * Mysql Database Result.
 *
 * @package  Dao\Mysql\Result\PDO
 * @category Result
 * @author 	 vcentor
 */

namespace Dao\Mysql\Result\PDO;
use Dao\Mysql\Result\Result;

class MySQLResult extends Result {

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL) {
		parent::__construct($result, $sql, $as_object, $params);

		$this->_total_rows = count($result);
	}

	public function __destruct() {

	}

	/**
	 * Implements SeekableIterator::seek
	 * @param  integer $offset
	 * @return boolean
	 */
	public function seek($offset) {
		if ($this->offsetExists($offset)) {
			$this->_current_row = $offset;
		}
	}

	/**
	 * Implements Iterator::current
	 * move pointer by seek
	 * @return
	 */
	public function current() {
		return $this->valid() ? $this->_result[$this->_current_row] : NULL;
	}
}
