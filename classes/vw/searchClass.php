<?php
/***********************************************************************************************
 * Search class displays the html for the search page
 * Most search functionality takes place in the generate class
 ********************************************************************************************/
class Search extends View{   
	 private $model;
	 private $msg;
    private $generated;
    
    /*Main, and required, method for the search view class*/ 
	 protected function displayContent(){
		  $this->generated=new Generate();
		 
		  if($_POST['search']&&$_POST['searchTerm']!=null){    
			  $html.=$this->displaySearch($_POST['searchTerm']);         
		  }else{
			  $html.=$this->displayEmptySearch();         
		  }
		  $html.="</div>";  
		  $html.=$this->displayRightContent();
		  $html.='<div class="clear"></div>';
		 
		  return $html;
	 }//end displayContent
   
   /*Method to display the search results*/
    private function displaySearch($searchFor){
		  $html.='<div class="left">'."\n";
		  $html.='<div class="pageInfo">'."\n";
		  $html.='<div class="centre"><h2>Search Results For :: '.$searchFor.' </h2></div>'."\n";
		  $html.='<div class="centre">';
		  $html.=$this->displaySearchBox();
		  $html.='</div>';
		  $html.='</div>';
		  $searchFor=addslashes($searchFor);
		  $html.=$this->generated->showSearch($searchFor);	
		  return $html;
    }//end displaySearch
   
   /*Method to display an empty search result*/
    private function displayEmptySearch(){
		  $html.='<div class="left">'."\n";
		  $html.='<div class="pageInfo">';
		  $html.='<div class="centre"><h2>Search Results:</h2></div>'."\n";
		  $html.=$this->displaySearchBox();
		  $html.='</div>';
		  $html.='<p class="note">'."\n";
		  $html.='Use the search bar to find results'."\n";
		  $html.='</p>'."\n";
		  return $html;
    }//end displayemptySearch
   
    /*Method to display Teh search Bar*/
    private function displaySearchBox(){
		  $html='<div class="searchBar">'."\n";
		  $html.='<form action="index.php?pageName=search" method="post" id="pageSearchForm">'."\n";
		  $html.='<label for="pageSearch">Search</label>'."\n";
		  $html.='<input type="text" name="searchTerm" id="pageSearch" value=""/>'."\n";
		  $html.='<input type="submit" name="search" id="pageSubmit" value="Go" />'."\n";
		  $html.='</form>'."\n";
		  $html.='</div>'."\n"; //end div SearchBar
		  return $html;
    }//end displaySearchBox
	
	
	/********************************************************************************
	 *Displays any content to be shown on the right
	*******************************************************************************/
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
	 }  
   
}//end search class
?>