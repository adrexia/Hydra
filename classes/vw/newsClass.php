<?php
/*****************************************************************************************************************
 *News Class is responsible for the rendering of the front page of the website
 *Makes heavy use of the generate class
**************************************************************************************************************/
class News extends View{
	private $generated;
	private $model;
	private $allow;
	private $msg;
	
	/**Main method to display the page.*/
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
	
	/**************************************************************************************
	 *Method handles the processing of any submitted forms and runs
	 *the methods to display the main content of the news page
	 ************************************************************************************/
	private function displayLeftContent(){
		$html.='<div class="left">'."\n";		
		if($_SESSION['userType']&&$_SESSION['userType']!='user'){
			$html.='<h2 id="adminNav"><a href="index.php?pageName=news&amp;action=add">+ Add Post</a>'."\n";					
			$html.='</h2>'."\n";		
		}
		if($_GET['action']&&$this->allow){			
			if($_POST['confirm']){			
					$this->result=$this->model->removeNews($_GET['newsID']);
					unset($_GET['action']);
					unset($_GET['newsID']);				
					$html.='<p class="note">News Post Successfully deleted!</p>'."\n";		
					$html.='<div class="space"></div>'."\n";		
			}		
			if($_POST['cancel']){			
					unset($_GET['action']);
					unset($_GET['newsID']);
					header('Location: index.php');
					exit;
			}				
			if($_POST['news']){//if news form has been submitted	
				$this->result=$this->model->processNews();
				$this->msg=$this->result['msg'];
				if($this->msg=="Success"){
					$this->generateRSS();
					unset($_GET['action']);
					unset($_GET['newsID']);
					$html.='<p class="note">The news has been successfully updated</p>'."\n";		
						$html.='<div class="space"></div>'."\n";		
				}	
			}			
		}		
		$html.=$this->handleNews();					
		$html.='</div><!-- end left div /-->'."\n";		
		return $html;
	}//end displayLeftContent
	
	
	/****************************************************************************
	 *Method handles user actions and shows them the
	 *addnews/editnews/deltenews options if they have permission
	 *and have chosen these. Else shows the news posts
	 **************************************************************************/
	private function handleNews(){
		if($this->allow&&($_GET['action']=='add'||$_GET['action']=='edit')){
			$html.=$this->showNewsForm();			
		}
		if(!$_POST['news']&&$_GET['action']=='delete'&&$this->allow){//if comment is being deleted
				 $html=$this->generated->showDeleteNews();				 
		}else{
			$html.=$this->generated->displayNewsPosts();
		}
		return $html;
	}//end handleNews
	
	
	/******************************************************************************
	 *Method generates the form to add or edit a news post
	 *If editing a post, the form grabs the old data for editing.
	 *****************************************************************************/
	private function showNewsForm(){
		$oldInfo=$this->generated->getNewsPost($_GET['newsID']);
		if($oldInfo){
			extract($oldInfo);
		}
		if($_POST['news']){
			$html.='<p class="note"><strong>'.$this->msg.'</strong></note>'."\n";				
				extract($_POST);
		}
		if(!$userID){
			$userID=$_SESSION['userID'];
			$newsID=0;
		}
		//Form starts here
		$html.='<p class="note"><em>Note: You can use &lt;h3&gt; tags for headings,  &lt;b&gt; tags to make text bold, &lt;i&gt; tags to italicize text, and &lt;a&gt; tags for links</em></p>'."\n";		
		$html.='<div class="pageForm">'."\n";	
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form" />'."\n";
		$html.='<input type="hidden" name="userID" value="'.$userID.'" id="userID" />'."\n";	
		$html.='<input type="hidden" name="newsID" value="'.$newsID.'" id="newsID" />'."\n";	
		$html.='<div class="h3"><h3>'."\n";	
		$html.='<label for="newsTitle">Title: </label>'."\n";	
		$html.='<input type="text" name="newsTitle" value="'.htmlentities(stripslashes($newsTitle)).'" id="newsTitle" />'."\n";	
		$html.='</h3></div>'."\n";	
		$html.='<p><textarea name="newsText" id="newsText">'.$this->model->stripHTMLTags(stripslashes($newsText)).'</textarea></p>'."\n";		
		$html.='<p class="newsSubmit"><input type="submit" name="cancel" value="Cancel" id="newsCancel"><input type="submit" name="news" value="Post" id="newsPost"></p>'."\n";
		$html.='</form>'."\n";			
   	$html.='</div>'."\n";	
		return $html;
	}//end showNewsForm
	
	/***********************************************************************
	 *Method Generates and stores the XML for the RSS
	 *feed of updated news content
	 ********************************************************************/
	private function generateRSS(){
		$posts=$this->model->getNewsPosts();
		$currentHost=CURRENTHOST;
		$news=array();
		foreach($posts as $post){
			$newsDate=strtotime($post['newsDate']);
			if($newsDate<=time()){			
				$data=array(
					"title"=>$post['newsTitle'],
					"link"=>$currentHost.'index.php?pageName=news#'.$post['newsID'],
					"description"=>$post['newsText']
				);			
				array_push($news, $data);
			}
		}	
		$doc=new DOMDocument('1.0', 'UTF-8');
		$rss=$doc->createElement('rss');
		$rss->setAttribute('version','2.0');
		$doc->appendChild($rss);
		$channel=$doc->createElement('channel');
		$channelTitle=$doc->createElement('title', 'Hydra News');
		$channelLink=$doc->createElement('link', $currentHost);
		$channelDesc=$doc->createElement('description', 'News on the Hydra Larp Convention');
		$rss->appendChild($channel);
		$channel->appendChild($channelTitle);
		$channel->appendChild($channelLink);
		$channel->appendChild($channelDesc);		
		foreach($news as $post){					
			$item = $doc->createElement('item');
			$itemTitle = $doc->createElement('title', $post['title']);
			$itemLink = $doc->createElement('link', $post['link']);
			$itemDesc = $doc->createElement('description', $post['description']);
			$channel->appendChild($item);
			$item->appendChild($itemTitle);
			$item->appendChild($itemLink);
			$item->appendChild($itemDesc);		
		}		
		$doc->formatOutput = true;
		$str = $doc->saveXML();
		$file = fopen('rss.xml','w');
		fwrite($file, $str);
		fclose($file);
	}//end generateRSS
	
	/*Method to display the rightContent*/
	private function displayRightContent(){
			$html='<div class="right">'."\n";	
			$html.=$this->generated->displaySearchBox();	
			$html.='<div id="pageNav">'."\n";	
			$html.='<div class="h2"><h2>Archive</h2></div>'."\n";	
			$html.='<div class="rightContent">'."\n";	
			$html.='<ul class="rightNav">'."\n";	
			$html.=$this->generated->displayNewsLinks();			
			$html.='</ul>'."\n";	
			$html.='</div>'."\n";	
			$html.='</div>'."\n";	
			
			$html.='</div> <!-- end right div /-->'."\n";	
			return $html;
	}//end displayRightBox
	
	
}//end Home class
?>