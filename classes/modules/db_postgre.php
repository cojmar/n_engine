<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class db_postgre extends n_class
{
	static private $connected = false;

	//==Last insert id
	public function last_insert_id()
	{
		return false;
		return pg_last_oid();
	}

	//==Table columns
	public function table_columns($data=NULL)
	{

		if (is_null($data)) $data = $this->data;
        $fields = array();
		$sql ="SELECT COLUMN_NAME as field FROM information_schema.columns WHERE table_name ='$data'";
		if ($q=$this->query($sql))	foreach($q as $k=>$r)
		{
			$fields[]=$r['field'];
		}
		return $fields;     
	}

	//==Table constraints
	public function table_constraints($data=NULL)
	{
		if (is_null($data)) $data = $this->data;
        $fields = array();
		$sql = "SELECT column_name  as field FROM information_schema.constraint_column_usage WHERE table_name ='$data'";
		if ($q=$this->query($sql))	foreach($q as $k=>$r)
		{
			$fields[]=$r['field'];
		}
		return $fields;
	}

	function in($dta,$table,$magic_strip=true)
	{
		if ($magic_strip==true)
		{
			$fields = $this->table_columns($table);
			foreach ($dta as $k=>$v) if (!in_array($k,$fields)) unset($dta[$k]);
		}

		$constraints = $this->table_constraints($table);
		$where = array();
		foreach ($dta as $k=>$v) if (in_array($k,$constraints)) 
		{
			$where[$k]=$v;			
		}

		$mode = "insert";

		if (count($where)>=1)
		{
			$exists = pg_select(self::$connected,$table, $where);
			if (pg_select(self::$connected,$table, $where))
			{
				$mode = "update";
			}
		}
		switch ($mode)
		{
			case 'insert':
				return pg_insert(self::$connected, $table, $dta);
			break;

			case 'update':
				return pg_update(self::$connected, $table, $dta, $where);
			break; 
		}
	}
	
	
	//== Array to sql
	function vec_sql($dta,$table,$magic_strip=true)
	{
		return $this->in($dta,$table,$magic_strip);
	}

	//==Connect	
	function connect($cfg)
	{
		if ((is_array($cfg))&&(isset($cfg['host']))&&(isset($cfg['db']))&&(isset($cfg['user']))&&(isset($cfg['pass'])))
		{

			$conn_string = "host={$cfg['host']} port=5432 dbname={$cfg['db']} user={$cfg['user']} password={$cfg['pass']}";
			self::$connected = pg_pconnect($conn_string);

			$this->query("SET CLIENT_ENCODING TO 'UTF8'");
			return self::$connected;
		}
		return false;
	}

	function format_postgre($str)
	{
		$rep = array
		(
			"`"=>''
		);
		foreach ($rep as $k=>$v)	$str = str_replace($k,$v,$str);

		$src = "limit";
		$limit = stripos($str,$src);
		if ($limit !==false)
		{
			$sql = substr($str,0,$limit);
			$limit_str =  trim(substr($str,$limit+strlen($src)));

			$limit_sep = ",";
			$sep_pos = stripos($str,$limit_sep);
			if ($sep_pos !==false)
			{
				$limit_ar = explode($limit_sep,$limit_str);
				$str = $sql." LIMIT ".$limit_ar[1]." OFFSET ".$limit_ar[0];
			}
		}

		return $str;
	}

	//==Query
	public function query($sql)
	{
		ob_start();
		$sql = $this->format_postgre($sql);
		$ret = false;
		if ($q = pg_query(self::$connected, $sql))
		{
			while ($r = pg_fetch_assoc($q)) 
			{
				if (!is_array($ret)) $ret = array();
				$ret[] = $r;
			}
		}
		$error .= ob_get_contents();
		ob_end_clean();


		//file_put_contents("debug_sql.txt", $sql."\n\n--\n\n", FILE_APPEND | LOCK_EX);
		return $ret;
	}

	//==ESCAPE
	function escape($data=NULL)
	{
		if (!self::$connected) return false;
		return pg_escape_string($data);	
	}


}