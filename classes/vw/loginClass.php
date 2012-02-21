<?php
/*****************************************************************************
 *Login class helps with login
 * $_GET['history'] or $_POST['history'] contains users previous page
 * Need to use redirect and send user back to tehir page once logged in
******************************************************************************/
class Login extends View{
	private $model;
	private $generated;
	private $result;
	protected $rs;
	
	//Constructor
	public function __construct($rs, $model){
		$this->rs=$rs;
		$this->model=$model;
		$this->generated=new Generate;	
	}//end constructor
	
	/*Main method to display the page*/
	public function displayPage(){
		$this->result=$this->model->checkUserSession();
		$html=parent::displayPage();
		return $html;		
	}//end displayPage
	
	/*Method to display the content of the page*/
	protected function displayContent(){
		$html.=$this->displayRightContent();
		$html.='<div class="left"><div class="post"><div class="pageContent">';		
		if($this->result['message']){
			$html.= $this->result['message'];
		}else if(!$_SESSION['userName']||$this->result['logout']){
			$html.=$this->loginForm();
		}		
		$html.='</div><!-- end left div /-->';			
		$html.='<div class="clear"></div>'."\n";
		$html.='</div>'."\n";	
		return $html;
	}//end displayContent
	
	/*Method to display the login Form*/
	private function loginForm(){
		$html='<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post" id="lForm">'."\n";
		$html.='<div class="formShade"></div>'."\n";
		$html.='<ul class="form">'."\n";
		$html.='<li>'."\n";
		$html.='<label for="userName">User Name</label>'."\n";
		$html.='<input type="text" name="userName" value="" id="userName" />'."\n";
		$html.='</li>'."\n";
		$html.='<li>'."\n";
		$html.='<label for="userPassword">Password</label>'."\n";
		$html.='<input type="password" name="userPassword" value="" id="userPassword" />'."\n";
		$html.='</li>'."\n";
		$html.='<li class="submit">'."\n";
		$html.='<input type="submit" name="login" value="Login" id="pageSubmit" />'."\n";
		$html.='</li>'."\n";
		$html.='</ul>'."\n";
		$html.='<div class="clear"></div>'."\n";
		$html.='</form>'."\n";
		$html.= '<p class="loginError">'.$this->result['errorMessage'].'</p>'."\n";
		return $html;
	}//end loginForm		
		
	/*Method displays the right content for a page*/	
	private function displayRightContent(){
		$html='<div class="right">';
		$html.=$this->generated->displaySearchBox();	
		$html.='<div class="pageNav">';
		$html.='<div class="h2"><h2>Roster of Games</h2></div>';
		$html.='<div class="rightContent">';
		$html.=$this->generated->displayGameSchedule();	
		$html.='</div>';
		$html.='</div>';		  
		$html.='</div> <!-- end right div /-->';
		return $html;	 
	}//end displayRightContent
	
	
}//end Login Class
?>