<?php
/*	returns a unique 32 bits ID saved in cookie
*  PURPOSE - generate a unique id per browser/device
* 
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class client_id extends n_class
{
	//==MAGIC
	public function __construct()	
	{
		$this->cookie_name = md5(dirname($_SERVER['SCRIPT_FILENAME']));
	}
	//==PRIVATE
	private function gen_id()
	{
		return md5(uniqid(mt_rand(), true));
	}
	private function get_id()
	{
		return (!empty($_COOKIE[$this->cookie_name]))?$_COOKIE[$this->cookie_name]:false;
	}
	private function save_id($id)
	{
		setcookie
		(
		  $this->cookie_name,
		  $id,
		  time() + (10 * 365 * 24 * 60 * 60)
		);
	}
	private function regen_id()
	{
		$id = $this->gen_id();
		$this->save_id($id);
		return $id;
	}
	//==PUBLIC
	public function client_id()
	{
		if (!$id = $this->get_id())
		{
			$id = $this->regen_id();
		}
		else
		{
			$this->save_id($id);
		}
		return $id;
	}
}