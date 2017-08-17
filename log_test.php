<?php
function autoload($classname) {
	$classname = str_replace('\\', '/', realpath(__DIR__).DIRECTORY_SEPARATOR.$classname.'.php');
	if (file_exists($classname)) {
		require_once $classname;
	}
}

spl_autoload_register('autoload');

use Log\Log;

$path = '/Users/vcentor/work/php-modules/log';
Log::instance()->initLog($path, Log::LOG_LEVEL_DEBUG);


Log::fatal('aa');
Log::warning('bb');
Log::debug('cc');
