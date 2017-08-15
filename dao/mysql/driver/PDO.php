<?php
/**
 * PDO DRIVER
 *
 * @package 	Dao\Mysql\Driver
 * @category 	Driver
 * @author 		vcentor
 */

namespace Dao\Mysql\Driver;
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
		))

		unset($this->_config['connection']);

		// Force PDO to use exceptions for all errors
		$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		if ( ! empty($persistent)) {
			// Make the connection persistent
			$options[PDO::ATTR_PERSISTENT] = TRUE;
		}

		try {
			$this->_connection = new PDO($dsn, $username, $password, $options);
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

	public function query($type, $sql, $as_object = FALSE) {
		$this->_connection OR $this->connect()

		try {
			$result = $this->_connection->query($sql);
		} catch (Exception $e) {
			throw new MysqlException($e->getMessage(), $e->getCode());	
		}

		$this->last_query = $sql;

		if ($type === Database::SELECT) {
			if ($as_object === TRUE) {
				$result->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
			} elseif (is_string($as_object)) {
				$result->setFetchMode(PDO::FETCH_CLASS, $as_object);
			} else {
				$result->setFetchMode(PDO::FETCH_ASSOC);
			}

			$result = $result->fetch_all();

			return $result;
		} elseif ($type === Database::INSERT) {
			return array(
				'insert_id' 	=> $this->_connection->lastInsertId();
				'affected_rows'	=> $result->rowCount();
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
		throw new MysqlException('Database method list_tables is not supported by PDO', MysqlException::PDO_ERR);
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE) {
		throw new MysqlException('Database method list_columns is not supported by PDO', MysqlException::PDO_ERR);
	}

	public function escape($value) {
		$this->_connection OR $this->connect();

		$this->_connection->quote($value);
	}
}