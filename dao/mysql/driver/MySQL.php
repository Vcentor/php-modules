<?php
/**
 * MySQL Driver 
 * 注:此驱动自php5.5.0已经废弃,在php7.0.0开始被移除
 * @package  Dao\Mysql\Driver
 * @category Driver
 * @author   vcentor
 */
namespace Dao\Mysql\Driver;
use Dao\Mysql\Database;
use Dao\Mysql\Exception\MysqlException;

class MySQL extends Database {

	// Database in use by each connection
	protected static $_current_database = array();

	// Use SET NAMES to set character set
	protected static $_set_names;

	// Identifier for this connectino
	protected $_connection_id;

	// MySQL uses a backtick for identifiers
	protected $_identifier = '`';

	public function connect() {
		if ($this->_connection) {
			return ;
		}

		extract($this->_config['connection'] + array(
			'hostname' 		=> '',
			'username' 		=> '',
			'password' 		=> '',
			'database' 		=> '',
			'persistent'	=> FALSE,
		));

		// Prevent this information from showing up in trances
		unset($this->_config['connection']['username'], $this->_config['connection']['password']);

		// connection mysql
		try {
			if ($persistent) {
				$this->_connection = mysql_pconnect($hostname, $username, $password);
			} else {
				$this->_connection = mysql_connect($hostname, $username, $password, TRUE);
			}
		} catch (Exception $e) {

			// No connection exists
			$this->_connection = NULL;

			throw new MysqlException($e->getMessage(), $e->getCode());
		}

		// select database

		// \xFF(\) is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);
		$this->_select_db($database);

		// set character
		if ( ! empty($this->_config['charset'])) {
			$this->set_charset($this->_config['charset']);
		}
	}

	public function query($type, $sql, $as_object = FALSE) {
		// Make sure the database is connected
		$this->_connection OR $this->connect();


		// Set the last query
		$this->last_query = $sql;

		if ( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== self::$_current_database[$this->_connection_id]) {
			$this->_select_db($this->_config['connection']['database']);
		}

		// Execute the query
		if (($result = mysql_query($sql, $this->_connection)) === FALSE) {
			throw new Exception(mysql_error($this->_connection), mysql_errno($this->_connection));
		}

		if ($type === Database::SELECT) {
			$ret = array();
			if ($as_object === TRUE) {
				while ($rows = mysql_fetch_object($result)) {
					$ret[] = $rows
				}
			} elseif (is_string($as_object)) {
				while ($rows = mysql_fetch_object($result, $as_object)) {
					$ret[] = $rows
				}
			} else {
				while ( $rows = mysql_fetch_assoc($result)) {
					$rows[] = $rows;
				}
			}

			return $ret;
		} elseif ($type === Database::INSERT) {
			// Return an list of insert id and rows created
			return array(
				'insert_id'     => mysql_insert_id($this->_connection),
				'affected_rows' => mysql_affected_rows($this->_connection),
			);
		} else {
			// Return the number of rows affected
			return mysql_affected_rows($this->_connection);
		}
	}


	public function begin($model = NULL) {
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if ($model AND ! mysql_query("SET TRANSACTION ISOLATION LEVEL $model", $this->_connection)) {
			throw new MysqlException(mysql_error($this->_conection), mysql_errno($this->_connection));
		}

		return (bool) mysql_query('START TRANSACTION', $this->_connection);
	}

	public function commit() {
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		return (bool) mysql_query('COMMIT', $this->_connection);
	}

	public function rollback() {
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		return (bool) mysql_query('ROLLBACK', $this->_connection);
	}

	public function list_tables($like = NULL) {
		if (is_string($like)) {
			$result = $this->query(Database::SELECT, 'SHOW TABLES LIKE '.$this->quote($like), FALSE);
		} else {
			$result = $this->query(Database::SELECT, 'SHOW TABLES', FALSE);
		}

		$tables = array();
		foreach ($result as $row) {
			$tables[] = reset($row);
		}
		return $tables;
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE) {
		// Quote the table name
		$table = ($add_prefix === TRUE) ? $this->quote_table($table) : $table;

		if (is_string($like)) {
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table.'LIKE '.$this->quote($like), FALSE);
		} else {
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table, FALSE);
		}

		return $result;
	}

	public function escape($value) {
		// Make sure the database is connected
		$this->_conection OR $this->connect();
 
		if (($value = mysql_real_escape_string($value)) === FALSE) {
			throw new MysqlException(mysql_error($this->_connection), mysql_errno($this->_connection));
		}

		// SQL standard is to use single-quotes for all values
		return "'$value'";
	}

	/**
	 * Select the database
	 *
	 * @param  string $database database
	 * @return void
	 */
	protected function _select_db($database) {
		if ( ! mysql_select_db($database, $this->_connection)) {
			throw new MysqlException(mysql_error($this->_connection), mysql_errno($this->_connection));
		}

		self::$_current_database[$this->_connection_id] = $database;
	}

	public function set_charset($charset) {
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if (self::$_set_names === NULL) {
			self::$_set_names = ! function_exists('mysql_set_charset');
		}

		if (self::$_set_names === TRUE) {
			$status = (bool) mysql_query('SET NAMES '.$this->quote($charset), $this->_connection);
		} else {
			// PHP is compiled against MySQL5.x
			$status = mysql_set_charset($charset, $this->_connection);
		}

		if ($status === FALSE) {
			throw new MysqlException(mysql_error($this->_connection), mysql_errno($this->_connection));
		}
	}

	public function disconnect() {
		try {
			$status = TRUE;
			if (is_resource($this->_connection)) {
				if ($status = mysql_close($this->_connection)) {
					$this->_connection = NULL;
					parent::disconnect();
				}
			}
		} catch (Exception $e) {
			$status = ! is_resource($this->_connection)
		}

		return $status;
	}
}