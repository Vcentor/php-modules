<?php
/**
 * Database query builder for UPDATE statements.
 *
 * @package 	Dao\Mysql\Db\Query\Builder
 * @category 	Query
 * @author 		vcentor
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Db\Query\Builder\Where;
use Dao\Mysql\Database;

class Update extends Where {

	// UPDATE ...
	protected $_table;

	// SET ... 
	protected $_set = array();

	/**
	 * Set the table for a update.
	 * 
	 * @param 	mixed 	table name or array($table, $alias) or object
	 * @return 	void
	 */
	public function __construct($table = NULL) {
		if ($table) {
			$this->_table = $table;
		}

		parent::__construct(Database::UPDATE, '');
	}

	/**
	 * Sets the table to update.
	 *
	 * @param 	mixed 	table name or array($table, $alias) or object
	 * @return 	$this
	 */
	public function table($table) {
		$this->_table = $table;
		return $this;
	}

	/**
	 * Set the values to update with an associative array.
	 *
	 * @param 	array $pairs 	associative (column => value) list
	 * @return 	$this
	 */
	public function set(array $pairs) {
		foreach ($pairs as $column => $value) {
			$this->_set[] = array($column, $value);
		}

		return $this;
	}

	/**
	 * Set the value of a single column.
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	mixed 	$value 		column value
	 * @return  $this
	 */
	public function value($column, $value) {
		$this->_set[] = array($column, $value);

		return $this;
	}

	/**
	 * Compile the SQL query and return it.
	 * 
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @param 	string
	 */
	public function compile($db = NULL) {
		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$query = 'UPDATE '.$db->quote_table($this->_table);

		if ( ! empty($this->_set)) {
			$query .= ' SET '.$this->_compile_set($db, $this->_set);
		}

		if ( ! empty($this->_where)) {
			$query .= ' WHERE '.$this->_compile_conditions($db, $this->_where);
		}

		if ( ! empty($this->_order_by)) {
			$query .= ' '.$this->_compile_order_by($db, $this->_order_by);
		}

		if ($this->_limit !== NULL) {
			$query .= ' LIMIT '.$this->_limit;
		}

		$this->_sql = $query;

		return parent::compile($db);
	}

	public function reset() {
		$this->_table 		= NULL;

		$this->_set 		= 
		$this->_where 		= array();

		$this->_limit 		= NULL;

		$this->_parameters 	= array();

		$this->_sql 		= NULL;

		return $this;
	}
}
