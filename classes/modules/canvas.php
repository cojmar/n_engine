<?php	
/*
	canvas - image manipulation
	Returns: result of function
	Examples:
					$this->canvas()->image([file_name / url / base64 html image string/ row_data ],x,y,new width, new height); 
					$this->canvas()->text("Hellow world",size, x , y ,#000000 , font path );
					$this->canvas()->render(mode);

					render modes: 
									true  -> outputs image
									empty	->	returns image object-> w:image width, h:image height, img: image data
									base64	-> returns base64 html image string	
									download	-> outputs image with downlonalod header, example: $this->canvas()->render('download','my_pic.png');
NOTE 1 - a ref to supported methods can be returned if a var is initialized with the result
EXAMPLE
					$bg	 =	$this->canvas()->image('bg.png'); 
					$logo = 	$this->canvas()->image('logo.png'); 
					$logo->x = 100;//==> changing logo X position, u can use: x , y , h, w if u use h and w the image will be scaled 
					$this->canvas()->render(1);

NOTE 2 - posible croping by manipulating canvas size:
$this->canvas()->set_size(100,100);
$bg = $this->canvas()->image('bg.png'); 
$bg->x = -100;
$this->canvas()->render(1);


	VERSION 1.5.2
*/
if (!class_exists('n_class')) {include ("n_class.php");}
class canvas extends n_class
{
	//####### Magic
	function __construct()	
	{
		$this->clear();
	}
	function __destruct()
	{
		$this->clear();
	}
	//####### Public
	public function clear()
	{
		if (!empty($this->canvas_data['images'])) foreach($this->canvas_data['images'] as $image)
		{
			imagedestroy($image->img);
		}
		$this->canvas_data = array('images'=>array());
		return $this;
	}

	public function set_size()
	{
		$this->canvas_data['size'] = $this->params(func_get_args(),array
		(
			"w"		=>0,
			"h"		=>0,
		));
		return $this;
	}
	public function text()	
	{
		$data = $this->params(func_get_args(),array
		(
			"text"		=>false,
			"size"		=>12,
			"x"			=>0,
			"y"			=>0,
			"color"		=>false,
			"font"		=>false,
			"angle"		=>0
		));
		if (empty($data['font']))
		{
			$data['font'] = "assets/default_canvas_font.ttf";
			if (!file_exists($data['font']))
			{
				$font_url = "https://cdn.jsdelivr.net/comic-sans-replacer/1.1/fonts/ComicNeue-Bold.ttf";
				$font = file_get_contents($font_url);
				file_put_contents($data['font'],$font);
			}
		}
		if (empty($data['color']))
		{
			$data['color'] = '#000000';
		}
		$size = $this->calculate_text_box($data['text'],$data['font'],$data['size'],$data['angle']);
		$ret = array
		(
			"x"		=>$data['x'],
			"y"		=>$data['y'],
			"w"		=>0,
			"h"		=>0,
			"_w"		=>0,
			"_h"		=>0
		);
		$ret['img'] = imagecreatetruecolor($size['w'], $size['h']);
		$trasparent_color = imagecolorallocatealpha($ret['img'], 0, 0, 0, 127);
		imagefill($ret['img'], 0, 0, $trasparent_color);
		imagecolortransparent($ret['img'], $trasparent_color);
		//START SAVE ALPHA
		imagealphablending($ret['img'], false);
		imagesavealpha($ret['img'], true);
		$color_data = $this->hex_color($data['color']);
		$color = ImageColorAllocate($ret['img'], $color_data['r'], $color_data['g'], $color_data['b']);
		imagettftext($ret['img'], $data['size'], $data['angle'], $size['x'], $size['y'], $color, $data['font'], $data['text']);
		return $this->register_img($ret);
	}

	public function color_fill()
	{
		$data = $this->params(func_get_args(),array
		(
			"color"	=> '#000000',
			"w"		=> 1,
			"h"		=> 1,
			"x"		=> 0,
			"y"		=> 0
		));	
		$data['img'] = imagecreatetruecolor($data['w'], $data['h']);
		if ($data['color'] !='transparent')
		{
			$my_color = $this->hex_color($data['color']);
			$color = imagecolorallocate($data['img'], $my_color['r'], $my_color['g'], $my_color['b']);
		}
		else
		{
			$color = imagecolorallocatealpha($data['img'], 0, 0, 0, 127);
		}
		imagefill($data['img'], 0, 0, $color);
		$this->register_img($data);
		return $this;
	}

	public function image()
	{
		$data = $this->params(func_get_args(),array
		(
			"src"		=>	false,
			"x"		=>0,
			"y"		=>0,
			"w"		=>0,
			"h"		=>0,
			"_w"		=>0,
			"_h"		=>0,
			"img"	=>NULL
		));

		if (empty($data['img']))
		{
			if (empty($data['src'])) return false;
			//==Try to get file data into img_data if empty then init img_data with src
			$img_data = @file_get_contents($data['src']);
			if (empty($img_data)) $img_data = $data['src'];
			if (strpos($img_data,"base64,"))
			{
				$img_data = explode("base64,",$img_data);
				array_shift($img_data);
				$img_data = implode("",$img_data);
				$img_data = base64_decode($img_data);
			}
			$data['img'] = @imagecreatefromstring($img_data);
		}
		if (empty($data['img'])) return false;
		unset($data['src']);
		return $this->register_img($data);
	}

	public function render()
	{
		ini_set("memory_limit","1512M");
		set_time_limit(0);
		$params = (object)$this->params(func_get_args(),array
		(
			"output"=>	false,
			"file_name"=>date("Y-m-d_H-i-s").'.png' //== needed for download output, 
		));
		//==Size not inted and i have at least 1 image init size with image size
		if (empty($this->canvas_data['size']) && !empty($this->canvas_data['images'][0]))
		{
			$this->set_size($this->canvas_data['images'][0]);
		}
		if (empty($this->canvas_data['size'])) return false;
		//==All data ok create $out image;
		$out = imagecreatetruecolor($this->canvas_data['size']['w'], $this->canvas_data['size']['h']);
		imagecolortransparent($out, imagecolorallocatealpha($out, 0, 0, 0, 127));
		//START SAVE ALPHA
		imagealphablending($out, false);
		imagesavealpha($out, true);
		//END SAVE ALPHA
		//==Add Images
		if (!empty($this->canvas_data['images'])) foreach ($this->canvas_data['images'] as $image)
		{
			imagecopyresampled($out, $image->img, $image->x, $image->y, 0, 0, $image->w, $image->h, $image->_w, $image->_h);
			imagealphablending($out, true);
			imagesavealpha($out, true);
		}
		$tmp_name=$this->cfg('path','tmp').strtotime('now').uniqid().rand(1,100).'.png';
		imagepng($out,$tmp_name,9); 
		imagedestroy($out);
		$ret = file_get_contents($tmp_name);
		while(file_exists($tmp_name))
		{
			@unlink($tmp_name);
		}
		//==Handle output

		//==Empty -> render to output to var
		if (empty($params->output))
		{
			$r = $this->canvas_data['size'];
			$r['img'] = $ret;
			$ret = $r;
		}
		//==true -> render to screen
		else switch ($params->output)
		{
			case 1:
				header ('Content-Type: image/png');
				die($ret);
			break;
			//==Defult output to file name
			default:				
				$ret = file_put_contents($params->output,$ret);
			break;
			//==base64 return base64 html png
			case 'base64':
				$ret = "data:image/png;base64,".base64_encode($ret);
			break;
			case 'download':
				header("Content-Disposition: attachment; filename=\"".$params->file_name."\"");
				header ('Content-Type: image/png');
				die($ret);				
			break;
		}
		return $ret;
	}
	//####### Private
	private function calculate_text_box($text,$fontFile,$fontSize,$fontAngle) 
	{
		$rect = imagettfbbox($fontSize,$fontAngle,$fontFile,$text); 
		$minX = min(array($rect[0],$rect[2],$rect[4],$rect[6])); 
		$maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6])); 
		$minY = min(array($rect[1],$rect[3],$rect[5],$rect[7])); 
		$maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7])); 
		
		return array
		(
			 "x"		=> abs($minX) - 1, 
			 "y"		=> abs($minY) - 1, 
			 "w"		=> $maxX - $minX, 
			 "h"		=> $maxY - $minY, 
			 "box"	=> $rect 
		);
	}
	private function hex_color($color)
	{
		$color =	str_replace('#','',$color);
		$ret = array
		(
			'r' =>	hexdec(substr($color,0,2)),
			'g' =>	hexdec(substr($color,2,2)),
			'b'=>		hexdec(substr($color,4,2))
		);
		return $ret;
	}
	private function register_img($data)
	{
		$data['_w'] = imagesx($data['img']);
		$data['_h'] = imagesy($data['img']);
		if (empty($data['w'])) $data['w'] = $data['_w'];
		if (empty($data['h'])) $data['h'] = $data['_h'];
		return $this->canvas_data['images'][] = (object)$data;
	}

}