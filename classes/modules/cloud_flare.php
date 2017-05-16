<?php
/*
*  Implementation for Cloud Flare client V4
* universal class with n_class config detection 
*/
class cloud_flare 
{
	//==INIT
	public function __construct($construct_data = false)
	{	
		if (!is_array($construct_data)) $construct_data = array();
		$this->last_error = '';
		//==N_class cloud_flare config detection - if not found will go on normal config
		if (class_exists('n_class'))
		{
			$n_c = new n_class;
			$cfg = $n_c->cfg('cloud_flare');
		}
		//######   Normal config or other way here #################
		if (empty($cfg)) $cfg = array
		(
			//==Cloud flare api key
			'api_key'			=>	"",
			//==Domain email, example: admin@baff.ro
			'email'				=>	"",
			//==Domain name, example: baff.ro
			'domain'			=> "", 
		);
		//###########################################
		$this->init($cfg);
		if (!empty($construct_data)) $this->init($construct_data);
		//==Define debuging function if not present
		if (!function_exists("debug"))
		{
			function debug($data)
			{
				$ret_data = print_r($data,1);
				print "<div style='background-color:#efefef;padding:5px;border:1px solid #5d5d5d;border-top:0px;'><xmp>".$ret_data."</xmp></div>";
			}
		}
	}
	//==Inits c_data (class data used for processes)
	public function init($data)
	{
		if (empty($data)) $data = array();
		if (empty($this->c_data)) $this->c_data = (object)array
		(
			'api_key'			=>	"",
			'email'				=>	"",
			'domain'			=> "",
			'api_url'			=> "https://api.cloudflare.com/client/v4/",
			'debug_mode'	=>0,//==0 no debuging, 1 errors on response debuging, 2 full debug all CURL
		);
		foreach($this->c_data as $k=>$v) if (isset($data[$k])) $this->c_data->$k = $data[$k];
		$this->c_data = (object)$this->c_data;
	}

	/*
		Clears Cache on all zones, 
		-$links can be array (list of links to clear), string (a single link) or string with value 'all' to clear all cache
		-Example 1: $this->clear_cache(array('http://www.example.com/css/styles.css','http://www.example.com/index.html'));
		-Example 2: $this->clear_cache('http://www.example.com/index.html');
		-Example 3: $this->clear_cache('all');

		-$links must have max length 30 
	*/
	public function clear_cache($links = false)
	{
		$ret = false;
		if (empty($links)) return $ret;
		if (!is_array($links))	$data =($links=='all')?array('purge_everything'=>true):array('files'=>array($links));
		else $data = array('files'=>$links);
		if ($zones = $this->get_zones()) 
		{		
			$ret = array();
			foreach($zones as $zone)
			{
				$url = array
				(			
					'zones/',
					$zone['id'],
					'/purge_cache'
				);		
				$url = implode("",$url);
				$call_data = $this->do_call($url,$data,'DELETE');
				if(!empty($call_data)) $ret[] = array
				(
					'zone_id'	=>$zone['id'],
					'data'		=>$call_data
				);
			}
		}
		if (empty($ret)) $ret=false;
		return $ret;
	}

	//==Gets Dev mode on all Zones
	public function get_dev_mode()
	{
		if ($zones = $this->get_zones()) 
		{		
			$ret = array();
			foreach($zones as $zone)
			{
				$url = array
				(			
					'zones/',
					$zone['id'],
					'/settings/development_mode'
				);		
				$url = implode("",$url);
				$ret[] = array
				(
					'zone_id'	=>$zone['id'],
					'data'		=>$this->do_call($url)
				);
			}
			return $ret;
		}
	}

	//==Sets Dev mode on all Zones, $mode can  be: 'on','off' ; true,false ; 1,0
	public function set_dev_mode($mode)
	{
		if ($zones = $this->get_zones()) 
		{
			if ($mode =='off') $mode = false;
			$mode = (!empty($mode))?'on':'off';
			$ret = array();
			foreach($zones as $zone)
			{
				$url = array
				(			
					'zones/',
					$zone['id'],
					'/settings/development_mode'
				);		
				$url = implode("",$url);
				$ret[] = array
				(
					'zone_id' => $zone['id'],
					'data'=> $this->do_call($url,array('value'=>$mode),'PATCH')
				);
			}
			return $ret;
		}
		return false;
	}
	
	//==Gets all zones and caches it into a runtime var, if 'force' is true then no cacheing
	public function get_zones($force = false)
	{
		if (!empty($force)) $this->cf_zones = false;
		if (!empty($this->cf_zones)) return $this->cf_zones;
		$url = array
		(			
			'zones?name=',
			$this->c_data->domain,
			'&status=active',
			'&page=1',
			'&per_page=20',
			'&order=status',
			'&direction=desc',
			'&match=all'
		);		
		$url = implode("",$url);
		$this->cf_zones = $this->do_call($url);
		return $this->cf_zones;
	}

	//==Call function (CURL WRAPER)
	public function do_call($url="",$data=array(),$method=false)
	{	
		$call_data = array
		(
			'url'				=>$url,
			'data'			=>$data,
			'method'		=>$method,
		);	
		$data = $this->curl_call($call_data['url'],$call_data['data'],$call_data['method']);
		$data['call_data'] = $call_data;
		$this->last_error = (!empty($data['result']['errors']))?$data['result']['errors']:'';
		
		if (!empty(	$this->c_data->debug_mode) &&(function_exists('debug')))
		{
			if ($this->c_data->debug_mode ==1)
			{
				if (!empty($this->last_error))
				{
					debug('ERRORS ON CALL: '.$call_data['url']);
					debug($this->last_error);
				}
			}
			else debug($data);
		}
		return (!empty($data['result']['result']))?$data['result']['result']:false;
	}

	//==CURL Comunication with api
	public function curl_call($cmd='', $data = array() , $method =null , $headers = null)
	{	
		if (empty($headers))	$headers = array
		(
			'X-Auth-Key: '.$this->c_data->api_key,
			'X-Auth-Email: '.$this->c_data->email,
			'Content-type: application/json',
		);
		if (empty($method)) $method =(!empty($data))?"POST":"GET";
		$url = $this->c_data->api_url;
		if (!empty($cmd)) $url = (strpos($cmd,"http")!==false)?$cmd:$url.$cmd;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,				$url);
		curl_setopt($ch, CURLOPT_HEADER,			FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,	FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,	10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,	TRUE);
		curl_setopt($ch, CURLOPT_ENCODING,			'');
		$safe_mode = @ini_get('safe_mode');
		$open_basedir = @ini_get('open_basedir');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		if (!empty($headers)) 	curl_setopt($ch, CURLOPT_HTTPHEADER,	$headers);
		if ($method !="GET")
		{			
			if (is_array($data) && count($data) > 0)
			{
				curl_setopt($ch, CURLOPT_POST,			TRUE);		
				curl_setopt($ch, CURLOPT_POSTFIELDS,	 json_encode($data));
			}
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,	$method);
		}
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if (json_decode($result, TRUE) !== NULL)
		{
			$result = json_decode($result, TRUE);
		}
		return array('result' => $result, 'info' => $info, 'headers' => $headers, 'data' => $data);
	}
}