<?php
/*
* SERVER DATA 2 - TRANSPARENT VERSION
*
*	PURPOSE: persistent server data, a $data array that is stored into a server file saved on __destruct loaded on __ construct
*	REQUIRES: n_calss 2.1 or higher
*	OPTIONAL: $config['path']['server_data_location'] if n_calss config present
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class server_data extends n_class
{
	## PRIVATE ##

	private		$data				= false;
	private		$data_location	= '';

	//==Inits data
	private function init_data()
	{
		$this->data = (object)array();
		if (!file_exists($this->data_location)) return false;
		$files = array_diff(scandir($this->data_location), array('..', '.'));
		foreach ($files as $file)
		{
			$var = $this->var_file_name($file,1);
			$this->data->$var = function() use ($var)
			{
				return $this->load_var($var);
			};
		}
	}
	//==Encode data
	private function encode_data($data)
	{
		return @json_encode($data,JSON_PRETTY_PRINT);
	}
	//==Dencode data
	private function decode_data($data)
	{
		return @json_decode($data,1);
	}
	//==Var file name
	private function var_file_name($var,$decode=false)
	{
		return $var;
		if (!$decode)
		{
			return base64_encode($var);
		}
		return base64_decode($var);
	}

	//==Saves $var to file
	private function save_var($var,$val)
	{
		if (!empty($this->data_location))
		{
			$file_name = $this->data_location.$this->var_file_name($var);
			file_put_contents($file_name, $this->encode_data($val), LOCK_EX);		
			
			$this->data->$var = function() use ($var)
			{
				return $this->load_var($var);
			};			
			return true;
		}
		return false;
	}
	
	//==Load $var from file
	private function load_var($var)
	{
		if (!empty($this->data_location))
		{
			$file_name = $this->data_location.$this->var_file_name($var);			
			if (file_exists($file_name))
			{
				return $this->decode_data(file_get_contents($file_name));
			}
		}
		return false;
	}

	## MAGIC METHODS##	
	function __construct()
	{
		$this->data_location = $this->cfg("path","server_data");
		if (empty($this->data_location))
		{
			$this->data_location	= getcwd().DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.'server_data'.DIRECTORY_SEPARATOR;
		}
		$this->init_data();
	}

	
	## PUBLIC ##
	//==Frees $var or all $data if $var is NULL
	public function free($var = NULL)
	{
		if(!empty($var))
		{
			$file_name = $this->data_location.$this->var_file_name($var);
			if (file_exists($file_name)) unlink($file_name);
			if (isset($this->data->$var)) unset($this->data->$var);
		}
		else
		{
			foreach ($this->data as $var => $val)
			{
				$file_name = $this->data_location.$this->var_file_name($var);
				if (file_exists($file_name)) unlink($file_name);
				if (isset($this->data->$var)) unset($this->data->$var);
			}
		}
		return $this;
	}

	//==Dumps $data $var or all $data if $var is NULL
	public function data($var = NULL)
	{
		$ret = "";
		if (is_null($var))
		{
			$ret = array();
			foreach($this->data as $key => $var)
			{
				$ret[$key] = $var();
			}
		}
		else
		{
			$ret = $this->load_var($var);
		}
		return $ret;
	}

	//==Returns data $var or sets it if $val is not NULL
	public function server_data($var=NULL,$val=NULL)
	{
		$ret = NULL;
		if (!is_null($var))
		{
			$ret = false;
			if (is_null($val))
			{
				$ret = $this->load_var($var);
			}
			elseif(!is_null($val))
			{
				$this->save_var($var,$val);
				$ret = $this;
			}
		}
		return $ret;
	}
}