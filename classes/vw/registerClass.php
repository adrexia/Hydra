<?php
/*****************************************************************************
 * The Register class is very form heavy.
 * It displays:
	 * The form to create an account and the form to register for the event
	 * The form to select ga,es
	  * The process the user is up to in their registration
	  * A summery of all the data given by a user in their own registration
******************************************************************************/
include_once 'classes/captchaClass.php'; //include captch class
class Register extends View{
   private $model;
   private $generated;
   private $msg;
   public $captcha;
	
   /*Main method to display the registration page, including variable initialization*/
    protected function displayContent(){
		  $this->msg=NULL;
		  $this->model=new Model();
		  $this->generated=new Generate();
		  $this->captcha = new Captcha();
		    
		  $html=$this->displayRegister(); //runs all processes on this page	 
		  return $html;  
    }//end display content
	 
	 /******************************************************************************
	  *Method checks all st post arrays and then runs the
	  *display Handle method
	 ****************************************************************************/
	 private function displayRegister(){
	 	
	/*	if(isset($_GET['action'])&&$_GET['action']=='deleteUserGames'){
					unset($_GET['action']);
					$result=$this->model->removeUserGames($_SESSION['userID']);
					header('Location: index.php?pageName=register');
					exit;				
				}*/
		  //Check post array for form actions and process
		  if($_POST['cancel']){ //canceled delete user
				unset($_GET['action']);
				header('Location: index.php?pageName=register');
				exit;
		  }
	 	
		  if($_POST['delete']){//delete user selected
				unset($_GET['action']);
				$result=$this->model->deleteUser($_SESSION['userID']);
				header('Location: index.php?pageName=register');
				exit;
		  }		
		  if($_POST['account']){//process create user/reg form on submit
				//if doesn't captcha match, find any other form errors and display tall errors to user: Else process properly 
				if($_POST['captcha_req']&&!$this->captcha->match($_POST['captcha'])){ 
					 $result=$this->model->validatePost();
					 $this->msg=$result['msg'];
					 $this->msg.='* Words not matched. </br />';
				}else{		
					 @unlink($_SESSION['location']);
					 unset($_SESSION['location']);
					 $result=$this->model->processProfile();
					 $this->msg=$result['msg'];
				}
		  }
		  if($_POST['gameSelect']){//process create user/reg form on submit
				unset($_GET['action']);
				$result=$this->model->processUserGames();
				header('Location: index.php?pageName=register');
				exit;
		  }	  
		  $html=$this->displayHandle(); 
		  return $html;
	 }//end displayRegister
	
		
    /************************************************************************************************
    * Checks if form has been processed, if data exists for a user, what part
    * (if any) the user wishes to edit and runs the appropriate methods 
    *************************************************************************************************/
    private function displayHandle(){		  
		  $userGamesBool=$this->model->checkUserGames($_SESSION['userID']);//checks to see if user has chosen games
		  $infoBool=$this->model->checkInfoForUser($_SESSION['userID']); // checks to see if the user has filled in the current registration form			  
		  if($this->msg=="Success"){ //then processing has been successful. Unset any actions in Get array and reload page
				unset($_GET['action']);
				header('Location: index.php?pageName=register');
				exit;
		  }else{//if form not submited or returns error		 
				if($userGamesBool&!$_GET['action']){
					 $html.=$this->displayCompletedReg();			
				}elseif($infoBool&&($_GET['action']!='userDelete'&&$_GET['action']!='userEdit'&$_GET['action']!='regEdit'&&$_GET['action']!='regDelete')){
					 //if logged in user has registered, go to game selection
					 $html.=$this->displayGamesReg();		
				}elseif($_SESSION['userID']&&$_GET['action']=='userDelete'){ //if user has chosen to delete their account	  
					 
					 $html.='<div class="left">'."\n".'<div class="post">'."\n";			
					 $html.=$this->deleteAccount();
					 $html.='</div><!-- end left div /-->'."\n";
					 $html.=$this->displayRightContent();
					 $html.='<div class="clear"></div>'."\n";
					 $html.='</div>'."\n";	
				}else{	//if first form needs to be filled in/edited	  
					 
					 $html.='<div class="left">'."\n".'<div class="post">'."\n";			
					 $html.=$this->handleForms();
					 $html.='</div>'."\n".'</div>'."\n";	
					 $html.=$this->displayRightContent();
					 $html.="\n".'<div class="clear"></div>'."\n";
							
				}
		  }
		  return $html;
    }//end display submitted
	
	/***********************************************************************************
	 *Method displays the delete account message and confirmation form
	 **********************************************************************************/
	 private function deleteAccount(){
		  $html='<div class="h3Form"><h3>Delete Account?</h3></div>'."\n";
		  $html.=$this->displayUserInfo();
		  $html.='<p class="warning">';
		  $html.='<strong>Warning: </strong>';
		  $html.='Deleting your account is irreversible. It will remove your profile and any registration or games you have associated with your account.';
		  $html.='</p><br /><br />'."\n";
		  $html.='<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post" id="deleteForm">'."\n";
		  $html.='<p class="centre"><strong>Are you sure you want to delete this account?</strong><br /><br />'."\n";
		  $html.='<input type="submit" name="cancel" value="No, please take me back" id="cancel" />'."\n";
		  $html.='<input type="submit" name="delete" value="Yes, please delete this account" id="delete" />'."\n";
		  $html.='</p></form>'."\n";
		  return $html;
	 }//end deleteAccount
	
	 /*********************************************************************************************
	 * Method handles the display of the intitial registration form
	 ********************************************************************************************/
	 private function handleForms(){
		  if($_POST['account']){ //if not successful processing but post exists
				$html='<div class="h3Form"><h3>Registration Failed:</h3></div>'."\n";
				$html.='<div class="post">'."\n";
				$html.='<div class="formContent"><p class="note">'.$this->msg.'</p></div>'."\n";
				$html.='</div>'."\n";//end post
		  }
		  if($_SESSION['userID']&&$_GET['action']!='userEdit'){ //if user and not edit
		  
				$html.=$this->startForm();		   
				$html.=$this->displayUserInfo();		  
				$html.=$this->registerUser();		  
		  }else{ 
		 	
				$html.=$this->createAccount();	//runs startForm method from inside	   
				$html.=$this->registerUser();		  
		  }		
		  return $html;
	 }//end handle Form
	
	 /*********************************************************************
	 * Method displays the info for the an existing User
	 * filling in the registration form
	 *********************************************************************/
	 private function displayUserInfo(){
		  $result=$this->model->getUserDetails($_SESSION['userID']);
		  extract($result);
		  $imgSrc='<img src="users/'.strtolower($userName).'/'.$userPic.'" alt="'.$userName.'" />';
		  $html='<div class="pageContent">'."\n";
		  $html.='<div class="h3Form"><h3>User Details</h3></div>'."\n";
		  $html.='<div class="post">'."\n".'<div class="formContent">'."\n";
		  $html.='<div class="info">'."\n";
		  $html.='<p><span class="labelUser">Full Name: </span> '.$userFullName.'</p>'."\n";
		  $html.='<p><span class="labelUser">Email: </span> <a href="mailto:'.$userEmail.'">'.$userEmail.'</a></p>'."\n";	
		  $html.='</div>'."\n";//end info
		  $html.='<div class="imgWrap">'."\n";
		  $html.=$imgSrc;
		  $html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>'."\n";
		  $html.='<input type="hidden" name="userID" value="'.$_SESSION['userID'].'" id="userID" />'."\n";
		  $html.='<input type="hidden" name="userName" value="'.$_SESSION['userName'].'" id="userName" />'."\n";
		  $html.='</div>'."\n";//end imgwrap
		  $html.='</div>'."\n";//end formContent
		  $html.='</div>'."\n";//end post
		  $html.='</div>'."\n";//end pageContent
		  if($_GET['action']!='delete'&&$_GET['action']!='userDelete'){ //display user footer if user isn't delting account
				$html.='<div class="postFooter">'."\n";
				$html.='<a href="index.php?pageName=register&amp;action=userEdit">Edit</a> | '."\n";
				$html.='<a href="index.php?pageName=register&amp;action=userDelete">Delete Account</a>'."\n";
				$html.='</div>'."\n";
		  }
		  $html.='<div class="space"></div>'."\n";
		  return $html;
	}//end displayUserInfo
	
	/*****************************************************************************************
	 *Helper Method to display the userName field in the form.
	 ****************************************************************************************/
	 private function userNameField(){
		  if(!$_SESSION['userName']){//if no logged in user
				$html.='<li>'."\n";
				$html.='<label for="userName">User Name*</label>'."\n";
				$html.='<input type="text" value="'.$_POST['userName'].'" name="userName" id="userName" />'."\n";
				$html.='<span class="error" id="userNameMsg"></span>'."\n";
				$html.='</li>'."\n";
				$html.='<li class="hidden">'."\n";
				$html.='<input type="hidden" name="userID" value="0" id="userID" />'."\n";
				$html.='</li>'."\n";
		  }else{ //if logged in user editing their details
				$html.='<li>'."\n";
				$html.='<span class="label">User Name</span>'."\n";
				$html.=$_SESSION['userName']."\n";
				$html.='</li>'."\n";
				$html.='<li class="hidden">'."\n";
				$html.='<input type="hidden" name="userID" value="'.$_SESSION['userID'].'" id="userID" />'."\n";
				$html.='<input type="hidden" name="userName" value="'.$_SESSION['userName'].'" id="userName" />'."\n";
				$html.='</li>'."\n";
		  }
		  return $html;
	 }//end userNameField
	
	 /*****************************************************************************************
	 * Method used to generate form for user creation and registration
	 ****************************************************************************************/
	 private function createAccount(){	 
	 	  	
		  $result=$this->model->getUserDetails($_SESSION['userID']);//if the user already exists
		  if($result){
			 extract($result);
		  }
		  if($_POST['account']){
			 extract($_POST);
		  }
		  $_SESSION['location']=$this->captcha->create();
		 $html.=$this->startForm();
		  $html.='<div class="pageContent">'."\n";
		  $html.='<div class="h3Form"><h3>Personal Details</h3></div>'."\n";		 
		  $html.='<div class="post">'."\n";
		  $html.='<ul class="form">'."\n";
		  $html.=$this->userNameField();
		  $html.='<li>'."\n";
		  $html.='<label for="userFullName">Full Name*</label>'."\n";
		  $html.='<input type="text" value="'.$userFullName.'" name="userFullName" id="userFullName" />'."\n";
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="userEmail">Email*</label>'."\n";
		  $html.='<input type="text" value="'.$userEmail.'" name="userEmail" id="userEmail" />'."\n";
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="userPassword">Password*</label>'."\n";
		  $html.='<input type="password" value="" name="userPassword" id="userPassword" />'."\n";
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="userPassword2">Repeat Password*</label>'."\n";
		  $html.='<input type="password" value="" name="userPassword2" id="userPassword2" />'."\n";
		  $html.='</li>'."\n".'<li class="last">'."\n";
		  $html.='<label for="userPic">User Picture</label>'."\n";
		  $html.='<input type="file" size="40" value="" name="userPic" id="userPic" />'."\n";
		  $html.='<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />'."\n";
		  $html.='</li>'."\n".'<li class="captcha">'."\n";
		  $html.='<label for="captcha">Enter the words in the image*</label>'."\n";
		  $html.='<input type="text" id="captcha" name="captcha" />'."\n";
		  $html.='<input type="hidden" id="captcha_req" value="true" name="captcha_req" />'."\n";
		  $html.='<span class="formExtra" id="captchaMsg"><img src="'.$_SESSION['location'].'" alt="'.$_SESSION['captcha'].'" /></span>'."\n";	
		  $html.='<div class="clear"></div>'."\n";		  
		  $html.='</li>'."\n".'</ul>'."\n".'</div>'."\n".'</div>'."\n";//end list item, list, post div, pageContent div
		  return $html;
	 }//end createAccount

	
	 /************************************************************************
	  * Method produces the registration form for a user
	 *************************************************************************/
	 private function registerUser(){
		  if($_POST['account']){
				extract($_POST);
	     }elseif($this->model->checkUserInfo($_SESSION['userID'])){		  
				$userInfo=$this->model->getUserInfo($_SESSION['userID']);
				extract($userInfo);
			    $infoFoodArray = explode(";", $infoFood);
				 $infoFood=$infoFoodArray[0];
				 $infoFoodExtra=$infoFoodArray[1];
		  }
		  //Arrays for select box options
		  $optionValueAttend=array("Full", "Saturday", "Sunday", "Flagship");//attendance options
		  $optionNameAttend=array("Full Convention", "Saturday Only", "Sunday Only", "Flagship Larp Only (Saturday night)");	  		    
		  $optionValueMeal=array("No", "Yes", "Yes, but");//meal options
		  $optionNameMeal=array("None, I'll supply my own meals", "Yes, please feed me", "Yes, but I'm... (fill in extra info) ");		  
		  $optionValueAccom=array("None", "Both", "Friday", "Saturday");//accomodation options
		  $optionNameAccom=array("None Required", "Friday and Saturday", "Friday Only", "Saturday Only");	  		 		  
		  //Hydra reg form
		  $html.='<div class="pageContent">';
		  $html.='<div class="h3Form"><h3>Registration Details</h3></div>'."\n";
		  $html.='<ul class="formB">'."\n";				 
		  $html.='<li>'."\n";
		  $html.='<label for="infoAttend">Attendance:</label>'."\n";
		  $html.='<select name="infoAttend" id="infoAttend">'."\n";	
		  $html.=$this->populateSelect($optionValueAttend, $optionNameAttend, $infoAttend);
		  $html.='</select>'."\n";	
		  $html.='<span class="error" id="infoAttendMsg"></span>'."\n";	
		  $html.='</li>'."\n".'<li class="check">'."\n";
		  $html.='<span class="label">NZLarps Member?*</span>'."\n";
		  $html.='<div class="checkbox">'."\n";
		  $html.='<input type="radio" value="1" name="infoMembership" id="infoMembershipYes" ';
		  if($infoMembership=="1"){
			$html.='checked="checked"';
		  }
		  $html.=' />'."\n";
		  $html.='<label class="inline" for="infoMembershipYes">Yes</label>'."\n";
		  $html.='<input type="radio" value="0" name="infoMembership" id="infoMembershipNo" ';
		  if($infoMembership=="0"||!$infoMembership){
			$html.='checked="checked"';
		  }
		  $html.=' />'."\n";
		  $html.='<label class="inline" for="infoMembershipNo">No</label>'."\n";
		  $html.='<span class="error" id="infoMembershipMsg"></span>'."\n";	
		  $html.='</div>'."\n";
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="infoPlayWith">I\'d like to play with...</label>'."\n";
		  $html.='<input type="text" value="'.$infoPlayWith.'" name="infoPlayWith" id="infoPlayWith" />'."\n";
		  $html.='<span class="error" id="infoPlayWithMsg"></span>'."\n";	
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="infoNotPlayWith">I can\'t play with...</label>'."\n";
		  $html.='<input type="text" value="'.$infoNotPlayWith.'" name="infoNotPlayWith" id="infoNotPlayWith" />'."\n";
		  $html.='<span class="error" id="infoNotPlayWithMsg"></span>'."\n";	
		  $html.='</li>'."\n".'<li class="check">'."\n";
		  $html.='<span class="label">Transport Option:*</span>'."\n";
		  $html.='<div class="checkbox">'."\n";
		  $html.='<input type="radio" value="Yes" name="infoTransport" id="infoTransportYes" ';
		  if($infoTransport=="Yes"){
			$html.='checked="checked"';
		  }
		  $html.=' />'."\n";	  
		  $html.='<label class="inline" for="infoTransportYes">Yes</label>'."\n";
		  $html.='<input type="radio" value="No" name="infoTransport" id="infoTransportNo" ';
		  if($infoTransport=="No"||!$infoTransport){
			$html.='checked="checked"';
		  }
		  $html.=' />'."\n"; 
		  $html.='<label class="inline" for="infoTransportNo">No</label>'."\n";
		  $html.='<span class="error" id="infoTransportMsg"></span>'."\n";	
		  $html.='</div>'."\n";	
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="infoFood">Meal Option:</label>'."\n";
		  $html.='<select name="infoFood" id="infoFood">'."\n";	
		  $html.=$this->populateSelect($optionValueMeal, $optionNameMeal, $infoFood);
		  $html.='</select>'."\n";	
		  $html.='<span class="error" id="infoFoodMsg"></span>'."\n";	
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="infoFoodExtra">Meal Extra Info:</label>'."\n";
		  $html.='<input type="text" value="'.$infoFoodExtra.'" name="infoFoodExtra" id="infoFoodExtra" />'."\n";
		  $html.='<span class="error" id="infoFoodMsgExtra"></span>'."\n";	
		  $html.='</li>'."\n".'<li>'."\n";
		  $html.='<label for="infoAccom">Accommodation:</label>'."\n";
		  $html.='<select name="infoAccom" id="infoAccom">'."\n";	
		  $html.=$this->populateSelect($optionValueAccom, $optionNameAccom, $infoAccom);	
		  $html.='</select>'."\n";	
		  $html.='<span class="error" id="infoAccomMsg"></span>'."\n";	
		  $html.='</li>'."\n".'<li class="commentBox">'."\n";
		  $html.='<label for="infoComments">Comments</label>'."\n";
		  $html.='<textarea rows="3" cols="50" name="infoComments" id="infoComments">'.$infoComments.'</textarea>'."\n";
		  $html.='<span class="error" id="infoCommentsMsg"></span>'."\n";	
		  $html.='<div class="clear"></div>'."\n";		  
		  $html.='</li>'."\n".'<li class="submit">'."\n";
		//  $html.='<h3 class="inline">Total:</h3> <span id="total">$0</span>'."\n";	 //target and replace price 
		  $html.='<input type="submit" name="account" value="Submit!" id="pageSubmit" />'."\n";	
		  $html.='</li>'."\n".'</ul>'."\n".'<p>&nbsp;</p>'."\n";
		  $html.='</div>';
		  $html.='</form>'."\n";//end pageContent
		  return $html;
	 }//end registerUser
	
	 /**************************************************************************************************
	 *Help Method starts the form for registeration and/or account creation
	 ************************************************************************************************/
	 private function startForm(){
		  $html='<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data" method="post" id="mainForm">'."\n";
		  return $html;
	 }//end startForm
	
		
	
	 /**********************************************************************************
	 * Helper method to populate select options for the games array
	 * Takes an array of values, an array of displayNames and
	 * the field history to make the select sticky
	 *********************************************************************************/
	 private function populateSelectGames($optionValue, $optionName, $field){	 
		  $html.='<option value="'.$optionValue.'"';
		  if($field==$optionValue){
				$html.=' selected="selected"';
		  }
		  $html.=' >'."\n";
		  $html.=$optionName.'</option>'."\n";
		  return $html;
	 }//end populateSelectGames	
	 
	
	 /****************************************************************************
	 * Game Selection method produces the form for games selection
	 * *************************************************************************/
	 private function gameSelection(){	 
	      $slotName=array("Friday Night","Saturday Morning","Saturday Afternoon","Saturday Evening","Sunday Morning","Sunday Afternoon");
		  $slotValue=array("","First_1", "Second_1", "First_2", "Second_2","First_3", "Second_3","First_4", "Second_4","First_5", "Second_5","First_6", "Second_6");
	      $slotChar=array("","First_1_Char", "Second_1_Char", "First_2_Char", "Second_2_Char","First_3_Char", "Second_3_Char","First_4_Char", "Second_4_Char","First_5_Char", "Second_5_Char","First_6_Char", "Second_6_Char");
		  $html=$this->gameSelectionForm($slotName, $slotValue, $slotChar);	//process with user content and any existingpost array
		  $html.=$this->gameSelectionGeneral($infoGameComments);		//attach form footer/general comments field
		  return $html;		
	 }//end user Created	 
		
	 /*****************************************************************************
	 * Helper Method produces the static content at the end of the
	 * game selection form
	 ****************************************************************************/
	 private function gameSelectionGeneral($infoGameComments){
	 	  $html.='<div class="h3"><h3>General</h3></div>'."\n";		
		  $html.='<ul class="form">'."\n";
		  $html.='<li class="commentBoxSmall">'."\n";		
		  $html.='<label for="infoGameComments">Notes &amp; Preferences:</label>'."\n";
		  $html.='<textarea  rows="3" cols="50" name="infoGameComments" id="infoGameComments">'.$infoGameComments.'</textarea>'."\n";		
		  $html.='</li>'."\n";	
		  $html.='<li class="submit">'."\n"; //submit form
		  $html.='<input type="submit" name="gameSelect" value="Submit!" id="pageSubmit" />'."\n";	
		  $html.='</li>'."\n";
		  $html.='</ul>'."\n";
		  $html.='<p>&nbsp;</p>'."\n";
		  $html.='</form>'."\n";	
		
		  return $html;
	 }//end gameSelectionGeneral
	
	/********************************************************************************************
	 * Method produces the form for game selection. 
	********************************************************************************************/
	 private function gameSelectionForm($slotName, $slotValue, $slotChar){
	     $oldData=$this->model->getUserGames($_SESSION['userID']);	
		  if($_POST['gameSelect']){ //at the moment there is no validation that stops this form from being submitted, so this won't be called
				extract($_POST);
		  }elseif(is_array($oldData)){
				foreach($oldData as $old){//set up variables with stored data
					 $userGamesPref=$old['userGamesPref'];
					 $gameID=$old['gameID'];
					 $userGamesCharPref=$old['userGamesCharPref'];				
					 $gameSlot=$old['gameSlot'];
					 $gameChoice[$gameSlot][$userGamesPref][$gameID]=$userGamesCharPref;
				}//end foreach
		  }
		  $html=$this->startForm();
		  $i=1;  //int to cycle through slotNumber array, starts at 1 (should end at 6)
		  $j=1; //int to cycle through game choice array, starts at 1 (should end at 12)
	     foreach($slotName as $slot){		 
				$html.='<div class="h3"><h3>'.$slot.'</h3></div>'."\n";
				$games=$this->model->getGameBySlot($i);		  
				$html.='<ul class="form">'."\n";
				$html.='<li class="'.$slotValue[$j].'">'."\n";
				$html.='<label for="'.$slotValue[$j].'">First Choice:</label>'."\n";
				$html.='<select name="'.$slotValue[$j].'" id="'.$slotValue[$j].'">'."\n";
				$html.='<option value="1" ';
				if($$slotValue[$j]==null){
					 $html.='selected="selected"';
				}
				$html.=' >'."\n";
				$html.='Not Attending</option>'."\n";				
				if(is_array($gameChoice["$i"][1])){
					 $key=array_keys($gameChoice["$i"][1]);
					 $key=$key[0];		
				}else{
					 $key=0;
				}
				if($games){
					foreach($games as $game){
						 $html.=$this->populateSelectGames($game['gameID'], $game['gameName'], $key);				 
					}
				}
				$html.='</select>'."\n";		
				$html.='<div class="clear"></div>'."\n";
				$html.='</li>'."\n";
				$html.='<li class="commentBoxSmall '.$slotValue[$j].'">'."\n";
				$html.='<label for="'.$slotChar[$j].'">Character Preferences:</label>'."\n";
				$html.='<textarea  rows="3" cols="50" name="'.$slotChar[$j].'" class="charPref" id="'.$slotChar[$j].'" >'.$gameChoice[$i][1][$key].'</textarea>'."\n";
				$html.='<div class="clear"></div>'."\n"."\n";
				$html.='</li>'."\n";
				$j++; //Increment choice array pointer				 
				if(is_array($gameChoice["$i"][2])){
					 $key=array_keys($gameChoice["$i"][2]);
					 $key=$key[0];		
				}else{
					 $key=0;
				}			 
				$html.='<li class="'.$slotValue[$j].'">'."\n";
				$html.='<label for="'.$slotValue[$j].'">Second Choice:</label>'."\n";
				$html.='<select name="'.$slotValue[$j].'" id="'.$slotValue[$j].'">'."\n";
				$html.='<option value="1" ';
				if($$slotValue[$j]==null){
					$html.='selected="selected"';
				}
				$html.=' >'."\n";
				$html.='Not Attending</option>'."\n";
				if($games){
					foreach($games as $game){
						$html.=$this->populateSelectGames($game['gameID'], $game['gameName'], $key);
					}
				}
				$html.='</select>'."\n";		
				$html.='<div class="clear"></div>'."\n";		
				$html.='</li>'."\n";	
				$html.='<li class="commentBoxSmall  '.$slotValue[$j].'">'."\n";		
				$html.='<label for="'.$slotChar[$j].'">Character Preferences:</label>'."\n";
				$html.='<textarea  rows="3" cols="50"  name="'.$slotChar[$j].'" class="charPref" id="'.$slotChar[$j].'">'.$gameChoice[$i][1][$key].'</textarea>'."\n";		
				$html.='</li>'."\n";		
				$html.='</ul>'."\n";		
				$j++; //Increment choice array pointer
				$i++;
		  }				 
		  return $html;
	 }//userGameSelection		
	
	 /*********************************************************************
	  * Method to display the game selections page either
	  * for users with no games, or for those editing games
	  ********************************************************************/
	 private function displayGamesReg(){
		 
		  $html.='<div class="left">'."\n";
		  $html.='<div class="post">'."\n";
		  $html.='<div class="pageContent">'."\n";
		  $html.='<div class="h3Form"><h3>Game selection</h3></div>'."\n";
		 //$pageDetails=$this->model->getPageContent('register'); //no interface, so not used
		  $pageDetails="Game Selection is now open! Choose your games below. Please supply 2 choices for each round.";
		  
		 if(!$_GET['live']==true){
			  $html.='<p class="note">Games selection is not yet live. ';
			  $html.='We will send you an email when we have enough games to open up ';
			  $html.='this section of the registration process. Remember your username and password, so you can log back in.<br />';
			  $html.=' Thanks!</p>'; 
		  }else{
		 	 $html.='<p class="space">'.$pageDetails.'</p>'."\n";
			 $html.=$this->gameSelection(); //game selection
		    }
		  $html.='</div></div></div><!-- end left div /-->'."\n";	
		  $html.=$this->displayRightContent();		
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</div>'."\n";
		  return $html;
	 }//end displayGamesReg	
	 
	 	 /**********************************************************************************
	 *Helper method to populate select options.
	 *Takes an array of values, an array of displayNames and
	 *the field history to make the select sticky
	 *********************************************************************************/
	 public function populateSelect($optionValue, $optionName, $field){	 
		  $i=0;
		  foreach($optionValue as $value){
				$html.='<option value="'.$value.'"';
				if($field==$value){
					 $html.=' selected="selected"';
				}
				$html.=' >'."\n";
				$html.=$optionName[$i].'</option>';
				$i++;
		  } 
		  return $html;
	 }//end populateSelect
	
	 
	 
	
	/*************************************************************************
	 *Method to display ta users submitted data to them.
	 *Is shown to the userr if they have completed all three
	 *stages of registration: user creationb, reg, and game selection
	 ************************************************************************/
    private function displayCompletedReg(){
		  
		  $html.='<div class="left"><div class="post">'; 
		  $html.=$this->displayUserInfo();	  	 
		  $html.=$this->displayUserReg();	 //display User Registration
		  $html.='<div class="pageContent">';	
		  $html.='<div class="h3Form"><h3>Game Selections</h3></div>'."\n";
		  $html.='<div class="games">';
		  $html.=$this->generated->displayUserGames($_SESSION['userID']);	//display User Games
		  $html.='</div>';//end games div
		  $html.='</div>';//end pageContent div
		  $html.='<div class="postFooter"><a href="index.php?pageName=register&amp;action=gamesEdit">Edit</a>  ';
		  //$html.='| <a href="index.php?pageName=register&amp;action=deleteUserGames">Remove All</a>';
		  $html.='</div>'."\n";	//end footer div
		  $html.='<div class="space"></div>'."\n";
		  $html.='</div><!-- end left div /-->';
                    $html.='</div></div>'."\n";  	
		  $html.=$this->displayRightContent();		
		  $html.='<div class="clear"></div>'."\n";
		
		  return $html;
    }//end displayCompletedReg
	
	/************************************************************************************************
	 * Helper Method turns information from the form back into the detials the
	 * user submitted and then runs the method to display the information to the user
	 **********************************************************************************************/
	 private function displayUserReg(){
		  $userReg=$this->model->getUserInfo($_SESSION['userID']);		 
		  if($userReg['infoMembership']==1){
			 $userReg['infoMembership']='Yes';
		  }else{
			  $userReg['infoMembership']='No';
		  }
		  switch($userReg['infoAttend']){
			 case 'Full': $userReg['infoAttend']="Full Convention";
			 break;
			 case 'Saturday': $userReg['infoAttend']= "Saturday Only";
			 break;
			 case 'Sunday': $userReg['infoAttend']="Sunday Only";
			 break;
			 case 'Flagship': $userReg['infoAttend']="Flagship Larp Only";
			 break;		 
		  }
		  $html=$this->showUserRegDetails($userReg);
		  return $html;
    }//end display User Reg	 
	 
	 /***********************************************************************************
	  *Method displays the details submitted in the user registration form
	 **********************************************************************************/
	 private function showUserRegDetails($userReg){		  		
		  extract($userReg);
		  $html='<div class="pageContent">'."\n";	
		  $html.='<div class="h3Form"><h3>Registration Details</h3></div>'."\n";//end h3div
		  $html.='<div class="reg">'."\n";	
		  $html.='<div class="post"><div class="formContent">'."\n";	
		  $html.='<p><span class="labelUser">Attendance: </span>'.$infoAttend.'</p>'."\n";	
		  $html.='<p><span class="labelUser">NZLarps Member?: </span>'.$infoMembership.'</p>'."\n";		  
		  $html.='<p><span class="labelUser">I\'d like to play with: </span>'.$infoPlayWith.'</p>'."\n";	
		  $html.='<p><span class="labelUser">I can\'t play with: </span>'.$infoNotPlayWith.'</p>'."\n";	
		  $html.='<p><span class="labelUser">Transport Option: </span>'.$infoTransport.'</p>'."\n";	
		  $html.='<p><span class="labelUser">Meal Details: </span>'.$infoFood.'</p>'."\n";	
		  $html.='<p><span class="labelUser">Accommodation: </span> '.$infoAccom.'</p>'."\n";	
		  $html.='<p><span class="labelUser">Comments: </span> '.$infoComments.'</p>'."\n";	
		  $html.='</div>'."\n";	//end formContent
		  $html.='</div>'."\n";	//end post	
		  $html.='</div>'."\n";//end pageContent
		  $html.='</div>'."\n";//end reg
		  $html.='<div class="postFooter"><a href="index.php?pageName=register&amp;action=regEdit">Edit | </a>'."\n";
		  $html.='<a href="index.php?pageName=register&&action=regRemove">Remove</a></div>'."\n";
		  $html.='<div class="space"></div>'."\n";
		  return $html;
	 }//end showUserRegDetails
		
	  /*Method to display the right content*/
	 private function displayRightContent(){	 
		  $html='<div class="right">'."\n";
		  $html.=$this->generated->displaySearchBox();	
		  $html.='<div class="pageNav">'."\n";
		  $html.='<div class="h2"><h2>Your Registration</h2></div>'."\n";
		  $html.='<div class="rightContent">'."\n";
		  $html.=$this->generated->displayUserProcess();			 		
		  $html.='</div>'."\n";
		  $html.='</div>'."\n";		  
		  $html.='<div class="pageNav">'."\n";
		  $html.='<div class="h2"><h2>Roster of Games</h2></div>'."\n";
		  $html.='<div class="rightContent">'."\n";
		  $html.=$this->generated->displayGameSchedule();	
		  $html.='</div>'."\n";
		  $html.='</div>'."\n";	  
		  $html.='</div> <!-- end right div /-->'."\n";
		  return $html;	
	 }//end displayRightBox

}//end Register Class
?>