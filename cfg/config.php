<?php
/*	 nEngine - CONFIG 2.1 */

$config = array();
define("_DS_",DIRECTORY_SEPARATOR);
define("_ROOT_",getcwd()._DS_);

//==Engine
$config['engine'] = array
(
	'get_page_var'							=>	'pg',				//==Routeing var (old style index.php?[get_page_var]=)
	'default_page'							=>	'home',			//==Default route
	'default_lang'							=>	'en',				//==Default Langauge
	'debug_mode'							=>true
);
//==DB
/*
$config['db'] = array
(
	//==DB Login
	'host'				=> '',
	'db'					=> '',
	'user'				=> '',
	'pass'				=> ''
);
*/
//==Path
$config['path'] = array
(
	'scripts'	=> array
	(
		_ROOT_.'classes'._DS_.'core',
		_ROOT_.'classes'._DS_.'modules',
		_ROOT_.'classes'._DS_.'usr'
	),
	'templates'		=> _ROOT_.'views',
	'server_data'	=>_ROOT_.'cfg'._DS_.'server_data'._DS_,
	'absolute_path'	=>	str_replace("//","/","///{$_SERVER['HTTP_HOST']}".str_replace("\\","/",dirname($_SERVER['PHP_SELF']))."/")
);