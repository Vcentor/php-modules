<?php
/**
 * Database result wrapper.
 *
 * @package  Dao\Mysql\Result
 * @category result
 * @author 	 vcentor
 */
namespace Dao\Mysql\Result;
use \Iterator;
use \Countable;
use \ArrayAccess;
use \SeekableIterator;
use Dao\Mysql\Exception\MysqlException;

abstract class Result implements Countable, Iterator, SeekableIterator, ArrayAccess {

	/**
	 * @var Executed SQL for this result
	 */
	protected $_query;

	/**
	 * @var Raw result resource
	 */
	protected $_result;

	/**
	 * @var Return rows as an object or associative array
	 */
	protected $_as_object;

	/**
	 * @var Parameters for __construct when using object results
	 */
	protected $_object_params = NULL;

	/**
	 * @var integer Total number of the rows
	 */
	protected $_total_rows = 0;

	/**
	 * @var Current row numnber
	 */
	protected $_current_row = 0;

	/**
	 * Free result resource
	 *
	 * @return  void
	 */
	abstract public function __destruct();

	/**
	 * Sets the total number of rows and stores the result locally.
	 *
	 * @param   mixed   $result     query result
	 * @param   string  $sql        SQL query
	 * @param   mixed   $as_object
	 * @param   array   $params
	 * @return  void
	 */
	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL) {
		$this->_result = $result;

		$this->_query = $sql;

		if (is_object($as_object)) {
			$as_object = get_class($as_object);
		}
		$this->_as_object = $as_object;

		if ($params) {
			$this->_object_params = $params;
		}
	}

	/**
	 * Return the named column from the current row.
	 *
	 *     // Get the "id" value
	 *     $id = $result->get('id');
	 *
	 * @param   string  $name     column to get
	 * @param   mixed   $default  default value if the column does not exist
	 * @return  mixed
	 */
	public function get($name, $default = NULL) {
		$row = $this->current();
		if ($this->_as_object) {
			if (isset($row->$name)) {
				return $row->$name;
			}
		} else {
			if (isset($row[$name])) {
				return $row[$name];
			}
		}

		return $default;
	}

	/**
	 * Return all of the rows in the result as an array.
	 *
	 *     // Indexed array of all rows
	 *     $rows = $result->as_array();
	 *
	 *     // Associative array of rows by "id"
	 *     $rows = $result->as_array('id');
	 *
	 *     // Associative array of rows, "id" => "name"
	 *     $rows = $result->as_array('id', 'name');
	 *
	 * @param   string  $key    column for associative keys
	 * @param   string  $value  column for values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL) {
		$results = array();

		if ($key === NULL AND $value === NULL) {
			foreach ($this as $row) {
				$results[] = $row;
			}
		} elseif ($key === NULL) {
			if ($this->_as_object) {
				foreach ($this as $row) {
					$results[] = $row->$value;
				}
			} else {
				foreach ($this as $row) {
					$results[] = $row[$value];
				}
			}
		} elseif ($value === NULL) {
			if ($this->_as_object) {
				foreach ($this as $row) {
					$results[$row->$key] = $row; 
				}
			} else {
				foreach ($this as $row) {
					$results[$row[$key]] = $row;
				}
			}
		} else {
			if ($this->_as_object) {
				foreach ($this as $row) {
					$results[$row->$key] = $row->$value;
				}
			} else {
				foreach ($this as $row) {
					$result[$row[$key]] = $row[$value];
				}
			}
		}

		$this->rewind();

		return $results;
	}

	/**
	 * Implements Countable::count,returns the total number of the rows
	 * 
	 *     echo count($result)
	 *     
	 * @return integer
	 */
	public function count() {
		return $this->_total_rows;
	}

	/**
	 * Implements ArrayAccess::offsetExists
	 *
	 *    if (isset($result[10])){
	 *        // Row 10 exists
	 *    }
	 * 
	 * @param int $offset 
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return ($offset >= 0 AND $offset < $this->_total_rows);
	}

	/**
	 * Implements ArrayAccess::offsetGet
	 * @param  int $offset 
	 * @return mixed
	 */
	public function offsetGet($offset) {
		if ( ! $this->seek($offset)) {
			return NULL;
		}
		return $this->current();
	}

	/**
	 * Implements ArrayAccess::offsetSet
	 * @param  int   $offset
	 * @param  mixed $value
	 * @return
	 * @throws MysqlException
	 */
	public function offsetSet($offset, $value) {
		throw new MysqlException('Database results are read-only', MysqlException::MYSQL_RESULT_READ_ONLY);
	}

	/**
	 * Implements ArrayAccess::offsetUnset
	 * @param  int   $offset
	 * @return
	 * @throws MysqlException
	 */
	public function offsetUnset($offset) {
		throw new MysqlException('Database results are read-only', MysqlException::MYSQL_RESULT_READ_ONLY);
	}

	/**
	 * Implements Iterator::key
	 *
	 *    echo key($result)
	 * 
	 * @return integer
	 */
	public function key() {
		return $this->_current_row;
	}

	/**
	 * Implements Iterator::next
	 * 
	 *    next($result)
	 * 
	 * @return $this
	 */
	public function next() {
		++$this->_current_row;
		return $this;
	}

	/**
	 * Implements Iterator::prev
	 *
	 *    prev($result)
	 * 
	 * @return $this
	 */
	public function prev() {
		--$this->_current_row;
		return $this;
	}

	/**
	 * Implements Iterator::rewind
	 *
	 *   rewind($result)
	 * 
	 * @return [type] [description]
	 */
	public function rewind() {
		$this->_current_row = 0;
		return $this;
	}

	/**
	 * Implements Iterator::valid
	 *
	 *    [!!] This method is only used internally.
	 * 
	 * @return boolean
	 */
	public function valid() {
		return $this->offsetExists($this->_current_row);
	}

}