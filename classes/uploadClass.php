<?php
/************************************************************************************
 *Upload Class takes a file name, a file type, and a folder address.
 *It will take the uploaded file and move it to the folder path.

 *It contains the following method(s):
	* isUploaded 
 ***********************************************************************************/
class Upload
{	
	private $fileName; 			//  name of file from upload form  
	private $fileTypes=array();  //  array of valid file types for upload  
	private $folderPath;  			//  path of folder where uploaded files will be moved
	
	public function __construct($fileName, $fileTypes, $folderPath){
		$this->fileName=$fileName;
		$this->fileTypes=$fileTypes;
		$this->folderPath=$folderPath;
	}//end constructor
	
	
	/* Method checks to see that there is a file and handles
	  moving the file to a permenant location */
	public function isUploaded($RID=""){
		$fileMessage=array();
		
		//check if $_FILE[‘file’][‘name’] is set and there are no errors
		if($_FILES[$this->fileName]['name']) {					
			if($_FILES[$this->fileName]['error']) {
				switch($_FILES[$this->fileName]['error']){
					case 1: $fileMessage['msg']='File exceeds PHP\'s maximum upload size';
							return $fileMessage;
					case 2: $fileMessage['msg']='File exceeds the maximum upload size<br />';
							return $fileMessage;
					case 3: $fileMessage['msg']='File only partially uploaded<br />';
							return $fileMessage;
					case 4: $fileMessage['msg']='No file selected<br />';
							return $fileMessage;
				}//end switch
			}//end fileError 	
			
			//check if the file type is valid
			$type=$_FILES[$this->fileName]['type'];
			$typeCount=count($this->fileTypes);
			$wrongType=0;
			foreach($this->fileTypes as $ftype) {
				if($type!=$ftype) {
					$wrongType++;
				}//end if
			}//end foreach
			
			//if the fileType is not found in the acceptable types array, return error message
			if($wrongType==$typeCount){
				$fileMessage['msg']='Error: Unsupported File Type';
				return $fileMessage;
			}
			
			//check if the file reaches the server in the temporary location
			if(@is_uploaded_file($_FILES[$this->fileName]['tmp_name'])) {
				$fileName = $_FILES[$this->fileName]['name'];
				//$fileName=strtolower($fileName);
				$filePath = $this->folderPath ? $this->folderPath."/".$fileName : $fileName;
				
				/*Checks if a record ID exists. If it does, file renamed to the record ID, and old file removed */
				if($RID!=""){//RID only used by publish play functionality
					$newName=$RID.'.xml';
					@unlink($filePath); //unlink old path
					$filePath = $this->folderPath ? $this->folderPath."/".$newName : $newName;
				}
				
				//Move file from temp location to destination folder
				if(move_uploaded_file($_FILES[$this->fileName]['tmp_name'],$filePath)){
					//Check if file reached destination folder
					if(file_exists($filePath)){
						@chmod ($filePath, 0777);
						$fileMessage['msg']==NULL;
						$fileMessage['filePath']=$filePath;
						//echo 'File '.$_FILES[$this->fileName]['name']. ' uploaded successfully';
						return $fileMessage;	//return the full filename (in case it was changed)
					}else{
						$fileMessage['msg']='File failed to reach destination folder';
						return $fileMessage;
					}
				}else{
						$fileMessage['msg']='Error in moving file to specified location';
						return $fileMessage;
				}	
			}else{
				$fileMessage['msg']='File did not reach tempory location on server';
				return $fileMessage;
			}
		}else{
			$fileMessage['msg']='File name not available';
			return $fileMessage;
		}
	}//end inUploaded Method
}//end upload class
?>