<?php
/**
 * builder select query 
 *
 * @package  DB
 * @category Query
 * @author   xieshuai
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Exception\MysqlException;
use Dao\Mysql\Db\Query\Builder\Where;
use Dao\Mysql\Db\Query\Builder\Join;
use Dao\Mysql\Database;

class Select extends Where{

	// SELECT ...
	protected $_select = array();

	// DISTINCT
	protected $_distinct = FALSE;

	// FROM ...
	protected $_from = array();

	// JOIN ...
	protected $_join = array();

	// GROUP BY ...
	protected $_group_by = array();

	// HAVING ... 
	protected $_having = array();

	// OFFSET ... 
	protected $_offset = NULL;

	// UNION ...
	protected $_union = array();

	// The last JOIN statement to created
	protected $_last_join;

	/**
	 * Set the initial columns to select from
	 *
	 * @param  	array $columns
	 * @return  void
	 */
	public function __construct($columns = NULL) {
		if ( ! empty($columns)) {
			$this->_select = $columns;
		}

		parent::__construct(Database::SELECT, '');
	}

	/**
	 * SELECT DISTINCT
	 *
	 * @param 	boolean 	$value
	 * @return 	$this
	 */
	public function distinct($value) {
		$this->_distinct = (bool) $value;
		return $this;
	}

	/**
	 * Choose the columns to select from 
	 *
	 * @param 	mixed 	$columns
	 * @return  $this
	 */
	public function select($columns = NULL) {
		$columns = func_get_args();
		$this->_select = array_merge($this->_select, $columns);
		return $this;
	}

	/**
	 * Choose the columns to select from, using an array
	 *
	 * @param 	array 	$columns
	 * @return 	$this
	 */
	public function select_array(array $columns = NULL) {
		$this->_select = array_merge($this->_select, $columns);
		return $this;
	}

	/**
	 * Choose the tables to select "FROM ..."
	 *
	 * @param 	mixed 	$table 	table name or array($table, $alias) or object
	 * @return 	$this
	 */
	public function from($tables) {
		$tables = func_get_args();
		$this->_from = array_merge($this->_from, $tables);
		return $this;
	}

	/**
	 * Adds addition tables to "JOIN ..."
	 *
	 * @param 	mixed 	$table 	table name or array($table, $alias) or object
	 * @param 	string 	$type 	join type (LEFT, RIGHT, INNER, etc)
	 * @return 	$this
	 */
	public function join($table, $type = NULL) {
		$this->_join[] = $this->_last_join = new Join($table, $type);
		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement
	 *
	 * @param 	mixed 	$c1 	column name or array($column, $alias) or object
	 * @param 	string 	$op 	logic operator
	 * @param 	mixed 	$c1 	column name or array($column, $alias) or object
	 * @return 	$this
	 */
	public function on($c1, $op, $c2) {
		$this->_last_join->on($c2, $op, $c2);
		return $this;
	}

	/**
	 * Adds "USING ..." conditions for the last created JOIN statement
	 *
	 * @param 	string 	$column 	column name
	 * @return 	$this
	 */
	public function using($column) {
		$columns = func_get_args();
		call_user_func_array(array($this->_last_join, 'using'), $columns);
		return $this;
	}

	/**
	 * Creates a "GROUP BY ..." filter
	 *
	 * @param 	$columns 	column name or array($column, $alias) or object
	 * @return 	$this
	 */
	public function group_by($columns) {
		$columns = func_get_args();
		$this->_group_by = array_merge($this->_group_by, $columns);
		return $this;
	}

	/**
	 * Alias of and_having()
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string 	$op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return 	$this
	 */
	public function having($column, $op, $value = NULL) {
		return $this->and_having($column, $op, $value);
	}

	/**
	 * Creates a new "AND HAVING" condition for the query
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string 	$op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return 	$this
	 */
	public function and_having($column, $op, $value = NULL) {
		$this->_having[] = array('AND' => array($column, $op, $value));
		return $this;
	}

	/**
	 * Creates a new "OR HAVING" condition for the query
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string 	$op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return 	$this
	 */
	public function or_having($column, $op, $value = NULL) {
		$this->_having[] = array('OR' => array($column, $op, $value));
		return $this;
	}

	/**
	 * Alias of and_having_open()
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function having_open() {
		return $this->and_having_open();
	}

	/**
	 * Opens a new "AND HAVING (...)" grouping
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function and_having_open() {
		$this->_having[] = array('AND' => '(');
	}

	/**
	 * Opens a new "OR HAVING (...)" grouping
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function or_having_open() {
		$this->_having[] = array('OR' => '(');
	}

	/**
	 * Alias of and_having_close()
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function having_clse() {
		return $this->and_having_close();
	}

	/**
	 * Close an open "AND HAVING (...)" grouping
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function and_having_close() {
		$this->_having[] = array('AND' => ')');
	}

	/**
	 * Close an open "OR HAVING (...)" grouping
	 *
	 * @param 	void
	 * @return 	$this
	 */
	public function or_having_close() {
		$this->_having[] = array('OR' => ')');
	}

	/**
	 * Adds an other UNION clause
	 * 
	 * @param 	object 	$select 	an instance of  Dao\Mysql\Query\Builder\Select
	 * @param 	boolean $all 	
	 * @return 	$this
	 */
	public function union(self $select, $all = NULL) {

		if (! $select instanceof self) {
			throw new MysqlException('first parameter must be a string or an instance of self', MysqlException::INVALID_PARAM);
		}

		$this->_union[] = array('select' => $select, 'all' => $all);

		return $this;
	}

	/**
	 * Start returning results after "OFFSET ..."
	 *
	 * @param 	integer $number 	starting result number or NULL to reset
	 * @return 	$this
	 */
	public function offset($number) {
		$this->_offset = ($number === NULL) ? NULL : (int) $number;
		return $this;
	}

	/**
	 * Compile the SQL query and return it
	 *
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @return 	string 
	 */
	public function compile($db = NULL) {

		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		// Callback to quote columns
		$quote_column = array($db, 'quote_column');

		// Callback to quote table
		$quote_table = array($db, 'quote_table');

		// Start a selection query
		$query = 'SELECT ';

		if ($this->distinct === TRUE) {
			$query .= 'DISTINCT ';
		}

		if (empty($this->_select)) {
			$query .= '*';
		} else {
			$query .= implode(', ', array_unique(array_map($quote_column, $this->_select)));
		}

		if ( ! empty($this->_from)) {
			$query .= ' FROM' . implode(', ', array_unique(array_map($quote_table, $this->_from)));
		}

		if ( ! empty($this->_join)) {
			$query .= ' '.$this->_compile_join($db, $this->_join);
		}

		if ( ! empty($this->_where)) {
			$query .= ' WHERE'.$this->_compile_conditions($db, $this->_where);
		}

		if ( ! empty($this->_group_by)) {
			$query .= ' '.$this->_compile_group_by($db, $this->_group_by);
		}

		if ( ! empty($this->_having)) {
			$query .= ' HAVING'.$this->_compile_conditions($db, $this->_having);
		}

		if ( ! empty($this->_order_by)) {
			$query .= ' '.$this->_compile_order_by($db, $this->_order_by);
		}

		if ($this->_limit !== NULL) {
			$query .= ' LIMIT'.$this->_limit;
		}

		if ($this->_offset !== NULL) {
			$query .= ' OFFSET'.$this->_offset;
		}

		if ( ! empty($this->_union)) {
			$query = '('.$query.')';
			foreach ($this->_union as $u) {
				$query .= ' UNION ';
				if ($u['all'] === TRUE) {
					$query .= 'ALL ';
				}
				$query .= '('.$u['select']->compile($db).')';
			}
		}

		$this->_sql = $query;

		return parent::compile($db);
	}

	public function reset() {

		$this->_select 		= 
		$this->_from 		=
		$this->_join 		= 
		$this->_where		= 
		$this->_group_by  	= 
		$this->_having 		=
		$this->_order_by	=
		$this->_union 		= 
		$this->parameters 	= array();

		$this->_limit 		= 
		$this->_offset		=
		$this->_sql			=
		$this->_last_join 	= NULL;

		$this->_distinct	= FALSE;
		
		return $this;
	}
}
