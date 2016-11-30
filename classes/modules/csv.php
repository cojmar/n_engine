<?php
if (!class_exists('n_class')) {include ("n_class.php");}
class csv extends n_class
{
	public function __construct()	
	{
		$this->file_name = $this->cfg('path','export_data');
		$this->file_name .="_".strtotime('now');
	}
	public function csv($data)
	{	
		$this->csv_file = fopen($this->file_name,"w+");
		fputs($this->csv_file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		if (is_object($data)) $data = (array)$data;
		if (!is_array($data)) return false;
		$init_head = false;
		foreach ($data as $line)
		{
			if(!$init_head)
			{
				fputcsv($this->csv_file, array_keys($line));				
				$init_head = true;
			}
			if (is_array($line))
			{
				fputcsv($this->csv_file, $line);
			}
		}
		fclose($this->csv_file);
		$ret = file_get_contents($this->file_name);
		unlink($this->file_name);
		return $ret;
	}
}
