<?php
/**
 * Database query builder for INSERT statements.
 * 
 * @package  Dao\Mysql\Db\Query\Builder
 * @category Query
 * @author 	 vcentor
 */

namespace Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Db\Query\Builder;
use Dao\Mysql\Database;
use Dao\Myusql\Db\Query;
use Dao\Myslq\Exception\MysqlException;

class Insert extends Builder {

	// INSERT INTO ...
	protected $_table;

	// columns
	protected $_columns = array();

	// values
	protected $_values = array();

	/**
	 * Set the table and columns for an insert.
	 *
	 * @param 	mixed 	$table  	table name or array($table, $alias) or object
	 * @param 	array 	$columns 	column names
	 * @return 	void
	 */
	public function __construct($table = NULL, array $columns = NULL) {

		if ($table) {
			$this->table($table);
		}

		if ($columns) {
			$this->_columns = $columns;
		}

		return parent::__construct(Database::INSERT, '');
	}

	/**
	 * Sets table to insert into.
	 *
	 * @param 	string 	$table 	table name
	 * @return  $this
	 */
	public function table($table) {
		if ( ! is_string($table)) {
			throw new MysqlException('INSERT INTO syntax does not allow table aliasing', MysqlException::INSERT_INTO_ERR);
		}

		$this->_table = $table;

		return $this;
	}

	/**
	 * Set the columns that will be inserted.
	 *
	 * @param 	array 	$columns 	column names
	 * @param 	$this
	 */
	public function columns(array $columns) {
		$this->_columns = $columns;

		return $this;
	}

	/**
	 * Adds or overwrites values. Multiple value can be added.
	 *
	 * @param  	array 	$values 	values list
	 * @param 	...
	 * @return 	$this
	 */
	public function values(array $values) {

		if ( ! is_array($this->_values)) {
			throw new MysqlException('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES', MysqlException::INSERT_INTO_ERR);
		}

		$values = func_get_args();

		$this->_values = array_merge($this->_values, $values);
		return $this;
	}

	/**
	 * Use a sub-query to for the inserted values.
	 *
	 * @param 	object 	$query 	Dao\Myusql\Db\Query of SELECT type
	 * @return 	$this
	 */
	public function select(Query $query) {
		if ($query->type() !== Database::SELECT) {
			throw new MysqlException('Only SELECT queries can be combined with INSERT queries', MysqlException::INSERT_INTO_ERR);
		}

		$this->_values = $query;

		return $this;
	}

	/**
	 * Compile the SQL query and return it.
	 *
	 * @param 	mixed 	$db 	Database instance or instance name
	 * @return 	string
	 */
	public function compile($db = NULL) {
		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$query = 'INSERT INTO '.$db->quote_table($this->_table);

		if ($this->_columns) {
			$query .= ' ('.implode(', ', array_map(array($db, 'quote_column'), $this->_columns)).')';
		}

		if (is_array($this->_values)) {
			$quote = array($db, 'quote');

			$groups = array();

			foreach ($this->_values as $group) {
				foreach ($group as $offset => $value) {
					if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE) {
						$group[$offset] = $db->quote($value);
					}
				}
				$groups[] = '('.implode(', ', $group).')';
			}

			$query .= ' VALUES '.implode(', ', $groups);
		} else {
			$query .= (string) $this->_values;
		}

		$this->_sql = $query;

		return parent::compile($db);
	}

	public function reset() {
		$this->_table 		=
		$this->_sql			= NULL

		$this->_columns 	=
		$this->_values 		= 
		$this->_parameters 	= array();

		return $this;
	}
}