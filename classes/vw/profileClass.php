<?php
/*****************************************************************************
 * Profile class displays:
 * The selected user profile based on userID from GET array
 * The form to edit the profile
 * Games submitted published by a user
 * Easy links to all the users content
******************************************************************************/
class Profile extends View{
	private $generated;
	private $model;
	private $allow; //People who have permission to edit a user profile are the user and the superuser (not other users and not moderators)
	private $msg;//store success/error messages
	private $userName;

	/***************************************************************
	Main method to display the generated profiles*
	**************************************************************/
	public function displayContent(){		
		if($_SESSION['userID']==$_GET['userID']||$_SESSION['userType']=='su'){
			$this->allow=true;
		}else{
			$this->allow=false;
		}
		$this->generated=new Generate();
		$this->model=new Model();
		$this->userName=$this->model->getUserFromID($_GET['userID']);
			
		$html.='<div class="left">'."\n".'<div class="post">'."\n";
		if($_POST['profileForm']){//process form			
			$this->msg=$this->model->processProfile();
			unset($_GET['action']);
			$userID=$_GET['userID'];
			$url="index.php?pageName=profile&userID=$userID";
			header("Location: $url");
		  exit;
		}		
		$html.=$this->showProfile();
	   if($this->model->checkGM($_GET['userID'])){
			$html.=$this->showGM();
		}
		$html.=$this->showGames();
		$html.='</div>'."\n";//end post
		$html.='</div>'."\n"; //end left		
		$html.=$this->displayRightContent();
		$html.='<div class="clear"></div>'."\n";
		
   	return $html;
	}//end display Content
	
	/**************************************************************************************
	 *Method to show the games a user has signed up to, and the
	 *status of their sign-up (ie. accepted/pending)
	 ************************************************************************************/
	private function showGames(){
		$html.='<div class="pageContent">'."\n";	
		$html.='<div class="h3Form"><h3>Game Selections</h3></div>'."\n";
		$html.='<div class="games">'."\n";
		$html.=$this->generated->displayUserGames($_GET['userID']);	//display User Games
		$html.='</div>'."\n";//end games div
		$html.='</div>'."\n";//end pageContent div
		if($_GET['userID']==$_SESSION['userID']){
			$html.='<div class="postFooter"><a href="index.php?pageName=register&&action=gamesEdit">Edit</a> | '."\n";
			$html.='<a href="index.php?pageName=register&&action=deleteUserGames">Remove All</a></div>'."\n";	//end footer div
			$html.='<div class="space"></div>'."\n";
		 }		
		return $html;
	}//end showGames
	
	/**************************************************************************************
	 *Method to show the games a user has offered to run, and the
	 *status of their submittion (ie. accepted/pending)
	************************************************************************************/
	private function showGM(){
		$html.='<div class="pageContent">'."\n";	
		$html.='<div class="h3Form"><h3>'.$this->userName.' is Running</h3></div>'."\n";
		$html.='<div class="games">'."\n";
		$games=$this->model->getGamesFromGM($_GET['userID']);			
		$html.='<div class="gameNav">'."\n";
		$html.='<ul>'."\n";					
		foreach($games as $game){
			extract($game);
			$html.='<li><div class="gWrap"><a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a></div><div class="statusRight"><em>'.$gameStatus.'</em></div></li>'."\n";
		}
		$html.='</ul>'."\n";	
		$html.='<br /><br />'."\n";				
		$html.='</div>'."\n"; //end gameNav
		$html.='</div>'."\n";//end games div
		$html.='</div>'."\n";//end pageContent div
		if($_GET['userID']==$_SESSION['userID']){		
			$html.='<div class="postFooter"><a href="index.php?pageName=submitGame">Edit or Delete Your Games</a></div>'."\n";
			$html.="\n";	//end footer div
			$html.='<div class="space"></div>'."\n";
		 }
		return $html;
	}//endShowGM
	
	/**********************************************
	Method displays profile information
	*********************************************/
	public function showProfile(){
		$userID=$this->generated->checkUser($_GET['userID']);
		$results=$this->generated->getUserDetails($userID);
		if($results){
			extract($results);
		}
		if($userID==0){
			$imgSrc='<img src="images/Anonymous.jpg" alt="Deleted User" width="100">';
			$userName="NoOne";
		}else{
			 $imgSrc='<img src="users/'.strtolower($userName).'/'.$userPic.'" alt="'.$userName.'" />';		
		}			
	   	$html='<div class="h3"><h3>'.$userName.'\'s Profile</h3></div>'."\n";//end h3div
		$html.='<div class="formContent">'."\n";
		//If user has the rights to edit the profile and has chosen to do so, display editform. Else display profile info	
		if($this->allow&&$_GET['action']=='edit'){
			$html.=$this->profileForm($results);
		}else{
			$html.=$this->displayProfileInfo($results); //for content still open
		}
		$html.='<div class="imgWrap">'."\n";
		$html.=$imgSrc;
		$html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>'."\n";
		$html.='</div>'."\n";//end image wrap
		$html.='</div>';//end form content
		$html.= $this->postFooter();
		return $html;
	}//end showProfile
	
	/************************************************************
	 *Method displays profileinfo
	************************************************************/
	private function displayProfileInfo($results){
		if($_GET['action']=='report'){//if report selected
			//report comment
			$result=$this->model->reportJob("profile");
			$reportMsg=$result['msg']; //==Success?
		}
		if($results){
			extract($results);
		}
		$userBio=htmlentities(stripslashes($userBio),ENT_QUOTES);
		$html='<div class="bioDetail">'."\n";
		if($this->msg){
			if($this->msg['msg']=="Success"){
					$html.='<h4 class="profileH">You have Successfully updated your profile!</h4>'."\n";
			}else{
				$html.='<h4 class="profileH">Account details not updated: </h4>'."\n";
				$html.='<p class="error">'.$this->msg['msg'].'</p>'."\n";
			}
		}	
		$html.='<div class="bioText">'."\n";
		$html.='<p>'."\n";		
		if($userBio!=""&&strlen($userBio)>0){
			$html.=nl2br($userBio);
		}else{
			$html.="No Bio";
			}
		$html.='</p>'."\n".'</div>'."\n";//end biotext
		$html.='</div>'."\n";//end bioDetail
		
		return $html;
	}//end displayprofileinfo

	private function postFooter(){
		$html='<div class="postFooter">'."\n";
		if($this->allow){
			$html.='<!--this user owns this profile, or user is admin/-->'."\n";
			$html.='<a href="index.php?pageName=profile&amp;userID='.$_GET['userID'].'&amp;action=edit" class="highlight">Edit Profile</a>'."\n";	
		}else if($_SESSION['userID']){
			$html.='<!--another user owns this profile/-->'."\n";
			if($reportMsg=="Success"){
				$html.='<span class="highlight">'."\n".'<em>You have just reported this Profile</em>'."\n".'</span>'."\n";
			}else{
				$html.='<a href="index.php?pageName=profile&amp;userID='.$_GET['userID'].'&amp;action=report" class="highlight">report</a>'."\n";
			}
		}
		$html.='</div>'."\n";
		return $html;
	}
	
	/*********************************************************************
	 *Method displays the form to edit user profiles
	*********************************************************************/
	private function profileForm($results){	
		extract($results);		
		$html='<div id="profileForm">'."\n";
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data" method="post" id="pForm">'."\n";
		$html.='<div class="formContent">'."\n";
		$html.='<div class="bioDetail">'."\n";
		$html.='<div class="bioText">'."\n";
		$html.='<textarea rows="12" cols="50" name="userBio" id="userBio" />'."\n";
		$html.=htmlentities(stripslashes($userBio),ENT_QUOTES);
		$html.='</textarea>'."\n";
		$html.='</div>'."\n"; ///end bioText
		$html.='<div class="userOpt">'."\n";
		$html.='<div class="group">'."\n";
		$html.='<label for="userEmail">Full Name:</label>'."\n";
		$html.='<input type="text" name="userFullName" value="'.$userFullName.'" />'."\n";
		$html.='<div id="userPic_msg">'.$userFullName_msg.'</div>'."\n";
		$html.='</div>'."\n";
		$html.='<div class="group">';
		$html.='<label for="userPic">Replace Profile Image</label>'."\n";
		$html.='<input type="file" name="userPic" id="userPic" />'."\n";
		$html.='<div id="userPic_msg">'.$userPic_msg.'</div>'."\n";
		$html.='</div>'."\n";
		$html.='<div class="repeatPassword">'."\n";
		$html.='<label for="userPassword">New Password?:</label>'."\n";
		$html.='<input type="password" name="userPassword" />'."\n";
		$html.='</div>'."\n";
		$html.='<div class="repeatPassword">'."\n";
		$html.='<label for="userPasswordRepeat">Repeat Password:</label>'."\n";
		$html.='<input type="password" name="userPasswordRepeat" />'."\n";
		$html.='</div>'."\n";
		$html.='<div class="clear"></div>'."\n";
		$html.='<div class="group">'."\n";
		$html.='<label for="userEmail">Email Address?:</label>'."\n";
		$html.='<input type="text" name="userEmail" value="'.$userEmail.'" />'."\n";
		$html.='</div>'."\n";
		$html.='</div>'."\n";//end userOpt div
		$html.='<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />'."\n";
		$html.='<input type="hidden" name="userID" value="'.$_GET['userID'].'" id="userID" />'."\n";
		$html.='<input type="hidden" name="userName" value="'.$userName.'" id="userName" />'."\n";
		$html.='<input type="submit" name="profileForm" value="Update" id="profileSubmit" />'."\n";
		$html.='<div class="clear"></div>'."\n";
		$html.='</form>'."\n".'</div>'."\n";//end form and profileForm Div
		$html.='</div>'."\n";
		$html.='</div>'."\n";
		return $html;		
	}//end displayProfileForm	
	
	/**********************************************************************************************
	*Method displays the rightBox in userProfiles with a list of their tags
	**********************************************************************************************/
	private function displayRightContent(){
		$html='<div class="right">';
		$html.=$this->generated->displaySearchBox();
			if($_SESSION['userID']==$_GET['userID']){
				$html.='<div class="pageNav">'."\n";
				$html.='<div class="h2"><h2>Your Registration</h2></div>'."\n";
				$html.='<div class="rightContent">'."\n";
				$html.=$this->generated->displayUserProcess();			 		
				$html.='</div>'."\n";
				$html.='</div>'."\n";
			}
		/*	$html.='<div id="pageNav">';
			$html.='<div class="h2"><h2>'.$this->userName.'\'s Game Choices</h2></div>';
			$html.='<div class="rightContent">';
			$html.=$this->generated->displayUserGames();
			$html.='<p class="rightFooter"><a href="index.php?pageName=register&&action=edit">Edit</a> | ';
		   $html.='<a href="index.php?pageName=register&&action=deleteUserGames">Remove All</a></p>'."\n";	
			$html.='</div>';			
			$html.='</div>';//end pageNav*/
		$html.='</div> <!-- end right div /-->'."\n";
		return $html;		
	}//end displayRightBox Method
	
	
}//end profile Class
?>