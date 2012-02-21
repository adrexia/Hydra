<?php
/*****************************************************************************
 * About class displays:
 * About information
 * About Edit Form

******************************************************************************/
class About extends View{
	private $msg;	
	private $generated;
	private $model;
	
	/*Main display Method*/
	protected function displayContent(){
		if(isset($_SESSION['userType'])&&$_SESSION['userType']!='user'){
			$this->allow=true;
		}	
      $this->generated=new Generate();
		$this->model=new Model();    
		$html.=$this->displayRightContent();
		$html.=$this->displayLeftContent();
		return $html;
	}//end display Content
	
	/*Method deals with handling posted forms, and calls the method which handles content*/
	private function displayLeftContent(){
		$html='<div class="left">';			
		if($_SESSION['userType']&&$_SESSION['userType']!='user'){
			$html.='<h2 id="adminNav"><a href="index.php?pageName=about&amp;action=edit">+ Edit About</a>';			
			$html.='</h2>';
		}
		if($_GET['action']&&$this->allow){						
			if($_POST['cancel']){			
					unset($_GET['action']);
					unset($_GET['newsID']);
					header('Location: index.php?pageName=about');
					exit;
			}
			//if details form has been submitted		
			if($_POST['about']){
				$this->result=$this->model->processUpdatePageContent('about');
				$this->msg=$this->result['msg'];
				if($this->msg=="Success"){
					unset($_GET['action']);
					unset($_GET['newsID']);
					$html.='<p class="note">The about page has been successfully updated!</p>';
				}	
			}			
		}		
		$html.=$this->handleAbout();
		$html.='<div class="space"></div>';
		$html.='</div><!-- end left div /-->';
		return $html;
	}//end aboutHTML
	
	/*Method handles content and delivers either a form or the page information*/
	private function handleAbout(){
		if($this->allow&&$_GET['action']=='edit'){
			$html.=$this->showAboutForm();		
		}else{
			$html.=$this->aboutContent();		
		}
		return $html;
	}//end handleAbout	
	
	/*Method to show page edit form*/
	private function showAboutForm(){
		if($_POST['details']){
			$html.='<p class="note"><strong>'.$this->msg.'!</strong></note>';			
				extract($_POST);
		}
		$this->rs = $this->model->getPage($_GET['pageName']); 
		$content=$this->rs['pageContent'];
		$content=$this->generated->stripHTMLTags($content);	
		$html.='<p class="note"><em>Note: You can use &lt;h3&gt; tags for headings,  &lt;b&gt; tags to make text bold, &lt;i&gt; tags to italicize text, and &lt;a&gt; tags for links</em></p>';
		$html.='<div class="pageForm">';
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="cForm">';
		$html.='<textarea rows="5" cols="50" name="pageContent" id="aboutText" />';
		$html.=$content;
		$html.='</textarea>';		
		$html.='<p class="newsSubmit"><input type="submit" name="cancel" value="Cancel" id="newsCancel">';
		$html.='<input type="submit" name="about" value="Update" id="detailsPost"></p>'."\n";
		$html.='<div class="clear"></div>';
		$html.='</form></div>';
		return $html;		
	}//end about Form
	
	/*Method to show about page content*/
	private function aboutContent(){
		$html.='<div class="post"><div class="pageContent">';		
		$rs=$this->generated->getPage($_GET['pageName']);
		$content=$rs['pageContent'];
		$content=$this->generated->stripHTMLTags($content);
	
		if($content!=""||$content!=null){
			$html.=nl2br($content);
		}
		$html.='</p>';
		$html.='</div></div>';
			$html.='<div class="clear"></div>';
		return $html;
	}//end aboutContent
	
	/*Method to show edit options*/
	private function aboutOptions(){
		$html='</div>';		
		if($_SESSION['userID']=='1'&&!$_GET['edit']){
			$html.='<!--admin logged in/-->';
			$html.='<p class="opt">';
			$html.='<a href="index.php?pageName=about&amp;edit=true" class="highlight">edit page</a>';
			$html.='</p>';
		}		
		$html.='<div class="clear"></div>';
		$html.='</div>';
		$html.='</div>';
		$html.='</div>';
		return $html;
	}//end aboutOptions
	
	/*Method to display the right content*/
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
		$html.='</div>';
		$html.='<div class="rightQuote">';
		//Potential to make a quote database and use it to cycle through quotes at random
		$html.='<blockquote class="quote">Some quote should go here, but we don\'t have one yet</blockquote>';
		$html.='<blockquote class="author">- Anon</blockquote> 					';
		$html.='</div>';
		$html.='&nbsp;';*/
		/*$html.='<div class="rightImage">';
		$html.='<img src="images/baddreams.jpg" alt="Bad Dreams" />';
		$html.='<p>Bad Dreams, Chimera 2010</p>';
		$html.='</div>';*/
		$html.='</div> <!-- end right div /-->';
		return $html;
	}//end displayRightBox	
	
	
}//end About Class
?>