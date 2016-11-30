<?php
/*		Project name : N_CLASS (the N class)
*		Author : Alex Platon
*		Version : 2.5 
*
*		Description : 
*			This class has the main purpose to include other classes as functions if the function not exsits on call, 
*			and to be used as a base class that other extend it to make an singleton framework.
*			
*		Optional Requirements : 
*			This class needs a script in the path "cfg/config.php" that must contain at least this : 
*				<?php
*				$config = array('');
*				$config['path'] = array
*				(
*					'scripts' => 'classes'
*				);
*
*			The $config['path']['scripts'] is the default location where the php files of classes are searched, if u whant to register aditional locations
*           u can use the register_path function provided in the public functions section.
*			2.3 change:  $config['path']['scripts'] can be array or object
*			2.3.1 add support for old php 
*			2.4	$this->cfg() will return all intialized config until now as an array
*					$this->cfg('path','scripts') can be array or object
*			2.5 redone __callStatic to work on more php versions
*
*			Since version 2.1 config file is optional if it is missing current class path will be default include path
*
*		Aditional functions:
*			cfg -> returns config $variable, example : $this->cfg('path','scripts') shood return `scripts` var from `path` section in config
*			2.2(change) :
*								1) cfg suports 3rd param $this->cfg('path','scripts','new_path');
*								2) cfg will return result of functions if the value of var is a function:
*								$this->cfg('engine','render_mode','wml');		
*								$this->cfg('path','templates',function()
*								{
*									return 'views_'.$this->cfg('engine','render_mode');
*								});
*								$this->cfg('path','templates') will return output of the function
*			register_path -> registers one or more include path to search for class files. 
*			(by default this function is called to register the scripts path from config in function load_class)
*
*		Example:
*		$this->test();// will return an object of class test
*/	

/*Version Log
*  2.3 $this->cfg('path','scripts') can be array or object
*	2.2	- modular config
*	2.1	- config is optional
*	2.0	- beta
*	1		- alpha
*/
class n_class
{
	######################## STATIC VARS	
	static private $config = false;				//==Config data
	static protected $methods = array();		//==Array with dynamic loaded methods	
	static private $include_path = array();	//==Array with paths from where the clases are included, default path is in config
	######################## PUB VARS	
	
	######################## PRIVATE FUNCTIONS
	//==Init the config
	private function init_config()
	{
		if (!self::$config) 
		{
			$config_file = "cfg/config.php";
			if (file_exists($config_file))
			{
				include  $config_file;
				if (isset($config))
				{
					self::$config=$config;
					unset($config);
				}
			}
			else self::$config=array();
		}
	}
	//==Loads a class and returns it
	private function load_class($class,$args=NULL)
	{
		$ret = false;
		//== Register default path if not set yet
		$default_path = self::cfg("path","scripts");//try get it from config
		if (empty($default_path)) $default_path = dirname(__FILE__);//register current path if config fails

		if (!empty($default_path))
		{
			if (is_array($default_path) || is_object($default_path))
			{
				foreach ($default_path as $path) if(!in_array($path,self::$include_path))
				{
					self::register_path($path);
				}
			}
			elseif(!in_array($default_path,self::$include_path))
			{
				self::register_path($default_path);
			}
		}
		//== Search all $include_path for the file
		foreach(self::$include_path as $path)
		{
			$file = $path.DIRECTORY_SEPARATOR.$class.".php";
			if (file_exists($file))
			{
				include_once($file);
			}
		}
		//== If class exists try construct
		if (class_exists($class))
		{
			$r = new ReflectionClass($class);
			$constructor = $r->getConstructor();
			if (!empty($constructor))
			{
				if($args)
				{
					$ret = $r->newInstanceArgs($args);
				}
				else
				{
					$ret = $r->newInstance();
				}
			}
			else
			{
				if (method_exists($r,'newInstanceWithoutConstructor'))
				{
					$ret = $r->newInstanceWithoutConstructor();
				}
				else
				{
					$ret = new $class;
				}
			}
		}
		return $ret;
	}

	//==Called when function not exists trys to get a class instead (if the class has a function with the same name that will be called to with $args and result will be returned)
	private function my_call($method, $args=NULL)
    {
		//==IF CLASS IS LOADED CALL THE METHOD
		if (isset(self::$methods[$method])) 
		{
			$r_class = self::$methods[$method];			
			if(method_exists($r_class,$method))
			{
				$call = call_user_func_array(array($r_class, $method), $args);
				if (!is_null($call)) $r_class = $call;
			}
			return $r_class;
		}
		//==IF CLASS IS NOT LOADED, LOAD IT AND CALL THE METHOD IF METHOD IS NOT CONSTRUCTOR
		elseif (self::$methods[$method] = self::load_class($method,$args))
		{
			$r_class = self::$methods[$method];//== returning class
			$call_method =(method_exists($r_class,$method))?true:false; //== see if method exists

			if ($call_method) //== if method exists see if is not constructor
			{
				$reflection = new ReflectionClass($method);
				if($constructor = $reflection->getConstructor())
				{
					if ($constructor->name == $method) $call_method = false;
				}
			}
			if($call_method) //==if method exists and is not constructor call the method
			{
				$call = call_user_func_array(array($r_class, $method), $args);
				if (!is_null($call)) return  $call;
			}
			return $r_class;
		}
    }


	######################## PUBLIC FUNCTIONS
	
	//==Registers a $path as an include path
	public function register_path($path)
	{
		self::$include_path[]=$path;
	}	
	//==Executs when function not found (warps to my_call)
	public function __call($method, $args)
    {
		return $this->my_call($method, $args);
    }

	//==Executs when static function not found (warps to my_call)
	public static function __callStatic($method, $args)
    {
		$n_c = new n_class();
		return $n_c->my_call($method, $args);
    }

	//==Returns config $var or $subvar of var if !$set_var else overwrites config var
	public function cfg($var=NULL,$subvar=false,$set_var=null)
	{
		$ret = NULL;
		if (is_null($var))
		{
			$ret = self::$config;
		}
		if (is_null($set_var))
		{
			self::init_config();
			if (isset(self::$config[$var]))
			{
				if ($subvar!==false)
				{
					$ret =(($subvar!==false) && isset(self::$config[$var][$subvar]))?self::$config[$var][$subvar]:NULL;
				}
				else $ret = self::$config[$var];
			}
		}
		elseif(!empty($var))
		{
			if (empty($subvar))
			{
				self::$config[$var] = $set_var;
			}
			else
			{
				self::$config[$var][$subvar] = $set_var;
			}
			$ret =  $set_var;
		}
		if (is_callable($ret)) return $ret();
		return $ret;
	}

}