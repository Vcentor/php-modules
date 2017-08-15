<?php
/**
 * Database query builder for JOIN statement.
 *
 * @package 	Dao\Mysql\Db\Query\Builder
 * @category 	Query
 * @author 		vcentor
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Database\;
use Dao\Mysql\Exception\MysqlException;

class Join extends Builder {

	// Join type
	protected $_type;

	// JOIN ...
	protected $_table;

	// ON ...
	protected $_on = array();

	// USING ...
	protected $_using = array();

	/**
	 * Creates a new JOIN statement for a table.
	 *
	 * @param 	mixed 	$table 	table name or ($table, $alias) or object
	 * @param 	string 	$type 	type of JOIN: LEFT, RIGHT, INNER, etc
	 */
	public function __construct($table, $type = NULL) {
		$this->_table = $table;

		if ($type !== NULL) {
			$this->_type = $type;
		}
	}

	/**
	 * Adds a new condition for joining.
	 *
	 * @param 	mixed 	$c1 	column name or array($column, $alias) or object
	 * @param  	string 	$op 	logic operator
	 * @param  	mixed 	$c2 	column name or array($column, $alias) or object
	 * @return 	$this
	 */
	public function on($c1, $op, $c2) {

		if ( ! empty($this->_using)) {
			throw new MysqlException('JOIN ... ON ... cannot be combined with JOIN ... USING ...', MysqlException::JOIN_ERR);
		}

		$this->_on[] = array($c1, $op, $c2);

		return $this;
	}

	/**
	 * Adds a new condition for joining.
	 *
	 * @param 	string 	$columns 	column name
	 * @return 	$this
	 */
	public function using($columns) {

		if ( ! empty($this->_on)) {
			throw new MysqlException('JOIN ... ON ... cannot be combined with JOIN ... USING ...', MysqlException::JOIN_ERR);
		}

		$columns = func_get_args();
		$this->_using = array_merge($this->_using, $columns);

		return $this; 
	}

	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @return 	string
	 */
	public function compile($db) {

		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$sql = '';
		if ($this->_type) {
			$sql .= strtoupper($this->_type).' JOIN';
		} else {
			$sql .= 'JOIN';
		}

		$sql .= ' '.$db->quote_table($this->_table);

		if ( ! empty($this->_using)) {
			$sql .= ' USING ('.implode(', ', array_map(array($db, 'quote_column'), $this->_using)).')';
		} else {
			$conditions = array();
			foreach ($this->_on as $condition) {
				list($c2, $op, $c2) = $condition;

				if ($op) {
					$op = ' '.strtoupper($op);
				}

				$conditions[] = $db->quote_column($c1).$op.' '.$db->quote_column($c2);
			}

			$sql .= ' ON ('.implode(' AND ', $conditions).')';
		}

		return $sql;
	}

	public function reset() {
		$this->_type  = 
		$this->_table = NULL;

		$this->_on 	  = 
		$this->_using = array();
	}
}