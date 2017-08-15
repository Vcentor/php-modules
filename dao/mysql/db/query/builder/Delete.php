<?php
/**
 * Database query builder for DELETE statements.
 *
 * @package 	Dao\Mysql\Db\Query\Builder
 * @category 	Query
 * @author 		vcentor
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Database;
use Dao\Mysql\Db\Query\Builder\Where;

class Delete extends Where {

	// DELETE FROM ...
	protected $_table;

	/**
	 * Set the table for a delete.
	 *
	 * @param 	mixed 	$table 	table name or array($table, $alias) or object
	 * @return 	void
	 */
	public function __construct($table = NULL) {
		if ($table) {
			$this->_table = $table;
		}

		parent::__construct(Database::DELETE, '');
	}

	/**
	 * Sets the table to delete from.
	 *
     * @param 	mixed 	$table 	table name or array($table, $alias) or object
     * @return 	$this
     */
	public function table($table) {
		$this->_table = $table;
		return $this;
	}

	/**
	 * Compile the SQL query and return it.
	 *
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @return 	string
	 */
	public function compile($db = NULL) {
		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$query = 'DELETE FROM '.$db->quote_table($this->_table);

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

		$this->_where		= array();

		$this->_parameters  = array();

		$this->_sql			= NULL;

		return $this;

	}
}
