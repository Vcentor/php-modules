<?php
/**
 * Database query builder
 *
 * @package  Dao\Mysql\Query
 * @category Query
 * @author 	 vcentor
 */

namespace Dao\Mysql\Db\Query;
use Dao\Mysql\Database;
use Dao\Mysql\Db\Query;

abstract class Builder extends Query {

	/**
	 * Compiles an array of JOIN statements into an SQL partial
	 *
	 * @param 	object 	$db 	Database instance
	 * @param 	array 	$joins	join statement
	 * @return 	string
	 */
	protected function _compile_join(Database $db, array $joins) {

		$statement = array();

		foreach ($joins as $join) {
			$statements[] = $join->compile($db);
		}

		return implode(' ', $statements);
	}

	/**
	 * Compiles an array of conditions into an SQL partial. Used for WHERE and HAVING.
	 *
	 * @param 	object 	$db 			Database instance
	 * @param 	array 	$conditions 	conditions statements
	 * @return 	string
	 */
	protected function _compile_conditions(Database $db, array $conditions) {

		$sql = '';
		foreach ($conditions as $logic => $condition) {

			if ( ! empty($sql)) {
				$sql .= ' '.$logic.' ';
			}

			list($column, $op, $value) = $condition;

			if ($value === NULL) {
				if ($op === '=') {
					$op = 'IS';
				} elseif ($op === '!=' OR $op === '<>') {
					$op = 'IS NOT';
				}
			}

			$op = strtoupper($op);

			if (($op === 'BETWEEN' OR $op === 'NOT BETWEEN') AND is_array($value)) {
				list($min, $max) = $value;

				if ((is_string($min) AND array_key_exists($min, $this->_parameters)) === FALSE){
					$min = $db->quote($min);
				}

				if ((is_string($max) AND array_key_exists($max, $this->_parameters)) === FALSE) {
					$max = $db->quote($max);
				}

				$value = $min.'AND'.$max;
			} elseif ((is_string($value) AND array_key_exists($value, $this->parameters)) === FALSE) {
				$value = $db->quote($value);
			}

			if ($column) {
				if (is_array($column)) {
					$column = $db->quote_identifier(reset($column));
				} else {
					$column = $db->quote_column($column);
				}
			}
			$sql .= '(';
			$sql .= trim($column.' '.$op.' '.$value);
			$sql .= ')';
			
		}

		return $sql;
	}

	/**
	 * Compiles an array of SET values into an SQL partial. Used for UPDATE.
	 *
	 * @param 	object 	$db 	Database instance
	 * @param 	array 	$values updated values
	 * @return 	string
	 */
	protected function _compile_set(Database $db, array $values) {

		$set = array();

		foreach ($values as $group) {

			list($column, $value) = $group;

			$column = $db->quote_column($column);

			if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE) {
				$value = $db->quote($value);
			}

			$set[$column] = $column.'='.$value;
 		}

 		return implode(', ', $set);
	}

	/**
	 * Compiles an array of GROUP BY columns into an SQL partial.
	 *
	 * @param 	object 	$db 		Database instance
	 * @param 	array 	$columns 	
	 * @return 	string
	 */
	protected function _compile_group_by(Database $db, array $columns) {

		$group = array();

		foreach ($columns as $column) {
			if (is_array($column)) {
				$column = $db->quote_identifier(end($column));
			} else {
				$column = $db->quote_column($column);
			}
			$group[] = $column;
		}

		return 'GROUP BY '.implode(', ', $group);
	}

	/**
	 * Compiles an array of ORDER BY statements into an SQL partial.
	 *
	 * @param 	object 	$db 		Database instance 
	 * @param 	array 	$columns 	sorting columns
	 * @return 	string
	 */
	protected function _compile_order_by(Database $db, array $columns) {

		$sort = array();

		foreach ($columns as $group) {
			list($column, $direction) = $group;

			if (is_array($column)) {
				$column = $db->quote_identifier(end($column));
			} else {
				$column = $db->quote_column($column);
			}

			if ($direction) {
				$direction = ' '.strtoupper($direction);
			}

			$sort[] = $column.$direction;
		}

		return 'ORDER BY '.implode(', ', $sort);
	}

	/**
	 * Reset the current builder status.
	 *
	 * @param  void
	 * @return $this
	 */
	abstract public function reset();
}