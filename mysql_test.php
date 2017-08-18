<?php

function autoload($classname) {
	$classname = str_replace('\\', '/', realpath(__DIR__).DIRECTORY_SEPARATOR.$classname.'.php');
	if (file_exists($classname)) {
		require_once $classname;
	}
}

spl_autoload_register('autoload');

use Dao\Mysql\DB;
use Dao\Mysql\Database;

$time = time();
try {
	Database::instance();
	// var_dump(Database::instance()->list_columns('user'));die;
	// var_dump(Database::instance()->list_columns('user', 'u%'));die;
	// var_dump(Database::instance()->list_table());die;

	// SELECT
	// $result = DB::query(Database::SELECT, 'SELECT * FROM user LIMIT 1')->execute();
	// $result = DB::select(array(DB::expr('COUNT(1)'), 'count'),'name')->from('user')->group_by('name')->execute();
	// $result = DB::select(array(DB::expr('COUNT(1)'), 'count'),'name')->from('user')->group_by('name')->execute(TRUE);
	// $result = DB::select('name', 'password')->distinct(TRUE)->from('user')->execute();
	// $result = DB::select()->from('user')->where('name', '=', 'b')->or_where('name', '=', 'a')->execute();
	// $result = DB::select(array(DB::expr('COUNT(*)'), 'count'))->from('user')->execute();
	// $result = DB::select('name', 'password')->from('user')->group_by('name', 'password')->execute();

	// INSERT
	// $result = DB::insert('user', array('name', 'password', 'create_time', 'update_time'))->values(array('xs', 'xs', $time, $time),array('xss', 'xss', $time, $time))->execute();
	// $result = DB::insert('user')->values(array('7','c', 'xsx', $time, $time))->execute();

	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('超级管理员', 1, $time, $time))->execute();
	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('超级管理员', 2, $time, $time))->execute();

	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('cms系统', 1, $time, $time))->execute();
	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('cms系统', 2, $time, $time))->execute();
	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('cms系统', 3, $time, $time))->execute();

	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('用户管理系统', 1, $time, $time))->execute();
	// $result = DB::insert('role', array('name', 'uid', 'ctime', 'utime'))->values(array('用户管理系统', 3, $time, $time))->execute();

	// DELETE 
	// $result = DB::delete('user')->where('id', '=', '7')->execute();
	// $result = DB::delete('user')->order_by('id', 'desc')->limit(1)->execute();

	// update
	// $result = DB::update('user')->set(array('name'=>'aaaa'))->where('id', '=', 10)->execute();
	// $result = DB::update('user')->set(array('name'=>'ccc'))->where('name', '=', 'c')->order_by('id', 'desc')->limit(1)->execute();

	// join
	// $result = DB::select()->from('user')->join('role', 'left')->on('user.id', '=', 'role.uid')->having('user.name', '=', 'a')->execute();
	// $result = DB::select()->from('user')->join('role', 'left')->on('user.id', '=', 'role.uid')->having('user.name', '=', 'a')->execute(TRUE);
	$result = DB::select()->from('user')->join('role', 'right')->on('user.id', '=', 'role.uid')->execute();

	var_dump($result);
} catch (Exception $e) {
	echo $e->getMessage();
}