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
	// SELECT
	// $result = DB::query(Database::SELECT, 'SELECT * FROM user LIMIT 1')->execute('default', '');
	// $result = DB::select(array(DB::expr('COUNT(1)'), 'count'),'name')->from('user')->group_by('name')->execute();
	// $result = DB::select('name', 'password')->distinct(TRUE)->from('user')->execute();
	// $result = DB::select()->from('user')->where('name', '=', 'b')->or_where('name', '=', 'a')->execute();
	// $result = DB::select(array(DB::expr('COUNT(*)'), 'count'))->from('user')->execute();
	// $result = DB::select('name', 'password')->from('user')->group_by('name', 'password')->execute();

	// INSERT
	// $result = DB::insert('user', array('id', 'name', 'password', 'create_time', 'update_time'))->values(array(3,'xs', 'xs', $time, $time),array(4, 'xss', 'xss', $time, $time))->execute();
	// $result = DB::insert('user')->values(array('6','c', 'xsx', $time, $time))->execute();

	// DELETE 
	// $result = DB::delete('user')->where('id', '=', '7')->execute();
	// $result = DB::delete('user')->order_by('id', 'desc')->limit(1)->execute();

	// update
	// $result = DB::update('user')->set(array('name'=>'a'))->where('id', '=', 1)->execute();
	// $result = DB::update('user')->set(array('name'=>'ccc'))->where('name', '=', 'xs')->order_by('id', 'desc')->limit(1)->execute();

	// join

	var_dump($result);
} catch (Exception $e) {
	echo $e->getMessage();
}