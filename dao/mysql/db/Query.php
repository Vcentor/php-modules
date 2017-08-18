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
	protected $_parameters = array();
	
	/**
	 * Create a new SQL query of the specified type.
	 *
	 * @param 	integer 	$type 	query type Database::SELECT, Database::INSERT, etc
	 * @param 	string 		$sql 	query string
	 * @return 	void
	 */
	public function __construct($type, $sql) {
		$this->_type = $type;
		$this->_sql = $sql;
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

	/**
	 * Set the value of a parameter in the query.
	 *
	 * @param 	string 	$param 	parameter key to replace
	 * @param 	mixed 	$value	value to use
	 * @param 	$this
	 */
	public function param($param, $value) {
		$this->_parameters[$param] = $value;
		return $this;
	}

	/**
	 * Bind a variable to a parameters in the query.
	 *
	 * @param 	string 	$param 	parameter key to replace
	 * @param 	mixed 	$var 	variable to use
	 * @return 	$this
	 */
	public function bind($param, & $var) {
		$this->_parameters[$param] = $var;
		return $this;
	}

	/**
	 * Add multiple parameters to the query.
	 *
	 * @param 	array 	$params 	list of parameters
	 * @return 	$this
	 */
	public function parameters(array $params) {
		$this->_parameters = $params + $this->_parameters;
		return $this;
	}

	/**
	 * Execute the current query on the given database.
	 *
	 * @param 	boolean 	$as_one 		select result ONE or ALL default ALL
	 * @param 	mixed 		$db 			Database instance or name of instance
	 * @return 	array 		select result
	 * @return 	mixed 		the insert id for INSERT queries
	 * @return 	integer  	number of affected rows for all other queries
	 */
	public function execute($as_one = FALSE, $db = NULL) {

		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		if ($as_object === NULL) {
			$as_object = $this->_as_object;
		}

		$sql = $this->compile($db);

		$result = $db->query($this->_type, $sql, $as_one);

		return $result;
	}
}