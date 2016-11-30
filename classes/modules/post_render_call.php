<?php
//V1.1
if (!class_exists('n_class')) {include ("n_class.php");}
class post_render_call extends n_class
{
	public function __construct()	
	{
		$this->delegate_calls = array();	
	}

	public function post_render_call()
	{
		$data = func_get_args();
		if (empty($data))
		{
			return $this->proccess_calls();
		}
		$this->delegate_calls[] = $this->params($data,array
		(
			'function'			=>false,
			'data'				=>array()
		));
		return true;
	}

	public function proccess_calls()
	{
		if (empty($this->delegate_calls)) return false;
		header("Content-Encoding: none\r\n");
		foreach($this->delegate_calls as $call_data)
		{
			if (is_callable($call_data['function']))
			{
				$call_data['function']($call_data['data']);
			}
		}
	}
}
