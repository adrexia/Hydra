<?php
/*****************************************************************************
 * ViewGames Calss displays the briefs of every accepted game
******************************************************************************/
class ViewGames extends View{
	
	private $generated;
	
   /*Main method to display the games page*/
	protected function displayContent(){
      $this->generated=new Generate();
		$html.=$this->displayRightContent();
		$html.='<div class="post">'."\n";
		$html.=$this->generated->displayGameBriefs();
		$html.='</div>'."\n";
		
   	return $html;
	}//end displayContent
	
	
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
   
	
	
}//end popular Class
?>