<?php
/*****************************************************************************
 * Details class displays:
 * The details of the Convention

******************************************************************************/
class Details extends View{
	private $allow;
   private $generated;
	private $model;
	private $msg;
	
   /*******************************************************************************
   * Main, and required, methods used to handle the content of
   * the tag page
   * ***************************************************************************/
	protected function displayContent(){
		if(isset($_SESSION['userType'])&&$_SESSION['userType']!='user'){
			$this->allow=true;
		}	
      $this->generated=new Generate();
		$this->model=new Model();    
		$html.=$this->displayRightContent();
		$html.=$this->displayLeftContent();
		return $html;
	}//end displayContent
	
	/******************************************************************************
	 *Method displays the main content of the page
	 ****************************************************************************/
	private function displayLeftContent(){
		$html='<div class="left">';			
		if($_SESSION['userType']&&$_SESSION['userType']!='user'){
			$html.='<h2 id="adminNav"><a href="index.php?pageName=details&amp;action=edit">+ Edit Details</a>';			
			$html.='</h2>';
		}
		if($_GET['action']&&$this->allow){		
				
			if($_POST['cancel']){			
					unset($_GET['action']);
					unset($_GET['newsID']);
					header('Location: index.php?pageName=details');
					exit;
			}
			//if details form has been submitted		
			if($_POST['details']){
				$this->result=$this->model->processUpdatePageContent('details');
				$this->msg=$this->result['msg'];
				if($this->msg=="Success"){
					unset($_GET['action']);
					$html.='<p class="note">The details page has been successfully updated!</p>';
				}	
			}			
		}		
		$html.=$this->handleDetails();	
		$html.='</div><!-- end left div /-->';
		return $html;
	}//end displayLeftContent
	
	/**************************************************************************
	 *Method handles access to the details form
	 ************************************************************************/
	private function handleDetails(){
		if($this->allow&&$_GET['action']=='edit'){
			$html.=$this->showDetailsForm();
		
		}else{
			$html.=$this->detailsContent();		
		}
		return $html;
	}//end handleDetails	
	
	/*Method to show about page content*/
	private function detailsContent(){
			$html.='<div class="post"><div class="pageContent">';			
			$this->rs = $this->model->getPage($_GET['pageName']); 
			$content=$this->rs['pageContent'];
			$content=$this->generated->stripHTMLTags($content);
			$replace= array('<h3>', '</h3>');
			$replaceWith=array('<div class="h3"><h3>', '</h3></div>');
			$content=str_replace($replace, $replaceWith, $content);
			if($content!=""||$content!=null){
				$html.=nl2br($content);
			}
			$html.='</div></div>';
			return $html;
	}//end detailsContent
	
	/*Method shows the details form*/
	private function showDetailsForm(){
		if($_POST['details']){
			$html.='<p class="note"><strong>'.$this->msg.'!</strong></note>';			
				extract($_POST);
		}
		$rs=$this->generated->getPage($_GET['pageName']);
		$content=$rs['pageContent'];
		$content=$this->generated->stripHTMLTags($content);
	 	  $html.='<p class="note"><em>Note: You can use &lt;h3&gt; tags for headings,  &lt;b&gt; tags to make text bold, &lt;i&gt; tags to italicize text, and &lt;a&gt; tags for links</em></p>'."\n";
		//Form goes here
		$html.='<div class="pageForm">'."\n";
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form" />'."\n";
		$html.='<p><textarea name="pageContent" id="pageContent">'.$content.'</textarea></p>'."\n";
		$html.='<p class="newsSubmit"><input type="submit" name="cancel" value="Cancel" id="newsCancel">'."\n";
		$html.='<input type="submit" name="details" value="Update" id="detailsPost"></p>'."\n";
		$html.='</form>'."\n";
   	$html.='</div>'."\n";
		return $html;
	}
	
	
	/*Method to display the right content*/
	private function displayRightContent(){
			$html='<div class="right">'."\n";
			$html.=$this->generated->displaySearchBox();	
			/*$html.='<div id="pageNav">';
			$html.='<div class="h2"><h2>Archive</h2></div>';
			$html.='<div class="rightContent">';
			$html.='<ul class="rightNav">';
			$html.=$this->generated->displayNewsLinks();			
			$html.='</ul>';
			$html.='</div>';
			$html.='</div>';*/
	
			$html.='</div> <!-- end right div /-->'."\n";
			return $html;
	}//end displayRightBox	

}//end About Class
?>