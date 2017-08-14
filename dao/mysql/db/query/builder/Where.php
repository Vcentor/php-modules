<?php
/**
 * Database query builder for WHERE statement
 *
 * @package  Dao\Mysql\Db\Query\Builder
 * @category Query
 * @author 	 vcentor
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Db\Query\Builder;

abstract class Where extends Builder {

	// WHERE ...
	protected $_where = array();

	// ORDER BY ...
	protected $_order_by = array();

	// LIMIT ...

	protected $_limit = NULL;

	/**
	 * Alias of and_where()
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string  $op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return  $this
	 */
	public function where($column, $op, $value) {
		return $this->and_where($column, $op, $value);
	}

	/**
	 * Create a new "AND WHERE" condition for the query
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string  $op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return  $this
	 */
	public function and_where($column, $op, $value) {
		$this->_where[] = array('AND' => array($column, $op, $value));
		return $this;
	}

	/**
	 * Create a new "OR WHERE" condition for the query
	 *
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string  $op 		logic operator
	 * @param 	mixed 	$value 		column value
	 * @return  $this
	 */
	public function or_where($column, $op, $value) {
		$this->_where[] = array('OR' => array($column, $op, $value));
		return $this;
	}

	/**
	 * Create a database query with order by
	 * 
	 * @param 	mixed 	$column 	column name or array($column, $alias) or object
	 * @param 	string 	$direction 	direction of sort
	 * @return 	$this
	 */
	public function order_by($column, $direction = NULL) {
		$this->_order_by[] = array($column, $direction);
		return $this;
	}

	/**
	 * Create a database query with limit
	 *
	 * @param integer 	$number maximum results to return or NULL to reset
	 * @return $this
	 */
	public function limit($number) {
		$this->_limit = ($number === NULL) ? NULL : (int) $number;
		return $this;
	}

}


