<?php
/**
 * @package  Dao\Mysql\Exception
 * @category Exception
 * @author   vcentor
 */

namespace Dao\Mysql\Exception;

class MysqlException extends Exception {

	// MYSQL_SYS_ERR
	const MYSQL_SYS_ERR  	= 50000;
	// Invalid param
	const INVALID_PARAM 	= 50001;
	// Select Exception
	const SELECT_ERR		= 50002;
	// Insert into exception
	const INSERT_INTO_ERR 	= 50003;
	// JOIN Exception
	const JOIN_ERR			= 50004;
}