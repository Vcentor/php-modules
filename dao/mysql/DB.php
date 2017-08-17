<?php
/**
 * Make query 
 *
 * @package 	Dao\Mysql
 * @category 	DB
 * @author 		vcentor
 */

namespace Dao\Mysql;
use Dao\Mysql\Database;
use Dao\Mysql\Db\Query;
use Dao\Mysql\Db\Query\Expression;
use Dao\Mysql\Db\Query\Builder\Select;
use Dao\Mysql\Db\Query\Builder\Insert;
use Dao\Mysql\Db\Query\Builder\Update;
use Dao\Mysql\Db\Query\Builder\Delete;


class DB {

	/**
	 * QUERY
	 *
	 *  DB::query(Database::SELECT, 'SELECT * FROM `users`');
	 *
	 * @param 	integer 	$type
	 * @param 	string 		$sql
	 * @return 	Database
	 */
	public static function query($type, $sql) {
		return new Query($type, $sql);
	}

	/**
	 * SELECT
	 *
	 *  // select id, username
	 *  DB::select('id', 'username');
	 *
	 *  // SELECT id AS user_id
	 *  DB::select(array('id', 'user_id'))
	 * 
	 * @param 	mixed 	$columns
	 * @return 	Select
	 */
	public static function select($columns = NULL) {
		return new Select(func_get_args());
	}

	/**
	 * SELECT_ARRAY
	 *
	 *    // select id, username
	 *    DB::select_array(array('id', 'username'))
	 *
	 * @param  array $columns
	 * @return Select
	 */
	public static function select_array(array $columns = NULL) {
		return new Select($columns);
	}

	/**
	 * INSERT
	 *
	 *  DB::insert('users', array('id', 'username'));
	 *
	 * @param 	string 	$table
	 * @param 	array 	$columns
	 * @return 	Insert
	 */
	public static function insert($table = NULL, array $columns = NULL) {
		return new Insert($table, $columns);
	}


	/**
	 * UPDATE
	 * 
	 *    DB::update('users');
	 * @param 	string 	$table
	 * @return 	Update
	 */
	public static function update($table = NULL) {
		return new Update($table);
	}

	/**
	 * DELETE
	 *
	 *     DB::delete('users');
	 *
	 * @param 	string 	$table
	 * @return 	Delete
	 */
	public static function delete($table = NULL) {
		return new Delete($table);
	}

	/**
	 * EXPR
	 * 
	 *    DB::expr('COUNT(users.id)');
	 *    DB::update('users')->set(array('login_count' => DB::expr('login_count + 1')))->where('id', '=', $id);
	 *
	 * @param 	string 	$string
	 * @param 	array 	$parameters
	 * @return Expression
	 */
	public static function expr($string, array $parameters = NULL) {
		return new Expression($string, $parameters);
	} 
}