<?php
/*
*  INPUT 2.0
*
*	PURPOSE: returns $_REQUEST (as default $method or can return $_GET or $_POST if method is specified) of $var, if var not defined returns 
* $default_val
*	REQUIRES: n_calss
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class input extends n_class
{
	public function __construct(){/* Constructor, so data function is not runed as constructor, so it can return something */}

	public function input($var=NULL,$default_val=false,$method="")
	{
		$method = strtolower($method);
		switch ($method)
		{
			default:
				$arr = $_REQUEST;
			break;

			case 'get':
				$arr = $_GET;
			break;

			case 'post':
				$arr = $_POST;
			break;
		}
		if (empty($var)) return (object) $arr;
		return (isset($arr[$var]))?$arr[$var]:$default_val;
	}
}