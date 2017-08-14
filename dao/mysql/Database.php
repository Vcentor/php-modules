<?php
/**
 * Register a mysql driver 
 * @package Dao\Mysql\Database
 * @category Base
 * @author xieshuai
 */

namespace Dao\Mysql;
use Dao\Mysql\Exception\MysqlException;
use Dao\Mysql\Driver\MySQL as Mysql;
use Dao\Mysql\Driver\MySQLi as Mysqli;
use Dao\Mysql\Driver\PDO as Pdo;
use Dao\Mysql\Db\Query\Expression;
use Dao\Mysql\Db\Query;

abstract class Database {

	// Query types
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;

	/**
	 * @var array Database instances
	 */
	public static $instances = array();

	/**
	 * @var string select different idc
	 */
	public static $name = 'default';

	// string the last query sql
	public $last_query;
	
	// connection resource
	protected $_connection;

	// Instance name
	protected $_instance;

	// quote identifiers
	protected $_identifier = '"';

	// config
	protected $_config;

	/**
	 * @param void
	 * @return void
	 */
	public function __consruct($name, array $config) {
		$this->_instance = $name;
		$this->_config = $config;

		if (empty($this->_config['table_prefix'])) {
			$this->_config['table_prefix'] = '';
		}
	}

	/**
	 * Get a singleton Database instance.
	 *
	 * @param 	string $name    instance name
	 * @param 	array  $config  configuration parameters  
	 * @return  Database
	 */
	public static function instance($name = NULL, array $config) {
		if ($name === NULL) {
			$name = self::$name;
		}
		if (!isset(self::$instances[$name])) {
			if (empty($config)) {
				throw new MysqlException("The config cannot be empty!", MysqlException::INVALID_PARAM);
			}
			$config = $config[$name];

			if (empty($config['type'])) {
				throw new MysqlException("Database type not defined in $name configuration!", MysqlException::INVALID_PARAM);
			}

			// Set the driver class name
			$driver = ucfirst($config['type']);

			// Create the database connection instance
			$driver = new $driver($name, $config);

			// Store the database instance
			self::$instances[$name] = $driver;
		}
		return self::$instances[$name];
	}

	/**
	 * Disconnect from the database when the object is destoryed.
	 * 
	 * @param void
	 * @param void
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * Returns the database instance name
	 *
	 *    echo (string)$db
	 *
	 *	@return string
	 */
	public function __toString() {
		return $this->_instance;
	}

	/**
	 * Disconnect from databases.
	 *
	 * @param void
	 * @return boolean
	 */
	public function disconnect() {
		unset(Database::$instances[$this->_instance]);

		return TRUE;
	}

	/**
	 * Return the table prefix defined in the current configuration.
	 *
	 * @param 	void
	 * @return 	void
	 */
	public function table_prefix() {
		return $this->_config['table_prefix'];
	}


	/**
	 * Quote a value for a SQL query.
	 *
	 * @param  mixed 	$value 	any value to quote
	 * @return string
	 */
	public function quote($value) {

		if ($value === NULL) {
			return 'NULL';
		} elseif ($value === TRUE) {
			return "'1'";
		} elseif ($value === FALSE) {
			return "'0'";
		} elseif (is_object($value)) {
			if ($value instanceof Query) {
				// create a sub-query
				return '('.$value->compile($this).')';
			} elseif ($value instanceof Expression) {
				return $value->compile($this);
			} else {
				return $this->quote( (string) $value);
			}
		} elseif (is_array($value)) {
			return '('.implode(',', array_map(array($this, __FUNCTION__), $value)).')';
		} elseif (is_int($value)) {
			return (int) $value;
		} elseif (is_float($value)) {
			return sprintf('%F', $value);
		}

		$this->escape($value);
	}

	/**
	 * Quote a database column name and add the table prefix if needed.
	 *
	 *     $db->quote_column($column);
	 * 
	 *     $db->quote_column(DB::expr('COUNT(`column`)'));
	 * 
	 * @param 	mixed 	$column
	 * @return 	string
	 */
	public function quote_column($column) {

		$escape_identifier = $this->_identifier.$this->_identifier;

		if (is_array($column)) {
			list($column, $alias) = $column;
			$alias = str_replace($this->_identifier, $escape_identifier, $alias);
		}

		if ($column instanceof Query) {
			// create a sub-query
			$column = '('.$column->compile($this).')';
		} elseif ($column instanceof Expression) {
			$column = $column->compile($this);
		} else {
			// Convert to a string
			$column = (string) $column;

			$column = str_replace($this->_identifier, $escape_identifier, $column);

			if ($column === '*') {
				return $column;
			} elseif (strpos($column, '.') !== FALSE) {
				$parts = explode('.', $column);

				if ($prefix = $this->table_prefix()) {
					// Get the offset of the table name
					$offset = count($parts) - 2;
					// Add the table prefix to the table name
					$parts[$offset] = $prefix.$parts[$offset];
				}

				foreach ($parts as & $part) {
					if ($part !== '*') {
						$part = $this->_identifier.$part.$this->_identifier;
					}
				}

				$column = implode('.', $parts);
			} else {
				$column = $this->_identifier.$column.$this->_identifier;
			}
		}

		if (isset($alias)) {
			$column .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $column;
	}

	/**
	 * Quote a database table name and adds the table prefix if needed
	 *
	 *    $db->quote_table($table)
	 *
	 * @param 	mixed 	$table 	table name or array(table, alias)
	 * @return 	string 
	 */
	public function quote_table($table) {
		// Identifiers are escaped by repeating them
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($table)) {
			list($table, $alias) = $table;
			$alias = str_replace($this->_identifier, $escape_identifier, $alias);
		}

		if ($table instanceof Query) {
			$table = '('.$table->compile($this).')';
		} elseif ($table instanceof Expression) {
			$table = $table->compile($this);
		} else {
			$table = (string) $table;

			$table = str_replace($this->_identifier, $escaped_identifier, $table);

			if (strpos($table, '.') !== FALSE) {
				$parts = explode('.', $table);

				if ($prefix = $this->table_prefix()) {
					$offset = count($parts) - 1;
					$parts[$offset] = $prefix.$parts[$offset];
				}
				foreach ($parts as & $part) {
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$table = implode('.', $parts);
			} else {
				$table = $this->_identifier.$this->table_prefix().$table.$this->_identifier;
			}
		}

		if (isset($alias)) {
			$table .= ' AS '.$this->_identifier.$this->table_prefix().$alias.$this->_identifier;
		}

		return $table;
	}

	/**
	 * Quote a database identifier
	 *
	 * @param 	mixed 	$value 	any identifier
	 * @return  string
	 */
	public function quote_identifier($value) {
		$escape_identifier = $this->_identifier.$this->_identifier;

		if (is_array($value)) {
			list($vaue, $alias) = $value;
			$alias = str_replace($this->_identifier, $escape_identifier, $alias);
		}

		if ($value instanceof Query) {
			$value = '('.$value->compile($this).')';
		} elseif ($value instanceof Expression) {
			$value = $value->compile($this);
		} else {
			$value = (string) $value;

			$value = str_replace($this->_identifier, $escape_identifier, $value);

			if (strops($value, '.') !== FALSE) {
				$parts = explode(',', $value);

				foreach ($parts as & $part) {
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$value = implode('.', $parts);
			} else {
				$value = $this->_identifier.$value.$this->_identifier;
			}
		}

		if (isset($alias)) {
			$value .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $value;
	}

	/**
	 * Connect to the database.
	 * 
	 *     $db->connect();
	 * 
	 * @throws MysqlException
	 * @return void
	 */
	abstract public function connect();

	/**
	 * Set the connection character set.
	 *
	 *    $db->set_charset('utf-8');
	 *
	 * @param string $charset character set name
	 * @return void
	 */
	abstract public function set_charset($charset);

	/**
	 * Perform an SQL query of the given type.
	 * 
	 *    // Make a SELECT query and use objects for results.
	 *    $db->query(Database::SELECT, 'SELECT * FROM groups', TRUE);
	 *   
	 *    // Make a SELECT query and use "Model_User" for the results.
	 *    $db->query(Database::SELECT, 'SELECT * from users limit 1', 'Model_User');
	 *
	 * @param 	integer $type 			Database::SELECT, Database::INSERT, etc
	 * @param 	string  $sql  			SQL query
	 * @param 	mix     $_as_object  	result object class string, TRUE for stdClass, FALSE for assoc array
	 * @param 	array   $params 		object construct parameters for result class
	 * @return 	object  Database_Result for SELECT queries
	 * @return  array   list (insert id, row count) for INSERT queries
	 * @return  integer number of affected rows for all other queries
	 */
	abstract public function query($type, $sql, $_as_object = FALSE, array $params);


	/**
	 * Start a SQL transaction. 
	 *    // start the transaction
	 *    $db->begin()
	 *    try {
	 *        DB::insert('users')->values($user1)...
	 *        DB::insert('users')->values($user2)...
	 *        $db->commit()
	 *    } catche (MysqlException $e) {
     *	      // Insert failed.Rolling back changes...
     *        $db->rollback()
	 *    }
	 * @param 	string $mode transaction mode  READ COMMITTED |  READ UNCOMMITTED | REPEATABLE READ | SERIALIZABLE
	 * @return 	boolean
	 */
	abstract public function begin($model = NULL);

	/**
	 * Commit the current transaction
	 * 
	 * @param void
	 * @param boolean
	 */
	abstract public function commit();

	/**
	 * Abort the current transation
	 *
	 * @param void
	 * @param boolean
	 */
	abstract public function rollback();

	/**
	 * List all of the tables in the database.
	 *    // Get all tables in the current database.
	 *    $db->list_tables();
	 *    
	 *    // Get all user-related tables
	 *    $db->list_tables('user%');
	 * @param 	string 	$like 	table to search for 
	 * @param 	array
	 */
	abstract public function list_tables($like = NULL);

	/**
	 * List all of the columns in a table.
	 * 
	 *    // Get all columns from the "users" table
	 *    $db->list_columns('users');
	 *    
	 *    // Get all name-related columns
	 *    $db->list_columns('users', '%name%');
	 *    
	 *    // Get the columns from a table that doesn't use the table prefix
	 *    $db->list_columns('users', NULL, FALSE);
	 * @param 	string 	$table 		table to get columns from
	 * @param 	string 	$like 		column to search for
	 * @param 	boolean $add_prefix whether to add the table prefix
	 */
	abstract public function list_columns($table, $like = NULL, $add_prefix = TRUE);

    /**
     * Sanitize a string by escaping characters that could cause an SQL injection attack.
     *    
     *    $db->escape('any string');
     * 
     * @param 	string 	$value value to quote
     * @return string
     */
    abstract public function escape($value);

}