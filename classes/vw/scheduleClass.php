<?php
/*****************************************************************************
 * Details class displays:
 * The details of the Convention
******************************************************************************/
class Schedule extends View{
	
   private $generated;
	private $allow;
	private $model;
	
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
	
	/********************************************************************************
	 * Method displays the schedule page
	 *******************************************************************************/
	private function displayLeftContent(){
		$html='<div class="left">'."\n";
		if($_SESSION['userType']&&$_SESSION['userType']!='user'){
			$html.='<h2 id="adminNav"><a href="index.php?pageName=schedule&amp;action=edit">+ Edit Schedule</a>'."\n";	
			$html.='</h2>'."\n";
		}
		if($_GET['action']&&$this->allow){
			if($_POST['cancel']){			
				unset($_GET['action']);
				unset($_GET['newsID']);
				header('Location: index.php?pageName=schedule');
				exit;
			}			
			if($_POST['schedule']){//if details form has been submitted		
				$this->result=$this->model->processUpdatePageContent('schedule');
				$this->msg=$this->result['msg'];
				if($this->msg=="Success"){
					unset($_GET['action']);					
					$html.='<p class="note">The schedule page has been successfully updated!</p>';
				}	
			}			
		}		
		$html.=$this->handleContents();
		$html.='<div class="space"></div>'."\n";
		$html.='</div><!-- end left div /-->'."\n";
		return $html;
	}//end display left content
	
	/*****************************************************************************
	 *Method handles access to the edit form for the page.
	 ***************************************************************************/
	private function handleContents(){
		if($this->allow&&$_GET['action']=='edit'){
			$html.=$this->showScheduleForm();		
		}else{
			$html.=$this->scheduleContent();	
		}
		return $html;
	}//end handleContents	
	
	/*Method to show schedule page content*/
	private function scheduleContent(){
		$html.='<div class="post"><div class="pageContent">'."\n";			
		$this->rs = $this->generated->getPage($_GET['pageName']); 
		$content=$this->rs['pageContent'];
		$content=$this->generated->stripHTMLTags($content);
		$replace= array('<h3>', '</h3>');
		$replaceWith=array('<div class="h3"><h3>', '</h3></div>');
		$content=str_replace($replace, $replaceWith, $content);
	
		if($content!=""||$content!=null){
			$html.=nl2br($content);
		}
		$html.='</div>'."\n".'</div>'."\n";
		return $html;
	}//end scheduleContent
	
	/*******************************************************************************
	 * Method shows the edit form for the schedule
	 ******************************************************************************/
	private function showScheduleForm(){
		if($_POST['schedule']){
			$html='<p class="note"><strong>'.$this->msg.'!</strong></note>'."\n";			
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
		$html.='<input type="submit" name="schedule" value="Update" id="schedulePost"></p>'."\n";
		$html.='</form>'."\n";
   	$html.='</div>'."\n";
		return $html;
	}//showScheduleForm	
	
	/*Method to display the right content*/
	private function displayRightContent(){
		$html='<div class="right">'."\n";
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

}//end About Class
?>