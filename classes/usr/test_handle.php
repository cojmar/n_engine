<?php
/*	Default Test Class
V 1.2
*/
if (!class_exists('n_class')) {include ("n_class.php");}
class test_handle extends n_class
{
	//==Magic
	function __construct(){}

	public function test_handle()
	{
		$params = $this->params(func_get_args(),array
		(
			'method'				=>'',
		));
		$method = "test_".$params['method'];
		if (method_exists($this,$method))
		{
			return $this->$method();
		}
		else
		{
			$class_methods = get_class_methods($this);
			asort($class_methods);
			$this->data('engine')->output_data->content="Available Test methods<hr>";
			foreach ($class_methods as $method) if ($method != __function__)
			{
				$pos = strpos($method,"test_");
				if ($pos!==false)
				{
					$method_link = $this->cfg('path','absolute_path').str_replace("test_","test/",$method);
					$this->data('engine')->output_data->content .='<a href="'.$method_link.'">'.$method.'</a><br>';
				}
			}
		}
	}
	//==Test image
	public function test_output()
	{
		$this->data('engine')->output_data->content='';
		foreach($this->data('engine')->output_data as $k=>$v)
		{
			$this->data('engine')->output_data->content.="$k: <!--$k--><br>";
		}
	}
	//==Test image
	public function test_img()
	{
		$txt = $this->canvas()->text("TEST",14,0,0);
		$this->canvas()->render(1);
	}

	//==Over write content
	public function test_content()
	{
		$this->data('engine')->output_data->content="Test Overide content <!--my_var-->";
		return array("my_var"=>"<b>OK!</b>");
	}

	//==Dump cfg
	public function test_cfg()
	{
		debug($this->cfg());
		debug($this->template()->linear_ar($this->cfg()));
	}
	//==Test user sesion ser_data and get_data
	public function test_user()
	{
		//$this->user()->set_data('user','guest');
		$this->user()->clear_data();
		debug($this->user()->get_data());
	}
	//==Dumps server data
	public function test_server_data()
	{
		//$this->server_data('version','2.3.2');
		debug($this->server_data()->data());
	}
	//==Test output json
	public function test_json()
	{
		$this->cfg('engine','render_mode','json');
		return array('test'=>'hellow world');
	}
	//==Test output json only data
	public function test_json_data()
	{
		$this->cfg('engine','render_mode','json_data');
		return array('test'=>'hellow world');
	}

	//==Test Db
	public function test_db()
	{
		$sql = 
		"
			SHOW TABLES
		";
		debug($this->db()->query($sql));
	}
	//==Test Post render
	public function test_background()
	{
		debug($this->server_data('test_background'));
		$this->server_data('test_background',0);
		$this->post_render_call(function()
		{
			for ($i=0;$i<=10;$i++)
			{				
				$this->server_data('test_background',$this->server_data('test_background')+1);
				sleep(1);			
			}
		});
	}

	public function test_stream()
	{	
	
		$stream = function($data)
		{
			flush();
			$empty_block ="";for($ii = 1; $ii <= 1024*2; $ii++) $empty_block .=chr(0);
			print $empty_block;
			print $data;
			print $empty_block;
			ob_flush();
			flush();
		};
		$j=5;
		$stream("Started steaming from 1 to $j<hr>");
		for($i=1;$i<=$j;$i++)
		{
			sleep(1);
			$stream("Stream - $i/$j<br>");
		}
		sleep(1);
		$stream("<hr>Streaming stoped");
		die;
	}
}
