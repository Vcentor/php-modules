<?php
/**
 * Database query wrapper.
 *
 * @package  Dao\Mysql\Db
 * @category Query
 * @author 	 vcentor
 */
namespace Dao\Mysql\Db;
use Dao\Mysql\Database;
use Dao\Mysql\Exception\MysqlException;

class Query {

	// Query type
	protected $_type;

	// SQL statement
	protected $_sql;

	// Quoted query parameters
	protected $_parameters;

	// Return results as associative arrays or objects
	protected $_as_object = FALSE;

	// Parameters for __construct when using object results
	protected $_object_params = array();

	/**
	 * Create a new SQL query of the specified type.
	 *
	 * @param 	integer 	$type 	query type Database::SELECT, Database::INSERT, etc
	 * @param 	string 		$sql 	query string
	 * @return 	void
	 */
	public function __construct($type, $sql) {
		$this->_type = $type;
		$this->_sql = $sql
	}

	/**
	 * Return the SQL query string. 
	 *
	 * @return string 
	 */
	public function __toString() {
		try {
			return $this->compile(Database::instance());
		} catch (Exception $e) {
			throw new MysqlException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Get the type of the query
	 *
	 * @return integer $type
	 */
	public function type() {
		return $this->_type;
	}

	/**
	 * Compile the SQL query and return it. Replaces any parameters with their given values.
	 *
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @return 	string 
	 */
	public function compile($db = NULL) {

		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$sql = $this->_sql;

		if ( ! empty($this->_parameters)) {
			$values = array_map(array($db, 'quote'), $this->_parameters);

			$sql = strtr($sql, $values);
		}

		return $sql;
	}
}