<?php
/*	Default controller (handler)
* Purpose - Controler/Router class ment to be extended by a handle.class
* All non public functions (i recomand using PROTECTED works with extension of this class, static is not callable in classes that extend this)
* require login and to redirect to login if not loged in
* for this to work a class user must exists with a function loged_in
* if class user is not present every one has access, if is present the function loged_in must return false when user is not loged in
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class n_handle extends n_class
{
	public function __construct(){}	
	public function redirect()
	{
		$params = $this->params(func_get_args(),array
		(
			'page'				=>false,
			'params'			=>false
		));

		$redirect_data =  ($params['params']) ? $params['params']:array();
		$this->session('redirect_data',$redirect_data);

		$url = $this->cfg('path','absolute_path').$params['page'];
		header("Location: $url");
		$this->post_render_call();
		die;	
	}
	
	public function handle()
	{
		$params = $this->params(func_get_args(),array
		(
			'page'				=>'',
			'params'			=>false,
			'redirect'			=>false
		));

		if ($params['redirect'])
		{
			$this->redirect($params);
		}

		$need_login = false;
		$method = "default_action";
		if (method_exists($this,$params['page']))
		{
			$method = $params['page'];
			$reflection = new ReflectionMethod($this, $method);
			if (!$reflection->isPublic()) $need_login = true;
		}
		else
		{
			$reflection = new ReflectionMethod($this, $method);
			if (!$reflection->isPublic()) $need_login = true;
		}
		//==Check if class user exists, if yes a function loged_in in class user is called that must respond false if not loged in
		$user_check = $this->user();
		if (!empty($user_check))
		{
			if ($need_login && !$this->user()->loged_in())
			{
				return $this->handle('login');
			}
		}
		$ret =(object)array
		(
			'page'		=> $params['page'],
			'template'	=> $params['page'],
			'handler'	=> $method,
			'data'		=> $this->$method($params['params'])
		);

		if (is_array($ret->data) ||is_object($ret->data))
		{
			foreach($ret as $key=>$cur_val)
			{
				if (is_array($ret->data)) $ret->$key = isset($ret->data[$key]) ? $ret->data[$key]:$ret->$key;
				else if (is_object($ret->data)) $ret->$key = isset($ret->data->$key) ? $ret->data->$key:$ret->$key;
			}
		}
		return $ret;
	}
}