<?php
//Version 1.0
if (!class_exists('n_class')) {include ("n_class.php");}
class db extends n_class
{
	static private $connected = false;
	static private $db_mode = false;
	static private $db_handler = false;
	
	//==Connect
	function connect($cfg)
	{
		if (self::$db_handler)
		{
			self::$connected =self::$db_handler->connect($cfg);
		}
		else return false;
	}

	//==Construct
	public function __construct()
	{
		if(!self::$db_mode)
		{
			$cfg_mode = $this->cfg('db','mode');
			self::$db_mode = (!empty($cfg_mode))?$cfg_mode:'mysql';
			eval('self::$db_handler = $this->db_'.self::$db_mode.'();');
		}
		if(!self::$connected)
		{
			$this->connect($this->cfg('db'));
		}
	}
	//==GET limit from array bassed on $this->cfg('db','limit')
	function gen_limit($data)
	{
		$data=func_get_args();
		$data = ((isset($data[0])) && (is_array($data[0]))) ? $data[0]:array();
		foreach ($data as $k=>$v) if (empty($v)) unset($data[$k]);
		$data = $this->params($data,$this->cfg('db','limit'));
		foreach ($data as $k=>$v) $data[$k] = intval($v);
	
		$ret = 
		"
			LIMIT
			{$data['start']}, {$data['length']}	
		";
	
		return $ret;
	}
	//==GET array with db results
	function get($data=NULL)
	{
		if (is_null($data)) $data = $this->data;
		$ret = $this->query($data);
		if (empty($ret)) $ret = false;
		if (!is_array($ret)) $ret = false;
		return $ret;	
	}
	//==ROW get 1st result
	function row($data=NULL)
	{
		if (is_null($data)) $data = $this->data;
		$ret = array();
		if (($r=$this->query($data)) && (count($r)>0))
		{
			return $r[0];
		}
		return false;	
	}
	
	//==INSERT / ON DUPLICATE UPDATE (face vec_sql + query)
	function in($dta,$table,$magic_strip=true)
	{
		if (!self::$connected) return false;
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return  call_user_func_array(array(self::$db_handler, __FUNCTION__), array($dta,$table,$magic_strip));
		}
		$sql = $this->vec_sql($dta,$table,$magic_strip);
		return $this->query($sql);
	}

	//=========================== Handler functions ==========================

	//==Last insert id returns last insert id (doh)
	public function last_insert_id()
	{
		if (!self::$connected) return false;
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return  call_user_func_array(array(self::$db_handler, __FUNCTION__), array());
		}
		return false;
	}

	//==Table columns -> returns array with $table fields or false
	public function table_columns($table=NULL)
	{
		if (!self::$connected) return false;
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return  call_user_func_array(array(self::$db_handler, __FUNCTION__), array($table));
		}
		return false;
	}

	//==Vec sql -> converts $data (array) into sql / insert on duplicate update sintax, returns sql string or false, if magic strip shood remove fields that are not in table columns
	function vec_sql($dta,$table,$magic_strip=true)
	{
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return  call_user_func_array(array(self::$db_handler, __FUNCTION__), array($dta,$table,$magic_strip));
		}
		return false;
	}

	//==Query -> returns results of an mysql query
	public function query($sql)
	{
		if (!self::$connected) return false;
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return call_user_func_array(array(self::$db_handler, __FUNCTION__), array($sql));
		}
		return false;
	}

	//==ESCAPE
	function escape($data=NULL)
	{
		if (!self::$connected) return $data;
		if (is_null($data)) $data = $this->data;
		if(method_exists(self::$db_handler,__FUNCTION__))
		{
			return  call_user_func_array(array(self::$db_handler, __FUNCTION__), array($data));
		}
		return $data;
	}
}


