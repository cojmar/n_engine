<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class db_mysql extends n_class
{
	static private $connected = false;
	
	//==Last insert id
	public function last_insert_id()
	{
		return self::$connected->insert_id;
	}

	//==Table columns
	public function table_columns($data=NULL)
	{
		if (is_null($data)) $data = $this->data;
        $fields = array();
		$sql ="SHOW COLUMNS FROM `$data`";
		if ($q=$this->query($sql))	foreach($q as $k=>$r)
		{
			$fields[]=$r['Field'];
		}
		return $fields;     
	}

	//== Array to sql
	function vec_sql($dta,$table,$magic_strip=true)
	{
		$sql='';
		if ($magic_strip==true)
		{
			$fields = $this->table_columns($table);
		}
		foreach ($dta as $k=>$v)
		{
			if ($magic_strip==true)
			{
				if (in_array($k,$fields))
				{
					$sql .= ($sql=='')?"`$k`='".$this->escape($v)."'":",`$k`='".$this->escape($v)."'";
				}
			}
			else
			{
				$sql .= ($sql=='')?"`$k`='".$this->escape($v)."'":",`$k`='".$this->escape($v)."'";
			}
		}
		$sql ="insert into `$table` set $sql on duplicate key update $sql";
		return $sql;
	}

	//==Connect	
	function connect($cfg)
	{
		if ((is_array($cfg))&&(isset($cfg['host']))&&(isset($cfg['db']))&&(isset($cfg['user']))&&(isset($cfg['pass'])))
		{
			self::$connected = new mysqli($cfg['host'],$cfg['user'], $cfg['pass'], $cfg['db']);
			if (self::$connected->connect_errno) 
			{
				self::$connected = false;
			   return "Failed to connect to MySQL: (" . self::$connected->connect_errno . ") " . self::$connected->connect_error;
			}
			$this->query('SET NAMES utf8');
			return self::$connected;
		}
		return false;
	}

	//==Query
	public function query($sql)
	{
		$ret = false;
		if (self::$connected->multi_query($sql)) 
		{
			$ret = array();
			do {
				/* store first result set */
				if ($result = self::$connected->store_result()) 
				{
					$res = array();
					while ($row = $result->fetch_assoc()) 
					{
						$res[] = $row;
					}
					$ret[] = $res;
					$result->free();
				}
				if (self::$connected->more_results())
				{
					self::$connected->next_result();
				}
			} while (self::$connected->more_results());
			if (count($ret)==1) $ret = $ret[0];
		}
		if (is_array($ret) && count($ret)==0) $ret = true;
		return $ret;
	}

	//==ESCAPE
	function escape($data=NULL)
	{
		if (!self::$connected) return false;
		return self::$connected->real_escape_string($data);	
	}


}