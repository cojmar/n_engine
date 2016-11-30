<?php
/*
*  DATA 2.0
*
*	PURPOSE: singletone data, a $data array that is stored static to provide a singletone data manipulation
*	REQUIRES: n_calss
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class data extends n_class
{
	static  $data = array();			//==static data storage (used by function data() )
	//==Returns data $var or sets it if $val is not NULL
	public function __construct()
	{
		//==Constructor, so data function is not runed as constructor, so it can return something
	}
	public function data($var=NULL,$val=NULL)
	{
		$ret = false;
		if (!is_null($var))
		{
			if (is_null($val) && isset(self::$data[$var]))
			{
				$ret = self::$data[$var];
			}
			elseif(!is_null($val))
			{
				$ret = self::$data[$var] = $val;
			}
		}
		return $ret;
	}
}