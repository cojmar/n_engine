<?php
/*	Default user model
V 1.0
*/
if (!class_exists('n_class')) {include ("n_class.php");}
class user extends n_class
{
	//==Magic
	function __construct()
	{
		$this->session_var = "user_".md5(dirname($_SERVER['SCRIPT_FILENAME']));
		$this->restore_data();
	}
	function __destruct()
	{
		$this->store_data();
	}
	//==Private
	private function restore_data()
	{
		$this->user_data = $this->session($this->session_var);
		if (empty($this->user_data)) $this->clear_data();
	}
	private function store_data()
	{
		$this->session($this->session_var,$this->user_data);
	}
	//==Public
	public function clear_data($sub_data = NULL)
	{
		if (is_null($sub_data))	$this->user_data = (object)array();
		elseif (isset($this->user_data->$sub_data)) unset($this->user_data->$sub_data);
		$this->store_data();
		return $this;
	}
	public function set_data()
	{
		$params = $this->params(func_get_args(),array
		(
			"key"=>null,
			"value"=>null
		));
		if (!empty($params['key'])) $this->user_data->$params['key']=(isset($params['value']))?$params['value']:null;
		$this->store_data();
		return $this;
	}
	public function get_data($var=NULL)
	{
		if (!empty($var)) return (isset($this->user_data->$var))?$this->user_data->$var:NULL;
		return $this->user_data;
	}
	//==Public USRER
	public function loged_in()
	{
		return true;
	}
}