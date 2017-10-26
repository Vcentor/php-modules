<?php
/**
 * MYSQLI DRIVER
 *
 * @package 	Dao\Mysql\Driver
 * @category 	Driver
 * @author 		vcentor
 */

namespace Dao\Mysql\Driver;
use mysqli as MySQLiDriver;
use Dao\Mysql\Database;
use Dao\Mysql\Result\Mysqli\MySQLiResult;
use Dao\Mysql\Exception\MysqlException;

class MySQLi extends Database {

	// Database in use by each connection
	protected static $_current_database = array();

	// Use SET NAMES to set character set
	protected static $_set_names;

	// Identifier for this connection within the PHP driver
	protected $_connection_id;

	// MySQL uses a backtick for identifiers
	protected $_identifier = '`';

	public function connect() {

		if ($this->_connection) {
			return;
		}

		extract($this->_config['connection'] + array(
			'hostname' => '',
			'username' => '',
			'password' => '',
			'database' => '',
			'port'     => 3306,
			'socket'   => '',
		));

		unset($this->_config['connection']['username'], $this->_config['connection']['password']);

		try {
			$this->_connection = new MySQLiDriver($hostname, $username, $password, $database, $port, $socket);
		} catch (Exception $e) {
			throw new MysqlException($e->getMessage(), $e->getCode());
		}

		$this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);

		if ( ! empty($this->_set_names)) {
			$this->set_charset($this->_config['charset']);
		}

	}

	public function set_charset($charset) {
		$this->_connection OR $this->connect();

		if (self::$_set_names === NULL) {
			self::$_set_names = ! function_exists('mysqli_set_charset');
		}

		if (self::$_set_names === TRUE) {
			$status = $this->_connection->query('SET NAMES '.$this->quote($charset));
		} else {
			$status = $this->_connection->set_charset($charset);
		}

		if ($status === FALSE) {
			throw new MysqlException($this->_connection->error, $this->_connection->errno);
		}
	}

	public function disconnect() {
		try {
			$status = TRUE;
			if ($this->_connection) {
				if ($status = $this->_connection->close()) {
					$this->_connection = NULL;
					parent::disconnect();
				}
			}
		} catch (Exception $e) {
			$status = ! is_resource($this->_connection);
		}

		return $status;
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL) {
		$this->_connection OR $this->connect();

		if (($result = $this->_connection->query($sql)) === FALSE) {
			throw new MysqlException($this->_connection->error, $this->_connection->errno);
		}

		$last_query = $sql;

		if ($type === Database::SELECT) {
			// Return an iterator of results
			return new MySQLiResult($result, $sql, $as_object, $params);
		} elseif ($type === Database::INSERT) {
			return array(
				'insert_id' 	=> $this->_connection->insert_id,
				'affected_rows' => $this->_connection->affected_rows,
			);
		} else {
			return $this->_connection->affected_rows;
		}
	}

	public function begin($mode = NULL) {
		$this->_connection OR $this->connect();

		if ($mode AND ! $this->_connection->query('SET TRANSACTION ISOLATION LEVEL '.$mode)) {
			throw new MysqlException($this->_connection->error, $this->_connection->errno);
		}

		return (bool) $this->_connection->query('START TRANSACTION');
	}

	public function commit() {
		$this->_connection OR $this->connect();

		return (bool) $this->_connection->query('COMMIT');
	}

	public function rollback() {
		$this->_connection OR $this->connect();

		return (bool) $this->_connection->query('ROLLBACK');
	}

	public function list_tables($like = NULL) {
		if (is_string($like)) {
			$result = $this->query(Database::SELECT, 'SHOW TABLES LIKE '.$this->quote($like));
		} else {
			$result = $this->query(Database::SELECT, 'SHOW TABLES');
		}

		$tables = array();

		foreach ($result as $row) {
			$tables[] = reset($row);
		}

		return $tables;
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE) {
		$table = ($add_prefix === TRUE) ? $this->quote_table($table) : NULL;

		if (is_string($like)) {
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table.' LIKE '.$this->quote($like));
		} else {
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table);
		}

		return $result;
	}

	public function escape($value) {
		$this->_connection OR $this->connect();

		if (($value = $this->_connection->real_escape_string((string) $value)) === FALSE) {
			throw new MysqlException($this->_connection->error, $this->_connection->errno);
		}

		return "'$value'";
	}
}