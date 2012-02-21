<?php
/*****************************************************************************
 * The sitemap class displays:
 * A sitemap based on whether a user is logged in or not
******************************************************************************/
class Sitemap extends View{
	private $model;
	private $generated;
	
	/*Main, and required, method. Responsible for generating the sitemap page*/
	protected function displayContent(){
		$this->model=new Model();
		$this->generated=new Generate();
		$html.=$this->displayRightContent();		
		$html.=$this->displayLeftContent();	
		return $html;
	}//end displayContent
	
	/*************************************************************************
	 *Method calls the left contetn, including the sitemap
	 ***********************************************************************/
	private function displayLeftContent(){
			$html='<div class="left">';		
		$html.='<ul class="sitemap">'."\n";
		$html.=$this->displaySiteMap();
		$html.='</ul>'."\n";
		$html.='<div class="clear"></div>';
		$html.='</div>';
		return $html;
	}//end displayLeftContent	
	
	/*************************************************************************
	 *Method displays the right content/nav
	 ************************************************************************/
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
		
			$html.='</div> <!-- end right div /-->';
			return $html;		
		return $html;
	}//end display right content
	
	
	/*This method generates a sitemap from the arrays at the top of the method*/
	private function displaySiteMap(){
		$games=$this->model->getGameLinks();
		//Set up the arrays to use to show pages		
		$pageDepthOne=array('news','details','register','submitGame','games','schedule');  
		$linkDepthOne=array('News','Details','Register','Run a Larp','Games','Schedule');	
		$profile=null;
		if($_SESSION['userID']){//add pages accesible by logged in users
			$profile='profile&amp;userID='.$_SESSION['userID'];			
		}	
		$count=0;		
		foreach($pageDepthOne as $page){
			$html.='<li class="top">'."\n".'<a href="index.php?pageName='.$page.'">'.$linkDepthOne[$count].'</a>'."\n";
			$html.='</li>'."\n";	
			if($page=="register"&&$profile!=null){
				$html.='<li class="subli">'."\n".'<ul class="sub">'."\n";
				$html.='<li>'."\n".'<a href="index.php?pageName='.$profile.'">'.$_SESSION['userName'].'</a>'."\n".'</li>'."\n";
				$html.='</ul>'."\n".'</li>'."\n";		
			}elseif($page=="games"){
				$html.='<li class="subli">'."\n".'<ul class="subH">'."\n";
				if(is_array($games)){	
					foreach($games as $game){				
						extract($game);
						$html.='<li>'."\n".'<a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a>'."\n".'</li>'."\n";				
					}
				}else{
					$html.='<li></li>'."\n";
				}
				$html.='</ul>'."\n".'</li>'."\n";
			}
			$count++;
		}
		return $html;
	}//end display Site Map
	
}//end sitemap Class
?>