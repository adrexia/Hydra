<?php
/*****************************************************************************
 *Class to resize images
 *Methods include:
	*	resize()
	*	resizeJpeg($newW, $newH, $origW, $origH, $fullPath)
	*	resizeGif($newW, $newH, $origW, $origH, $fullPath)
	*	resizePng($newW, $newH, $origW, $origH, $fullPath)
 ***************************************************************************/
class ResizeImage
{
	private $imageName;  		//name of file as returned from upload class  
	private $dimension; 			//desired dimension of longer side   
	private $destFolder;			//path of folder where resized images will be saved
	private $prefix;				//prefix that will be attached to the name of the resized image

	public function __construct($imageName, $dimension, $destFolder, $prefix=''){
		$this->imageName=$imageName;
		$this->dimension=$dimension;
		$this->destFolder=$destFolder;
		$this->prefix=$prefix;
	}
	
	/*Method to handle resizing of images*/
	public function resize(){
		
		if(!file_exists($this->imageName)){//checks if file exists on server
			echo 'RESIZE ERROR: Source file not found';
			return false;
		}
		
		//calculate image aspect ratio and get new width and height
		$imageInfo = getimagesize($this->imageName);
		
		$origW = $imageInfo[0]; //original width
		$origH = $imageInfo[1]; //original height
		if($origH>$origW){
			$newH = $this->dimension;
			$newW = ($origW * $newH) / $origH;
		}else{
			$newW = $this->dimension;
			$newH = ($origH * $newW) / $origW;
		}
		/*checks if destination folder for thumbs exists and creates
		it if not. Sets full file path based on destination folder*/
		$fileName = basename($this->imageName);
		$dFolder = $this->destFolder ? $this->destFolder.'/' : '';
		$dFile = $this->prefix ? $this->prefix.'_'.$fileName:$fileName;
		$fullPath = $dFolder.''.$dFile;
		
		//checks if file mime type is jpeg calls corresponding resize function
		if($imageInfo['mime']=='image/jpeg'){
			if ($this->resizeJpeg($newW, $newH, $origW, $origH, $fullPath)) {
				//returns full path when resizing has been successful, or false otherwise
				return $fullPath;
			}else{
				return false;
			}
		}//end jpg
		
		//checks if file mime type is gif calls corresponding resize function
		if($imageInfo['mime']=='image/gif'){
			if($this->resizeGif($newW, $newH, $origW, $origH, $fullPath)){
				//returns full path when resizing has been successful, or false otherwise
				return $fullPath;
			}else{
				return false;
			}
		}//end gif
		
		//checks if file mime type is png calls corresponding resize function
		if($imageInfo['mime']=='image/png'){
			if ($this->resizePng($newW, $newH, $origW, $origH, $fullPath)) {
				//returns full path when resizing has been successful, or false otherwise
				return $fullPath;
			}else{
				return false;
			}
		}//end png
		
	}//end resize
	
	/********************************************************************************************************
	The resize JPG Method takes a jpg, resizes it, and returns a true/false value
	********************************************************************************************************/
	private function resizeJpeg($newW, $newH, $origW, $origH, $fullPath){
		$im = ImageCreateTrueColor($newW, $newH);
		$baseImage = ImageCreateFromJpeg($this->imageName);
		imagecopyresampled($im, $baseImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
		imagejpeg($im, $fullPath);
		imagedestroy($im);
		if(file_exists($fullPath)){ //if successfully resized and saved
			return true;
		}else{
			echo 'ERROR: Unable to resize image '.$fullPath.'<br />'; //Error message
			return false;
		}
	}//end resizeJpeg
	
	/**************************************************************************************************************
	The resize Gif method takes a gif image, resizes it, and returns a true/false value	
	**************************************************************************************************************/
	private function resizeGif($newW, $newH, $origW, $origH, $fullPath){
		$im = ImageCreateTrueColor($newW, $newH);
		$baseImage = ImageCreateFromGif($this->imageName);
		imagecopyresampled($im, $baseImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
		imagejpeg($im, $fullPath);
		imagedestroy($im);
		if (file_exists($fullPath)){ //if successfully resized and saved
			return true;
		}	
		else {
			echo 'ERROR: Unable to resize image '.$fullPath.'<br />';
			return false;
		}
	}//end resize gif	
	
	/**************************************************************************************************************
	The resize Png method takes a png image, resizes it, and returns a true/false value
	Note: This currently does not support transpanrency in png images
	**************************************************************************************************************/
	private function resizePNG($newW, $newH, $origW, $origH, $fullPath){
		$im = ImageCreateTrueColor($newW, $newH);
		$baseImage = ImageCreateFromPng($this->imageName);
		imagecopyresampled($im, $baseImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
		imagejpeg($im, $fullPath);
		imagedestroy($im);
		if (file_exists($fullPath)){ //if successfully resized and saved
			return true;
		}	
		else {
			echo 'ERROR: Unable to resize image '.$fullPath.'<br />';
			return false;
		}
	}//end resize png	
	
}//end resize class
?>