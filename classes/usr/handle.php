<?php
/*	Default controller (handler)
* all PROTECTED functions require login and redirect to login if not loged in
* u can read more in classes/core/handler.php
HANDLE V 1.1
*/
if (!class_exists('n_handle')) {$this->n_handle();}

class handle extends n_handle
{
	//==Global stuff shood be here, engine hooks if any
	public function __construct()
	{
		//==Make all calls respond by default as json_data
		//$this->cfg('engine','render_mode','json_data');
	}
	//==DEFAULT handle (if this function is made protected all default cases will require login)
	public function default_action()
	{
		$params = $this->params(func_get_args(),array('pg'=>''));
		//==Add html support
		if (strpos($params['pg'],".html")!==false)
		{
			return $this->handle(str_replace(".html","",$params['pg']));
		}
	}
	//==Home page
	public function home()
	{
		debug('Welcome page');
		debug('Run Time: <!--execution_time--> sec');
		debug('nEngine v 2.0 made by Alex Platon aka Cojmar / Nlghtmare');
	}
	//==Test stuff
	public function test()
	{
		$params = $this->params(func_get_args(),array('pg'=>'','method'=>''));
		return $this->test_handle($params['method']);
	}

}