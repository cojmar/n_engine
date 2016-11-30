<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class params extends n_class
{
	
	/*
		get_params - magic trasforms parameters it it recives
		Returns: numeric array with all values recived
		Special case 1 value: if is 1 parameter and is not array and not object trys to explode it by , to produce an array
		Examples:
						$params = $this->get_params("a","b");
						$params = $this->get_params(array("a","b"));//Same for object as 1 param

		All 2 methods return : Array([0]=>'a',[1]=>'b')
		TIP: inside another function like this : $params = $this->get_params(func_get_args());
		
		VERSION 2
		- removed method "a,b"
	*/
	function __construct(){}
	private function get_params()
	{
		$params = func_get_args();
		if ($count = count($params))
		{
			if ($count==1)
			{
				$params	= $params[0];
				$count		= count($params);
				if($count > 0)
				{
					if ($count == 1)
					{
						if (is_array($params)) $params = $params[0];
						if (is_object($params)) $params = (array)$params;
						if (!is_array($params))
						{
							$params = array($params);
						}
					}
					return $params;
				}
			}
			return $params;
		}
		return NULL;
	}

	//==Wrapper to compute get_params for $params and populate an array based on $pattern
	private function compute_params($params,$pattern)
	{
		if (!$params = $this->get_params($params)) $params = array();
		$count = count($params);
		$i = 0;
		$ret = array();
		foreach ($pattern as $default_key=>$default_value)
		{
			if (isset($params[$i]))
			{
				$ret[$default_key] = ($i < $count) ? $params[$i] : $default_value;
			}
			elseif(isset($params[$default_key]))
			{
				$ret[$default_key] = $params[$default_key];
			}
			else
			{
				$ret[$default_key]=$default_value;
			}
			//==Unset NULL key
			if (is_null($ret[$default_key])) unset($ret[$default_key]);
			$i++;
		}
		return $ret;
	}
	
	public function params($params_input=array(),$params_pattern=null)
	{
		if (empty($params_pattern))
		{
			return  $this->get_params($params_input);			
		}
		return $this->compute_params($params_input,$params_pattern);
	}
}