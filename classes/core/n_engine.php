<?php
/*
N-ENGINE II
n_class based engine

Version 2.3.2
*/
if (!class_exists('n_class')) {include ("n_class.php");}
class n_engine extends n_class
{
	## PUBLIC ##
	function __construct(){}
	public function n_engine()
	{
		$this->time_start = microtime(true);
		$this->init();
		$this->main();
	}
	## PRIVATE ##
	//==Init
	private function init()
	{
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		if (!function_exists("debug"))
		{
			function debug($data,$mode=NULL)
			{
				$nc = new n_class();
				if(!$nc->cfg('engine','debug_mode')) return false;
				switch($mode)
				{
					default:
						$ret_data = print_r($data,1);
					break;
					case 1:
						$ret_data = json_encode($data);
					break;

					case 2:
						$ret_data = var_export($data,1);
					break;
				}
				print "<div style='background-color:#efefef;padding:5px;border:1px solid #5d5d5d;border-top:0px;'><xmp>".$ret_data."</xmp></div>";
			}
		}
		$this->cfg();
		//==Init RENDER MODE if not set in config, if is ajax repond json
		$render_mode = $this->cfg('engine','render_mode');
		if (empty($render_mode))
		{
			$render_mode = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')?'json':'html';
			$this->cfg('engine','render_mode',$render_mode);
		}
	}
	//==Run
	private function run()
	{	
		if (!$this->session('lang')) $this->session('lang',$this->cfg('engine','default_lang'));		
		$page = $this->input($this->cfg('engine','get_page_var'));
		$page_data = $_REQUEST;		
		
		if (empty($page) && $routes = $this->n_route())
		{
			$page = $routes[0];
			$page_data = $routes;
		}		

		if (empty($page)) $page = $this->cfg('engine','default_page');
		if ($this->session('redirect_data'))
		{
			$controler_data = $this->handle($page,$this->session('redirect_data'));
			$this->session('redirect_data',false);
		}
		else
		{
			$controler_data = $this->handle($page,$page_data);
		}
		if (is_null($this->output_data->content))
		{
			$this->output_data->content = $this->template($controler_data->template,$controler_data);
		}
		else
		{
			$this->output_data->content = $this->template()->insert_vars($this->output_data->content,$controler_data);
		}
		//==default data 
		if ($default_data = $this->cfg('default_response'))
		{
			$controler_data->data = (is_array($controler_data->data))?array_merge($default_data,$controler_data->data) : $default_data ;
		}
		foreach ($controler_data as $k=>$v) $this->output_data->$k=$v;
	}
	//==Render
	public function render($data)
	{
		if (!headers_sent())
		{
			switch ($this->cfg('engine','render_mode'))
			{
				default:
					@header("Content-type: text/html; charset=utf-8");
				break;
				case 'wml':
					@header("Content-type: text/vnd.wap.wml; charset=utf-8");
				break;
				case 'json_data':
				case 'json':
					@header("Content-type: application/json; charset=utf-8");
				break;
			}
		}
		
		switch ($this->cfg('engine','render_mode'))
		{
			default:
				$output = 	$this->template('layout',$data);
			break;
			case 'json':
				if (isset($data->content)) unset($data->content);
				$output =(empty($data))?"{}":json_encode($data);
			break;
			case 'json_data':
				$output =(empty($data->data))?"{}": json_encode($data->data);
			break;
		}
		@ob_end_clean();
		if (!headers_sent()) header("Connection: close\r\n");
		ignore_user_abort(true); // optional
		ob_start();
		print $output;
		$size = ob_get_length();
		if (!headers_sent()) header("Content-Length: $size");
		ob_end_flush();     // Strange behaviour, will not work
		flush();            // Unless both are called !
		//ob_end_clean();
		session_write_close();
		$this->post_render_call();
	}
	//==Main
	private function main()
	{
		//==Assign $this->data('engine'), for posble call from anywhere
		$this->data('engine',$this);
		//==Init $output_data buffer as array()
		$output_data=array("content"=>NULL,"debug"=>"",'execution_time'=>0);
		//==Add $output_data buffer as object to $this, for future modifications from anywhere u can call: $this->data('engine')->output_data
		$this->output_data = (object)$output_data;
		ob_start();
			$this->run();
			$this->output_data->debug = ob_get_contents();
		@ob_end_clean();
		//==Add execution time 
		$this->time_end = microtime(true);
		$this->output_data->execution_time = $this->time_end - $this->time_start;
		//==Render
		$this->render($this->output_data);
	}
}