<?php
include 'classes/db/dbClass.php';
include 'classes/uploadClass.php';
include 'classes/resizeImageClass.php';
/*************************************************************************************************************
 *The Model class contains all the rules and processes that interact with the main database class
 *that do not result in the generation of html. For the database class that deals with html generation
 *see the "generate" class.
 
 *The methods contained in this class are:
	Admin		public function reportJob($type)
				public function deleteComment()
				public function deleteUser($userID)
				private function deleteUserFolder($directory, $empty = false)
	Login		public function checkUserSession()
				public function validateUser()   
	Validation	public function validatePost()
				private function validateProfile()
				private function validateCreateAccount()
				private function validateUpdateAccount()
				private function validateGame()
				private function validateContact()
				private function validateComment()
				private function checkErrorMessages($result)
	Processing	public function processMail()
				public function processComment($commentID='')    
				public function processGame()  
				public function processCharacters()
				public function processUpdatePageContent($page)
				public function processNews()
				public function processProfile()
				private function processProfilePic()
				public function processUserGames($userID=0)
	Upload		private function uploadAndResizeImage($userName)
				private function makeUserFolders()	
	TODO:		public function addPrice()//empty method	
 ***********************************************************************************************************/
class Model extends DataBase{   
	private $validate;
	
	public function __construct(){
		parent::__construct();//use constructor from dataBase Class 
      $form=array('submitGame','register','contact','game'); //pages with forms		
      //if the page needs form processing, include the validate class, and instantiate it
		if($_GET['action']=='edit'||$_GET['action']=='add'||in_array($_GET['pageName'], $form)){
         include_once 'classes/validateClass.php';
			include_once 'classes/captchaClass.php';
         $this->validate=new Validate();       
      }//end if		
	}//end constructor
   
   
   /********************************************************************************************
	* Makes an entry in the job table for the type when a
	* user clicks report button. Type is either profile or comment
	* depending on what the report button is connected to
	*******************************************************************************************/
	public function reportJob($type){
		if($type=="profile"){
			$reportID=$_GET['userID'];
		}elseif($type=="comment"){
			$reportID=$_GET['commentID'];			
		}	
		$result=$this->putReport($reportID);
		return $result;		
	}//end reportJob   
   
   
   /***********************************************************************************************************
    *Method tells dbclass to remove comment and unsets the unneeded get arrays
   **********************************************************************************************************/
   public function deleteComment(){
		$commentID=$_GET['commentID'];
		$result=$this->removeComment($commentID);
      unset($_GET['edit']);
      unset($_GET['commentID']);
		return $result;		
	}//deleteComment
	
	/******************************************************************************	 *
	 * Method runs all the methods which need to be run to
	 * purge a user from the website
	 *****************************************************************************/
	public function deleteUser($userID){		
		$userName=$this->getUserFromID($userID);
		$userName=strtolower($userName);
		$result=$this->removeUser($userID);
		$dir='users/'.$userName.'/';
		$folder=$this->deleteUserFolder($dir); //delete users folder and files
	   unset($_GET['action']);
      $_GET['pageName']='logout'; //log the user out
		$_GET['history']='register';
		$result=$this->checkUserSession();
		return $result;		
	}//deleteUser
	
	/**************************************************************************************************************
	 * Method deletes all the files and the folder of a user.
	 * Used when a user deletes their account
	 *************************************************************************************************************/
	private function deleteUserFolder($directory, $empty = false){   
      $openDirectory=opendir($directory);
      while($contents=readdir($openDirectory)){ //delete everything in the directory
         if($contents!='.'&&$contents!='..'){
            $path=$directory . "/" . $contents;
            unlink($path);           
         }
      }       
      closedir($openDirectory); //close the direcotry       
      if(!rmdir($directory)) {
            return false;
      }       
      return true;
   }//end deleteUserFolder
	
	/****************************************************************************************************
    * Method runs when a user logs in or out.
    * It will either run the logOut Method,
    * or check the user and establish session variables if the user is logging in
   ******************************************************************************************************/
	public function checkUserSession(){		
		if($_GET['pageName']=='logout'){ //if pageName is logout
        $result=$this->logOut();
		}      
		if(isset($_POST['login'])){//if form has been submitted: validate
         $result=$this->validateUser();
			if($result['message']=="Success"){
				if($_GET['history']){
					$pageName=$_GET['history'];
					if($_GET['userID']){
						$pageName=$pageName.'&amp;userID='.$_GET['userID'];				
					}elseif($_GET['gameID']){
						$pageName=$pageName.'&amp;gameID='.$_GET['gameID'];		
					}
				}else{
					$pageName=$_POST['history'];
				}
				 header('Location: index.php?pageName='.$pageName);
			}		   		
		}    
		return $result['message'];
	}//end checkUserSession
	
	/***********************************************************************************
	 *Helper Method to log users out. It will kill the current session
	 *and send a user back to the page they were on when they
	 *chose to logout
	 *********************************************************************************/
	private function logOut(){
		unset($_SESSION['userName']); //kill session variable userName
		unset($_SESSION['userID']); //kill session variable userID
		unset($_SESSION['userType']); //kill session variable userType
		$result['logout']=true;
		$pageName=$_GET['history']; //to send user back to where they were when they logged out
		if(strlen($pageName)<1){
			$pageName="news";
		}
		if($_GET['userID']){
			$pageName=$pageName.'&amp;userID='.$_GET['userID'];				
		}elseif($_GET['gameID']){
			$pageName=$pageName.'&amp;gameID='.$_GET['gameID'];		
		}
		header('Location: index.php?pageName='.$pageName);
		return $result;
	}//end logOut  
  
   /**********************************************************************************************************
    *Method calls another method that checks the supplied username/password
    *combination with details in the database.
    *If correct: runs methods to set-up season.
    *If incorrect or field missing: produces error message
   ***********************************************************************************************************/
	public function validateUser(){     
		if($_POST['userName']&&$_POST['userPassword']){
        	$user=$this->getUserSession();
		  	if(is_array($user)){ //if $user exists then set session variables
         	$_SESSION['userName']=$user['userName']; 
            $_SESSION['userID']=$user['userID'];
            $_SESSION['userType']=$user['userType']; //su, mod, user
         	$result['message']="Success";
         	$result['ok']=true;
         	return $result;
      	}else{
           $result['message'] = "You have entered an incorrect user name or password";
      	}
   	}else{
        $result['message'] = "Please fill in both fields";
      }	
      $result['ok'] = false;
   	return $result;
	}//end validateUser
   
   /**************************************************************************************************************
    * Method runs validation for forms that need validation and returns a message
    * It acquires data from the post array 
   *************************************************************************************************************/
	public function validatePost(){
      $pageName=$_GET['pageName'];   
      switch($pageName){       
         case 'profile':
            $result['msg']=$this->validateProfile();           
            break;
         case 'register':
				if($_POST['userID']==0){
					$result['msg']=$this->validateCreateAccount();
				}else{
					$result['msg']=$this->validateUpdateAccount();
				}
            break;
         case 'submitGame':            
				if(!$_POST['characters']){				
					$result['msg']=$this->validateGame();
				}		
            break;
         case 'contact':
            $result['msg']=$this->validateContact();   
            break;     
         case 'about':
				extract($_POST);
				if($this->validate->checkRequired($pageContent)){
					$result['msg']=$this->validate->checkRequired($pageContent).' Content';
				}
            break;
			case 'details':
				extract($_POST);
				if($this->validate->checkRequired($pageContent)){
					$result['msg']='You are trying to post an empty form';
				}
            break;
			case 'news':
				extract($_POST);
				if($this->validate->checkRequired($newsTitle)){
					$result['msg']='Your news posts must have a Title.<br />';
				}
				if($this->validate->checkRequired($newsText)){
					$result['msg'].='You are trying to post an empty news post!';
				}			
            break; 
         case 'game':
            $result['msg']=$this->validateComment();            
            break; 
      }//end switch
   	$result=$this->checkErrorMessages($result);
     	return $result;
	}//end validatePost   
   
   /**********************************************************************************
    * Method runs the profile post array through validation
   **********************************************************************************/
   private function validateProfile(){
      extract($_POST); 
      $oldData=$this->getUserDetails($_SESSION['userID']);
      if($oldData&&strlen($userEmail<3)){
         $userEmail=$oldData['userEmail'];
         $_POST['userEmail']=$oldData['userEmail'];              
      }
      $result['msg']=$this->validate->checkRepeat($userPassword, $userPasswordRepeat);
		if(!strlen($result['msg'])>0){
         $result['msg'].=$this->validate->checkEmail($userEmail);
      }
		if($userFullName==""||strlen($userFullName)<2){
			echo $oldData['userFullName'];
			$_POST['userFullName']=$oldData['userFullName'];
		}		
		$result['msg'].=$this->validate->checkName($_POST['userFullName']);			
      unset($_GET['edit']);
      return $result['msg'];
   }//end validate Profile   
   
   /**********************************************************************************
    * Method runs the register post array through validation for new users
   **********************************************************************************/
   private function validateCreateAccount(){
      extract($_POST);		
		if($this->validate->checkRequired($userName)){  
         $result['msg'].=$this->validate->checkRequired($userName).'<span class="unem">User Name</span> <br />';
      }
		if($this->validate->checkRequired($userFullName)){  
         $result['msg'].=$this->validate->checkRequired($userFullName).'<span class="unem">Full Name</span> <br />';
      }
      if($this->validate->checkRequired($userEmail)){
         $result['msg'].=$this->validate->checkRequired($userEmail).'<span class="unem">Email</span> <br />'; 
      }
      if($this->validate->checkRequired($userPassword)){            
         $result['msg'].=$this->validate->checkRequired($userPassword).'<span class="unem">Password</span> <br />';
      }		
		if($this->validate->checkRepeat($userPassword, $userPassword2)){            
         $result['msg'].=$this->validate->checkRepeat($userPassword, $userPassword2).'<br />';
      }	
		 
      if($this->getUserName($userName)){
         $result['msg'].='<span class="unem">* Sorry, that User Name is taken, please try another</span> <br />';                
      }
      $result['msg'].=$this->validate->checkEmail($userEmail);
      $result['msg'].=$this->validate->userName($userName);				
		return $result['msg'];		
   }//end validateCreateAccount
	
	 /**********************************************************************************
    * Method runs the register post array through validation for existing
    * users
   **********************************************************************************/
   private function validateUpdateAccount(){
      extract($_POST);
      $oldData=$this->getUserDetails($_SESSION['userID']);		
      if($oldData&&strlen($userEmail<3)){
         $userEmail=$oldData['userEmail'];
         $_POST['userEmail']=$oldData['userEmail'];              
      }	
		if($this->validate->checkRepeat($userPassword, $userPassword2)){            
         $result['msg'].=$this->validate->checkRepeat($userPassword, $userPassword2).'<br />';
      }		
      if(strlen($userFullName)>0){
			$result['msg'].=$this->validate->checkName($userFullName);			
		}	
      $result['msg'].=$this->validate->checkEmail($userEmail);
      return $result['msg'];
   }//end validateUpdateAccount
	
   /**********************************************************************************
    * Method runs the submit game post array through validation
   **********************************************************************************/
   private function validateGame(){
      extract($_POST);      
      if($this->validate->checkRequired($gameName)){
         $result['msg'].=$this->validate->checkRequired($gameName).'<span class="unem">Title</span> <br />'; 
      }
		if($this->validate->checkRequired($gameDescription)){
         $result['msg'].=$this->validate->checkRequired($gameDescription).'<span class="unem">Description</span> <br />'; 
      }
		if($this->validate->checkRequired($gameMaxPlayers)){
         $result['msg'].=$this->validate->checkRequired($gameMaxPlayers).'<span class="unem">Number of Players</span> <br />'; 
      }					
		if($this->validate->checkNumbers($gameMaxPlayers)){
			$result['msg'].=$this->validate->checkNumbers($gameMaxPlayers).'Number of Players <br />';
		}
      return $result['msg'];
   }//end validateGame   
   
   /**********************************************************************************
    * Method runs the contact post array through validation
   **********************************************************************************/
   private function validateContact(){
      extract($_POST); 
      $result['msg']=$this->validate->checkName($name);
      $result['msg'].=$this->validate->checkEmail($email);
      if($this->validate->checkRequired($name)){  
         $result['msg'].=$this->validate->checkRequired($name).'<span class="unem">Your Name</span> <br />'; 
      }
      if($this->validate->checkRequired($email)){  
         $result['msg'].=$this->validate->checkRequired($email).'<span class="unem">Email</span> <br />'; 
      }
      if($this->validate->checkRequired($subject)){  
         $result['msg'].=$this->validate->checkRequired($subject).'<span class="unem">Subject</span> <br />'; 
      }
      if($this->validate->checkRequired($message)){  
         $result['msg'].=$this->validate->checkRequired($message).'<span class="unem">Message</span> <br />'; 
      }
      return $result['msg'];
   }//end validate Contact
   
   
   /**********************************************************************************
    * Method runs the comment post array through validation
   **********************************************************************************/
   private function validateComment(){
      extract($_POST); 
      if($commentTitle==''){
         $_POST['commentTitle']="Untitled";
      }
      if($this->validate->checkRequired($commentText)){
         $result['msg']=$this->validate->checkRequired($commentText).'<span class="unem">Comment Text</span> <br />';
      }else{
         $result['msg']=false; 
      }
      return $result['msg'];
   }//end validate Comment   
   
   
   /************************************************************************************
    *Method checks for error messages. If it finds them it returns
    *the messages, otherwise the content will be processed
   *************************************************************************************/
   private function checkErrorMessages($result){
		if(is_array($result)){
			foreach($result as $errMessage) {
				if (strlen($errMessage) > 0) {
					$result['ok'] = false;
					return $result;
				}
			}//end foreach
		}
		$result['ok'] = true;
		return $result;
	}//end checkErrorMessag
   
   
   /*****************************************************************************************
    *Method sends an email to the owner of the website, and a
    *confirmation to the person sending the email
    ***************************************************************************************/
   public function processMail(){
      $vResult = $this->validatePost();      
      if(!$vResult['ok']){//if validation fails, return error message
			return $vResult;	
		}else{
         extract($_POST); //$name, $userName, $email, $subject, $message
         $to='adrexia@gmail.com'; //change this
         //replaces line breaks in textareas
         $message= str_replace("\r", ", ", $message);
         $message= str_replace("\n", "", $message);
         $senderDetails='From:'.$name."\n User?".$userName;
         $content=$senderDetails."\n\r".$message;     
			$subject=stripslashes($subject);    
			$content=stripslashes($content);    
         mail($to,$subject,$content,"From: $email");//mail to admin
         mail($email,"Re: $subject","Thank you for contacting hydra. If neccessary, we will be in touch shortly", "From: $to");//mail to sender
         $result['msg']="Success";
         return $result;
      }
   }//end processMail
   
   /*****************************************************************************************
    *Method sends an email to the owner of the website. For safety
    ***************************************************************************************/
   private function emailReg($userID){
         extract($_POST); //
         $to='adrexia@gmail.com'; //change this
         $subject="Game choice back-up: $userName";
         //replaces line breaks in textareas
         $userName=$this->getUserFromID($userID);
         $message=$userName." \r\n".implode(",",$_POST); 
         mail($to,$subject,$message);//mail to admin
         $result['msg']="Success";
         return $result;
   }//end processMail
   
   
   
	   
      
   /******************************************************************************
    *Method handles processing of user comments made on games
    *If the comment is being edited rather than added, it submits
    *the comemnt with a commentID
   *****************************************************************************/
	public function processComment($commentID=''){     
		$vResult=$this->validatePost();   
		if(!$vResult['ok']){//if validation fails, return error message
      	return $vResult;	
		}else{
         if($commentID==''){           
            $rResult=$this->putComment(); //comment written to db
         }else{
            $rResult=$this->putComment($commentID); //comment updated in db         
         }
         $RID=$rResult['RID'];
      	if(!$RID){
            return $rResult;
         }
		return $rResult;
      }	
	}//end processComment   
    
    
   /*********************************************************************************************************
    *Method handles the processing of submitted games and returns the result of
    *the processing and the game ID.
    *Because this method could be used to update an existing game, the method
    *checks to see if there is a non-0 gameID in the POST Array and runs it
    *through an alternate update process if this is true
   ********************************************************************************************************/
   public function processGame(){		  
      $vResult=$this->validatePost();
      if(!$vResult['ok']){//if validation fails, return error message
			return $vResult;		 
		}else{
			if($_POST['gameID']==0){//new Game
				$rResult=$this->putGame(); //content written to db
				$RID=$rResult['gameID'];         
				if($RID==""){
					return $rResult;
				}
         }else{//update game
				$rResult=$this->updateGame(); //content written to db
				$RID=$rResult['gameID'];         
				if($RID==""){
					return $rResult;
				}	
			}
         return $rResult; //return error msg or playID 
      }//end if/else
   }//end processPublishPlay
	
	/*********************************************************************************************************************
	 * Method Processes the characters and returns the results of the processing.
	 * Currently it is not necessary to valaidate the input, but it is run past the validator
	 * to make it easy to add this step later if the client would like to add some sort
	 * of processing
	*********************************************************************************************************************/
	public function processCharacters(){
		$vResult=$this->validatePost();//currently doesn't test anything, but might need to add validation if any other fields are added.
      if(!$vResult['ok']){//if validation fails, return error message
			return $vResult;		 
		}else{		
         $rResult=$this->putCharacters($_GET['gameID']); //content written to db
         $RID=$rResult['gameID'];         
      	if($RID==""){
            return $rResult;
         }         
         return $rResult; //return error msg or playID 
      }//end if/else	
	} //end processCharacters
   
   /*******************************************************************************************
    *Method handles the processing for content from a given page
   ********************************************************************************************/
   public function processUpdatePageContent($page){
		$vResult = $this->validatePost();      
		if(!$vResult['ok']){//if validation fails, return error message
			return $vResult;	
		}else{
      	$rResult=$this->putContent($page); //content written to db
         $RID=$rResult['RID'];
      	if(!$RID){
            return $rResult;
         }
         return $rResult;
      }	
	}//processUpdatePageContent
	
	/*******************************************************************************************
    *Method handles the processing for news posts.
    *Deals with the processing of the post array and returns a result
   ********************************************************************************************/
   public function processNews(){
		$vResult = $this->validatePost();      
		if(!$vResult['ok']){//if validation fails, return error message
			return $vResult;	
		}else{
			if($_POST['newsID']==0){				
				$rResult=$this->putNews(); //content written to db
				$RID=$rResult['RID'];
				if(!$RID){
					return $rResult;
				}
			}else{
				$rResult=$this->updateNews(); //content written to db
				$RID=$rResult['RID'];
				if(!$RID){
					return $rResult;
				}      	
			}
         return $rResult;
      }	
	}//processNews
   
   
    /*******************************************************************************************
    * Method processes the editing and creation of profiles/user accounts
    * check required fields for both forms...
		but if user already exists: fields optional...
		if exist then check if valid 
   *******************************************************************************************/
   public function processProfile(){
      $userID = $_POST['userID'];   //if new user $userID will be 0		
      $vResult=$this->validatePost();	
      if(!$vResult['ok']){//if validation fails, return error message
         return $vResult;
      }
      if($userID==0){//checks to see whether this is a new user or an update on the profile
         if($this->makeUserFolders()){ //if new user, make folders
            if($userPic==null){ 
               $file="images/user.png"; //if no picture given, assign default image
               $newfile='users/'.strtolower($_POST['userName']).'/user.png';
               if (!copy($file, $newfile)){
                  echo "failed to copy $file...\n";
               }
            }   
         }else{
            return "Failed to make folders for this user";
         }
      }      
      $rResult=$this->processProfilePic();      
      if($userID==0){ //checks to see whether this is a new user or an update on the profile
         $userID=$this->putProfile(); //create new user and save userID
			//start session
			$result=$this->checkUserSession();
			if($userID){
				if($this->checkInfoForUser($_SESSION['userID'])){
					$rResult=$this->updateInfo($userID);
				}else{
					$rResult=$this->putInfo($userID);
				}
				if($rResult){
						$rResult['msg']="Success";
						$userDetails=$this->validateUser();
				}
			}else{
				$rResult['msg']="Failure in Recording Registration Details. Please Try Again";
			} 
      }else{ //if user exists need to check if info exists
         $rResult['msg']=$this->updateProfile($_SESSION['userID']); //comment updated in db
			//if info exists need to run an  update rather than a put
			if($_GET['pageName']=='register'){
				if(!$this->checkInfoForUser($userID)){
					$rResult['msg']=$this->putInfo($userID);
				}else{					
					$rResult['msg']=$this->updateInfo($userID);				
				}			
			}
      }//end !userID      
    	return $rResult;   
   }//end processProfile   
   
   /*********************************************************************************************
    *Method handles processing of the user's profile picture
    *Pictures are stored in a seperate folder for each user so that it should
    *be easy to add the option of multiple files for users at a alter date
   ********************************************************************************************/
   private function processProfilePic(){
      if($_FILES['userPic']['name']){
			$userPic=$this->uploadAndResizeImage(strtolower($_POST['userName']));
         if($userPic['msg']!=null){			
            $rResult['msg']=$userPic['msg'];
            return $rResult;
			}else if($userPic['fileName']){				
             if($userPic['imageFail']!=null){					
               $rResult['msg'] = $userPic['imageFail'];
               return $rResult;
             }else{					
               $userPic=$userPic['fileName'];					
               $_POST['userPic'] = $userPic;
               $rResult['userPic'] = $userPic;
               $rResult['msg'] = 'Success';
            }              
			}else{
            $rResult['msg'] = 'Unable to upload/resize image';
            return $rResult;
         }//end $userPic msg check
      }else{
         $_POST['userPic']=$this->getUserPic($POST['userID']);
      }//end userpic upload   
      return $rResult;
   }//end processProfilePic
	
	/*******************************************************************************************************
	 *Method handles the processing of userGames and returns a success/failure
	 *message. If there is no userID given it sets the userID to the session user.
	 *This is so that admins and mods will be able to edit other users games
	 ***************************************************************************************************/
	public function processUserGames($userID=0){
		if($userID==0){
			$userID=$_SESSION['userID'];
		}	
		
		$this->emailReg($userID);
		
		$infoGameComments=$_POST['infoGameComments'];		
		if(strlen($infoGameComments)>0){
			$result=$this->updateInfoGameComments($infoGameComments, $userID);
		}		
		$gameSelections=array();		
		foreach($_POST as $key => $value){
			$test="".$key."";
			if($test!="infoGameComments"&&$test!="gameSelect"){
					array_push($gameSelections, $value);		
			}
		}		
		$userPrefBool=true;		
		$gameIDKeep=array();		
		
		for($i=0;$i<count($gameSelections);$i++){
			$gameID=$gameSelections[$i];
			array_push($gameIDKeep, $gameID);
			$userGamesCharPref=$gameSelections[++$i];///WHAT			
			if($userPrefBool){
				$userGamesPref=1;
				$userPrefBool=false;
			}else{
				$userGamesPref=2;
				$userPrefBool=true;
			}				
			$result=$this->putUserGames($userID, $gameID, $userGamesPref, $userGamesCharPref);			
		}
		$userGames=$this->getUserGames($userID);		

		
		//this is erroring! Should create an array of... gameIDs to keep
	//	foreach($userGames as $game){ //want the game ID of all current games					
			//What did this do? O_o
			/*foreach($gameSelections as $key => $value){
				$keyName=array_search($key, $game); //string array	
				if($keyName=="gameID"){//get userGames; if user GameID !in gameselections array
					array_push($gameIDKeep, $game['gameID']);
				}
			}*/			
			//array_push($gameIDKeep, $game['gameID']);				
		//}		
	//	print_r($gameIDKeep); //returning 1?		
		
		//if not in gameIDKeep array, delete game	
		foreach($userGames as $game){
			if(!in_array($game['gameID'], $gameIDKeep)){
				$this->removeUserGame($game['gameID'], $userID);
			}
		} 

		return $result['msg'];
	}//end processUserGames


   /************************************************************************************
    *Method uploads images to user profiles and does any resizing
   ***********************************************************************************/
   private function uploadAndResizeImage($userName){
		$userName=strtolower($userName);
		$imgsPath='users/'.$userName;
   	if(!$_FILES['userPic']['name']) {//if no image
			return false;
		}            
		$extension = explode('.', $_FILES['userPic']['name']);
      if(stristr($extension[1],"jp")) { //treats jpegs as jpgs
         $extension[1] = "jpg";
		}
   	$fileTypes = array("image/jpeg", "image/pjpeg", "image/gif", "image/png"); 
		$upload = new Upload("userPic", $fileTypes, $imgsPath);
		$returnFile = $upload->isUploaded();
		if($returnFile['msg']!=NULL){
         return $returnFile;
		}
      $returnFile=$returnFile['filePath'];
	
		//set the name of the thumb image to UID
		$thumbPath = $imgsPath.'/'.$userName.'.'.$extension[1];  //sets name of file to userID
		$uid = uniqid('bk');
		//set name of backup file [used during edit profile]
		$thumbBackup = $imgsPath.'/'.$uid.''.$userName.'.'.$extension[1]; 
		if(file_exists($thumbPath)){
			rename($thumbPath, $thumbBackup);
		}
		copy($returnFile, $thumbPath);
		if(!file_exists($thumbPath)){
			return false;
		}
		$imgSize = getimagesize($returnFile);
      $userPic['msg']=NULL;
      $userPic['imageFail']=NULL;
     	if($imgSize[0] > 100 || $imgSize[1] > 100) { //resize to 100*100 pixels
			$thumbImage = new ResizeImage($thumbPath, 100, $imgsPath, '');
			if(!$thumbImage->resize()) {
				$userPic['imageFail']='Unable to resize longer side of image to 100 pixels';
			}
		}//end image resize if statement		
		if(file_exists($thumbPath)){
			@unlink($thumbBackup);
         $userPic['fileName']=basename($thumbPath);
 			return $userPic;
		}else{
			rename($thumbBackup, $thumbPath);
         $userPic['msg']="Unable to upload/resize image";
			return $userPic;
		}      
	}//end uploadAndResizeImage
   
   /***********************************************************************************************
    * Method creates folders for users under users/$userName.
    * Folders created are:
       *userName
    * Folders are all lowercase
    **********************************************************************************************/
   private function makeUserFolders(){
      $userName=strtolower($_POST['userName']);
      $path='users/'.$userName;
		if (!is_dir($path)) {
         if(!mkdir($path, 0777, true)){
            return false;
         }
		}
      return true;
   }//end make user folders
   
   
   
   
   
   public function writeToFile($results, $fileName, $titles){
   
   		$str=$titles."\r\n";
   		foreach($results as $line){
   			$line=str_replace(',', ';', $line);
			$line=str_replace("\n", '', $line);
			$line=str_replace("\r", '', $line);
   			$str.=implode(',', $line);
			$str.="\r\n";
		}
		
   		$file = fopen($fileName,'w');
		fwrite($file, $str);
		fclose($file);
		
		
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		//header("Content-Length: ". filesize("$fileName").";");
		header("Content-Disposition: attachment; filename=$fileName");
		header("Content-Type: text/comma-separated-values; "); 
		header("Content-Transfer-Encoding: binary");
		readfile($fileName);
		unlink($fileName);//delete file
		return true;
   }
	
	/***********************************************************************************************
	 * Method will add up the users costs
		needs to have access to:
		infoMembership (1 or 0) | priceName: Discount_NZLarpsMember
		infoAttend(Full, sat, sun, flagship) | priceName: Reg_Full, Reg_Sat, Reg_Sun, Reg_Flagship
		infoTransport (yes/no) | priceName: Extra_Transport
		infoFood(yes/no) | priceName: Extra_Meals
		priceName: 	Discount_EarlyBird if before earlybird date
	
   public function addPrice(){

   }//end add price
	
   *******************************************************************************************/
   
}//end ModelClass
?>