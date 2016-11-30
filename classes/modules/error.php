<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class error extends n_class
{
	public function __construct()	
	{
		$this->errors = $this->session('errors');	
	}

	public function error()
	{
		if (empty(func_get_args()))
		{
			$this->session('errors',false);
			return $this->errors;
		}
		else
		{
			$params = $this->params(func_get_args(),array
			(
				'error'				=>false,
			));
			if (!empty($params['error']))
			{
				if (empty($this->errors))	$this->errors = array();
				$this->errors[] = $params['error'];
				$this->session('errors',$this->errors);
			}
		}
		return true;
	}
}
