<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class n_if extends n_class
{
	function __construct(){}
	function n_if($condition,$r=NULL)
	{
		$ret ="";
		if (is_callable($condition)) $condition = $condition();
		if (!empty($condition))
		{
			if (is_null($r)) $r = $condition;
			$ret = (is_callable($r)) ? $r():$r;
		}
		return $ret;
	}
}