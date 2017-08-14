<?php
/**
 * @package  Dao\Mysql\Exception
 * @category Exception
 * @author   vcentor
 */

namespace Dao\Mysql\Exception;

class MysqlException extends Exception {

	// MYSQL_SYS_ERR
	const MYSQL_SYS_ERR = 50000;
	// Invalid param
	const INVALID_PARAM = 50001;
}