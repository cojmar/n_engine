<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class curl extends n_class
{
	public function __construct()	{}
	public function curl($url=NULL,$fields=NULL,$headers = array("Content-type: application/x-www-form-urlencoded; charset=UTF-8"))
	{
		if (is_null($url)) return NULL;
		if (!is_array($fields)) $fields = array();
		$result = false;
		$fields_string = '';

		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		if (count($fields))
		{
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);				
		//execute call
		$result = curl_exec($ch);
		//print $head = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		//close connection
		curl_close($ch);
		return $result;
	}		
}