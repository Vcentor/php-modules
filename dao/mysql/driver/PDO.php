<?php
/**
 * PDO DRIVER
 *
 * @package 	Dao\Mysql\Driver
 * @category 	Driver
 * @author 		vcentor
 */

namespace Dao\Mysql\Driver;
use PDO as PDODriver;
use Dao\Mysql\Database;
use Dao\Exception\MysqlException;

class PDO extends Database {

	// PDO use no quote for identifier
	protected $_identifier = '';

	public function connect() {
		if ($this->_connection) {
			return;
		}

		extract($this->_config['connection'] + array(
			'dsn'			=> 	'',
			'username'		=> 	NULL,
			'password'		=> 	NULL,
			'persistent'	=> 	FALSE,
		));

		unset($this->_config['connection']);

		// Force PDO to use exceptions for all errors
		$options[PDODriver::ATTR_ERRMODE] = PDODriver::ERRMODE_EXCEPTION;

		if ( ! empty($persistent)) {
			// Make the connection persistent
			$options[PDODriver::ATTR_PERSISTENT] = TRUE;
		}

		try {
			$this->_connection = new PDODriver($dsn, $username, $password, $options);
		} catch (PDOException $e) {
			throw new MysqlException($e->getMessage(), $e->getCode());
		}

		if ( ! empty($this->_config['charset'])) {
			$this->set_charset($this->_config['charset']);
		}
	}

	public function set_charset($charset) {
		$this->_connection OR $this->connect();

		$this->_connection->exec('SET NAMES '.$this->quote($charset));
	}

	public function disconnect() {
		// Destory the PDO object
		$this->_connection = NULL;
		parent::disconnect();
	}

	public function query($type, $sql, $as_one = FALSE) {
		$this->_connection OR $this->connect();

		try {
			$result = $this->_connection->query($sql);
		} catch (Exception $e) {
			throw new MysqlException($e->getMessage(), $e->getCode());	
		}

		$this->last_query = $sql;

		if ($type === Database::SELECT) {

			$result->setFetchMode(PDODriver::FETCH_ASSOC);

			if ($as_one) {
				return $result->fetch();
			}
			return $result->fetchAll();

		} elseif ($type === Database::INSERT) {
			return array(
				'insert_id' 	=> $this->_connection->lastInsertId(),
				'affected_rows'	=> $result->rowCount(),
			);
		} else {
			return $result->rowCount();
		}
	}


	public function begin($mode = NULL) {
		$this->_connection OR $this->connect();

		$this->_connection->beginTransaction();
	}

	public function commit() {
		$this->_connection OR $this->connect();

		$this->_connection->commit();
	}

	public function rollback() {
		$this->_connection OR $this->connect();

		$this->_connection->rollback();
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

		return $this->_connection->quote($value);
	}
}