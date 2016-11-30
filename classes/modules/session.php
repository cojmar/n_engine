<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class session extends n_class
{
	private function start()
	{
		if(!session_id()) 
		{
			session_start();
			header('P3P: CP="IDC DSP COR CURa ADMa OUR IND  PHY ONL COM STA"');		
		}
	}

	function __construct()
	{
		$this->start();
	}

	//==Returns $_SESSION $var or sets it if $val is not NULL, if not is set session $var returns false
	public function session($var=NULL,$val=NULL)
	{
		$ret = false;
		if (!is_null($var))
		{
			if (is_null($val) && isset($_SESSION[$var]))
			{
				$ret = $_SESSION[$var];
			}
			elseif(!is_null($val))
			{
				$ret = $_SESSION[$var] = $val;
			}		
		}
		return $ret;
	}
}