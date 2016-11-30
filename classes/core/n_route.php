<?php
/*	Default router
* Purpose - rewrite_url
* v1.4
*/

if (!class_exists('n_class')) {include ("n_class.php");}
class n_route extends n_class
{
	public function __construct(){}
	public function n_route()
	{		
		$path = false;
		$real_path = substr($_SERVER['REQUEST_URI'],strlen(dirname($_SERVER['SCRIPT_NAME'])));
		$path_info = pathinfo($real_path);
		$uri_path =(isset($path_info['dirname']) && strlen($path_info['dirname'])>1)?$path_info['dirname']:'';
		if (!empty($uri_path) && substr($uri_path,0,1)=="/") $uri_path =substr($uri_path,1);		
		if (!empty($uri_path)) $uri_path.="/";
		$uri_path .=$path_info['basename'];
		if($path_info['basename'] == $path_info['filename'] && !empty($uri_path))
		{
			$path=explode("/",$uri_path);
		}
		elseif(!empty($path_info['basename']))
		{
			$path=explode("/",$uri_path);
		}
		//debug($path);
		return $path;
	}
}