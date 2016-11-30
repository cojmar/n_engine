<?php
/*
* TEMPLATE 2.6
*
*	PURPOSE: returns a html template loaded from $file populated with $vars and printed if $print 
*	REQUIRES: n_calss 2.1 or higher, $config['path']['templates']
*	2.3 suports { } tag as $php ident u can place things like this: {$this->template('template2')} and a secondary template will be included
*	2.4	 added $force parameter that makes a template from given $file string
*	2.5	 added $this->data to be used inside { } to recive vars send
*	2.6	removed $print
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class template extends n_class
{
	static private $templates					= array();
	private $before_var							= "<!--";
	private $after_var							= "-->";
	private $result_template	= "";

	function __toString()
	{
		return $this->result_template;
	}
	function __construct(){}

	public function insert_vars($data,$vars)
	{
		if (is_object($vars)) $vars = (array)$vars;
		if (is_array($vars))
		{
			foreach ($vars as $k=>$v)
			{
				if (!is_array($v)&&!is_object($v))
				{
					$data=str_replace("{$this->before_var}{$k}{$this->after_var}",$v,$data);
				}
				else 
				{
					$data = $this->insert_vars($data,$v);
				}
			}
		}
		return $data;
	}

	public function linear_ar($data)
	{
		$ret = false;
		if (is_array($data) || is_object($data))
		{
			$ret = array();
			foreach($data as $k=>$v)
			{
				if (!is_array($v) && !is_object($v))
				{
					$ret[$k] = $v;
				}
				elseif($v_data = $this->linear_ar($v) )
				{
					foreach ($v_data as $k_v => $v_v)
					{
						$ret["{$k}_{$k_v}"] = $v_v;
					}
				}
			}
		}
		return $ret;
	}


	function template($file=NULL,$vars=NULL,$force = false)
	{
		if (empty($file)) return NULL;
		$file_key = md5($file);
		if (!isset(self::$templates[$file_key]))
		{
			$true_file = "".$this->cfg("path","templates").DIRECTORY_SEPARATOR.$file.".html";		
			self::$templates[$file_key] = (file_exists($true_file))?file_get_contents($true_file):"";
		}
		if ($force && empty(self::$templates[$file_key]))
		{
			self::$templates[$file_key] = $file;
		}
		$this->result_template = (isset(self::$templates[$file_key]))?self::$templates[$file_key]:"";
		$this->result_template = $this->php_eval($this->result_template,$vars);
		if (!is_null($vars))
		{
			$this->result_template = $this->insert_vars($this->result_template,$vars);
			if (is_array($vars))
			{
				$vars = array_reverse($vars);
				$this->result_template = $this->insert_vars($this->result_template,$vars);
			}
		}
		return $this->result_template;
	}

	function php_eval($str=NULL,$vars=NULL)
	{
		$td = "TAG_TAG";
		$render = str_replace("<<<$td", '', $str);
		$output = '';
		$this->data = $vars;
		eval("\$output = <<<$td\n{$render}\n$td;\n");
		return $output;
	}

}