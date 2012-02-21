<?php
/******************************************************************************************
 *Game Class contains all the methods to display the page that is
 *generated for submitted games
 ****************************************************************************************/
class Game extends View{	
	private $generated;
	private $model;
	private $result;
	private $allow;
	private $gameBool;
	private $statusBool;

	/********************************************************************
	*Main display Method. Establishes variables and runs
	*the main content display methods
	************************************************************************/
	public function displayContent(){
		$this->generated=new Generate();
		$this->model=new Model();
		$userID=$this->generated->getUserIDFromGame();
		$userID=$this->generated->checkUser($userID);
		$this->gameBool=$this->generated->checkGame($_GET['gameID']);//does game exist		
		$this->statusBool=$this->model->checkStatus($_GET['gameID']);//is game open?		
		if(($_SESSION['userID']==$userID||(isset($_SESSION['userType'])&&$_SESSION['userType']!='user'))){//set allow variable
			$this->allow=true;
		}elseif($statusBool){
			$this->allow=true;			
		}
		$html.=$this->displayRightContent();		
		$html.=$this->displayLeftContent();
		
		return $html;
	}//end displayContent
	
	/******************************************************************************************
	 *Method checks submitted forms and then runs the methods to generate
	 *visual content. displays the left hand content of an individual games page
	 ****************************************************************************************/
	private function displayLeftContent(){		
		if($_GET['gameID']&&$this->gameBool!=0&&($this->statusBool||$this->allow)){			
			if($_POST['confirm']){		//if delete comment confirmed	
					$this->result=$this->model->deleteComment();
					if($this->result['msg']=="Success"){
						unset($_GET['action']);
						unset($_GET['commentID']);
					}
			}		
			if($_POST['cancelComment']){			
					unset($_GET['action']);
					unset($_GET['commentID']);
					$gameID=$_GET['gameID'];
					$pageName="index.php?pageName=game&gameID=$gameID";
					header('Location: '.$pageName.'');
					exit;
			}				
			if($_POST['comment']){//if comment form has been submitted	
				$this->result=$this->model->processComment();
					unset($_GET['action']);
					unset($_GET['commentID']);
			}
			if($_POST['commentEdit']){
				$this->result=$this->model->processComment($_GET['commentID']);
				if($this->result['msg']=="Success"){
					unset($_GET['action']);
					unset($_GET['commentID']);	
				}		
			}
			$html='<div class="left"><div class="post"><div class="pageContent">'."\n";
			$larp=$this->generated->getGameFromID($_GET['gameID']);
			$html.=$this->generated->showGame($larp);			
			$html.=$this->displayComments();	
			if($_GET['action']!='edit'&&$_GET['action']!='delete'){			
				$html.=$this->showCommentForm();
			}
			$html.='<div class="clear"></div>'."\n";
			$html.='</div>'."\n";//end post
			$html.='</div>'."\n";//end left
		}else{
			$html.='<p class="note">This game does not exist, or is not public. Please visit the <a href="index.php?pageName=games">games</a> page for a list of the current games</p>'."\n";
			$html.='<br />'."\n";
			$html.='<div class="clear"></div>'."\n";
		}		
   	return $html;
	}//end displayLeftContent
	
	/**************************************************************************
	* Sets up the display for the messages left on games
	*************************************************************************/
	private function displayComments(){
		$html.='</div>'."\n".'</div>'."\n".'</div>'."\n".'</div>'."\n".'</div>'."\n";
		$html.='<div class="wrap">'."\n";
		$html.='<div class="messages">'."\n";
		$html.='<div class="h2"><h2>Messages</h2></div>'."\n";
		$html.='<div class="gameContent">'."\n";
		$html.='<ul class="gameComments">'."\n";
		if($_POST['confirm']&&$this->result['msg']=="Success"){			
			$html.='<li class="noInput">Comment successfully deleted! </li>'."\n";
		}
		$html.=$this->generated->showComments($this->result['msg']);
		$html.='</ul>'."\n";
		return $html;
	}//end displayComments
	
	/**********************************************************************
	 *Method processes any posted comments,
	 *then displays a form if a user is logged in,
	 *or a message in there is no logged in user
	 ********************************************************************/
	private function showCommentForm(){		
		$html.='<div class="clear"></div>'."\n";
		if(!$_SESSION['userID']){//If no logged in user: display this paragraph
			$html.='<div class="messageNoUser">'."\n";
			$html.='<p class="note">'."\n";
			$html.='<a href="index.php?pageName=login&amp;history=game&amp;gameID='.$_GET['gameID'].'">Login</a> or '."\n";
			$html.='<a href="index.php?pageName=register">Register</a> to leave your own comments'."\n";
			$html.='</p>'."\n";
			$html.='</div>'."\n";//end messageNoUser div	
		}else if(!$_GET['edit']||$_POST['cancel']){
			$html.=$this->displayForm();
		
		}//end if/else		
		return $html;
	}//end showCommentForm method
	
	/**********************************************************************************
	 *Comment form to display if there is a logged in user
	 ********************************************************************************/
	private function displayForm(){		
		$msgResult=$this->result;
		$this->result=$this->generated->getUserDetails($_SESSION['userID']);
		extract($this->result);
		if($_POST['comment']){
			extract($_POST);
		}		
		if($userID==0){
			$imgSrc='<img src="images/anon.jpg" alt="Deleted User">';
		}else{
			$imgSrc='<img src="users/'.strtolower($userName).'/'.$userPic.'" alt="'.$userName.'" />';		
		}
		$html.='<div id="commentForm">'."\n";
		$html.='<div class="leaveComment">'."\n";
		$html.='<h5>Leave a Comment</h5>'."\n";		
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form">'."\n";
		$html.='<div class="formContent">';
		$html.='<div class="imgWrap">';
		$html.=$imgSrc;
		$html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>';
		$html.='</div>';//end imgwrap div		
		$html.='<div class="info">';	
		$html.='<label for="commentTitle">Title</label>'."\n";
		$html.='<input type="text" name="commentTitle" value="" id="commentTitle">'."\n";	
		if($msgResult['msg']!='Success'){
			$html.='<div id="commentError">'."\n";
			$html.=$msgResult['msg']."\n";
			$html.='</div>'."\n";//end comment error div
		}		
		$html.='<div class="textbox">'."\n";
		$html.='<textarea rows="5" cols="50" name="commentText" id="commentText"></textarea>'."\n";
		$html.='</div>'."\n";//end textbox div
		$html.='<div class="clear"></div>';
		$html.='<input type="hidden" name="gameID" value="'.$_GET['gameID'].'" id="gameID">'."\n";
		$html.='<input type="hidden" name="userID" value="'.$_SESSION['userID'].'" id="userID">'."\n";
		$html.='<p class="submit"><input type="submit" name="comment" value="Submit Comment" id="cSubmit"></p>'."\n";
		$html.='</div>'."\n";//end info div
		$html.='</div>'."\n";	//end form content	
		$html.='</form>'."\n"; //end leave comment
		$html.='</div>'."\n"; //end commentForm	
		return $html;		
	}//end displayForm
	
	/*Method to display the right content*/
	private function displayRightContent(){
		$html='<div class="right cleared">'."\n";
		$html.=$this->generated->displaySearchBox();	
		$html.='<div id="pageNav">'."\n";
		$html.='<div class="h2"><h2>Roster of Games</h2></div>'."\n";
		$html.='<div class="rightContent">'."\n";
		$html.=$this->generated->displayGameSchedule();	
		$html.='</div>'."\n";
		$html.='</div>'."\n";		
		$html.='</div> <!-- end right div /-->'."\n";
		return $html;
	}//end displayRightBox	

}//end Play Class
?>