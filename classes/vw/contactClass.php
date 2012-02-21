<?php
/*****************************************************************************
 * Contact class displays:
 * The form to contact the site owner
 * Success method
******************************************************************************/
class Contact extends View{
	 private $model;
	 private $msg;
	 private $generated;
	
	/*Main display Method, creates and sets class variables and calls all other methods.*/
	 protected function displayContent(){
		  $this->msg=NULL;
		  $this->model=new Model();
		  $this->generated=new Generate(); 		  
		  if($_POST['contact']){//process form
			  $result=$this->model->processMail();
			  $this->msg=$result['msg'];
		  }
		  $html.=$this->displayRightContent();
		  $html.='<div class="left">';	 
		  if($this->msg=="Success"){
			  $html.=$this->mailSent();			
			  $html.="\n";
		  }else{					
			  $html.='<div class="formBox">'."\n";
			  $html.=$this->showForm();
			  $html.='</div>'."\n";	
		  }
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</div>'."\n";  
		  return $html;
	 }//end DisplayContent
	
	/*Shows the contact form*/
	private function showForm(){	
		$userName=$_SESSION['userName'];	
		$name=$this->model->getUserFullName($_SESSION['userID']);
		$email=$this->model->getUserEmail($_SESSION['userID']);
		  if($_POST['contact']){
				$html='<div class="h3"><h3>Message Failed to Send:</h3>'."\n";
				$html.='<p class="note">'.$this->msg.'</p>'."\n";
				extract($_POST);
		  }
		  $html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="mainForm">'."\n";
		  $html.='<div class="formShadeLonger"></div>'."\n";
		  $html.='<ul class="form">'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="name">Your Name *</label>'."\n";
		  $html.='<input type="text" value="'.htmlentities(stripslashes($name)).'" name="name" id="name" />'."\n";
		  $html.='<span class="formExtra">( * required )</span>'."\n";
		  $html.='</li>'."\n";		
		  $html.='<li>'."\n";
		  $html.='<label for="userName">User Name</label>'."\n";
		  $html.='<input type="text" value="'.htmlentities(stripslashes($userName)).'" name="userName" id="userName" />'."\n";
		  $html.='<span class="formExtra">(If applicable)</span>'."\n";		
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="email">Email *</label>'."\n";
		  $html.='<input type="text" value="'.htmlentities(stripslashes($email)).'" name="email" id="email" />'."\n";
		  $html.='<span class="formExtra">( * required )</span>'."\n";
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="subject">Subject *</label>'."\n";
		  $html.='<input type="text" value="'.htmlentities(stripslashes($subject)).'" name="subject" id="subject" />'."\n";
		  $html.='<span class="formExtra">(* required )</span>'."\n";	
		  $html.='</li>'."\n";	
		  $html.='<li class="longerCommentBox">'."\n";
		  $html.='<label for="message">Message *</label>'."\n";
		  $html.='<textarea rows="9" cols="50" name="message" id="message">'.htmlentities(stripslashes($message)).'</textarea>'."\n";
		  $html.='<span class="formExtra">( * required )</span>'."\n";
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<input type="submit" name="contact" value="Send Message" id="pageSubmit" />'."\n";	
		  $html.='</li>'."\n";
		  $html.='</ul>'."\n";
		  $html.='<p>&nbsp;</p>'."\n";
		  $html.='</form>'."\n";		
		  return $html;
	}//end show Form
	
	/*Show Success Method if message sent successfully*/
	private function mailSent(){		
		$html.='<div class="h3"><h3>Message Sent</h3></div>';
		$html.='<p class="note">Your Message has been sent to the website admin.';
		$html.=' You should receive a confirmation email at '.$_POST['email'].' shortly.</p>';
		return $html;		
	}//end mailSent
	
	/**********************************************************
	 *Method displays the right content
	 *******************************************************/
	 private function displayRightContent(){
		  $html='<div class="right">';
		  $html.=$this->generated->displaySearchBox();	
		  /*$html.='<div id="pageNav">';
		  $html.='<div class="h2"><h2>Archive</h2></div>';
		  $html.='<div class="rightContent">';
		  $html.='<ul class="rightNav">';
		  $html.=$this->generated->displayNewsLinks();			
		  $html.='</ul>';
		  $html.='</div>';
		  $html.='</div>';*/
		
		  /*$html.='<div class="rightImage">';
		  $html.='<img src="images/baddreams.jpg" alt="Bad Dreams" />';
		  $html.='<p>Bad Dreams, Chimera 2010</p>';
		  $html.='</div>';*/
		  $html.='</div> <!-- end right div /-->';
		  return $html;
	}
}//end Create Account Class
?>