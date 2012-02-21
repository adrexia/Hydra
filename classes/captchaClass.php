<?php
/******************************************************************************************************************
**	This class handles captcha creation by generating random text and
**	checking that words match. It requires a folder in the same
**	directory as the parent file (index.php) called "captchas", and the
**	00TT.TTF font in the fonts directory
****************************************************************************************************************/
class Captcha
{

	/*********************************************************************************************
	 * The main funcrion called by outside classes. Is used to create
	 * an image containing 1 set of randomly generated characters 
	 * and return the location of the image file created
	*********************************************************************************************/
	public function create(){		
		$string=$this->getWords();
		$numchars=strlen($string);	
		$width=$numchars*20;
		$width1=$width+4;
		$height=60;
		$font='fonts/HelveticaNeueLTCom-Lt.ttf';
		$location='captchas/'.uniqid('captcha_').'.jpg';
		$img=imageCreateTrueColor($width1, $height);
		$white=imageColorAllocate($img,255,255,255);
		$black=imageColorAllocate($img,0,0,0);
		imagefill($img, 0, 0, $white);
		imagerectangle($img, 0, 0, $width-1, $height-1, $black);
		$maxfontsize=round($width / $numchars);
		$startx=round($maxfontsize*0.5);
		$maxxoffs=round($maxfontsize*0.9);			
		for ($i=0;$i<$numchars;$i++){
			$ypos=($height/2)+rand(5, 20);
			$fontsize=round(rand(12,$maxfontsize));
			$angleor=round(rand(0,1));
			$angle=round(rand(0,30));
			if ($angleor == 1){
				$angle = (-1)*$angle;
			}
			imagettftext($img, $fontsize, $angle, $startx + $i * $maxxoffs, $ypos, $black, $font, substr($string,$i,1));
		}	
		imagejpeg($img, $location, 70);
		flush();
		imagedestroy($img);
		$_SESSION['captcha']=$string;
		return $location;
	}//end create method
	
	/**********************************************************************************
	 * Match is the method used by outside classes to test
	 * whether the generated captcha matches the text the user entered
	*********************************************************************************/
	public function match($value){								
		if (empty($value)) {
			return false;
		}
		return strtolower($value) == $_SESSION['captcha'];
	}//end match function
	
	
	/**********************************************************************************************
	* Method randomly generates a list of characters
	* that will be sed by the create Method to generate the
	* captcha shown to the user, and matched later with the match method
	*************************************************************************************************/
	private function getWords(){							
		$chars = 'abcdefghijklmnopqrstuvwxyz123456789'; //characters to use in image generation
		$numchars = strlen($chars); 
		$wordlength = rand(4,7);
		$word = '';
		for ($i=0;$i<$wordlength;$i++){
			$pos=rand(0, $numchars);
			$word.=substr($chars, $pos, 1);
		}
		return $word; 
	}//end getWords	
	
}//end captcha Class
?>