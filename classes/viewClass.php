<?php
/*********************************************************************************************************************
 * The ViewClass loads the html that will be presented to the user. 
 * It contains the following methods:

		public function displayPage()
		private function displayContentWrap()
		abstract protected function displayContent()	
		private function displayHeader()
		private function displayHtmlHeader()
		private function displayUserNav()
		private function displayLogin()
		private function displayBanner()
		private function displayHeaderContent()	
		private function displayFooter()
		private function displayAdminLinks()
		private function sendToLogin()
********************************************************************************************************************/

abstract class View{
	protected $rs;
	
	public function __construct($rs){
		$this->rs=$rs;
	}//end constructor
	
	/***************************************************************************
	 *	Method calls all neccessary methods to display a page
	***************************************************************************/
	public function displayPage(){
		$html=$this->displayHeader();
		$html.=$this->displayContentWrap();		
		$html.=$this->displayFooter();
		return $html;
	}//end display page method
	
	/**********************************************************************************
	 *	Method displays the breadcrumbs and headings of a page
	*********************************************************************************/
	private function displayContentWrap(){		
		$generated=new Generate();
		$crumbs=$this->rs['pagePath'];
		$crumbs=explode(">", $crumbs);
		if($_GET['pageName']=='game'){
				array_pop($crumbs);
				array_push($crumbs,'Games');
				$gameName=$generated->getGameNameFromID($_GET['gameID']);
				array_push($crumbs,$gameName);
		}
		$count=count($crumbs);
		for($i=0;$i<$count-1;$i++){	
			if($crumbs[$i]=="Home"){
				$pageName=$generated->getNameFromHeading("News");			
			}else{
				$pageName=$generated->getNameFromHeading($crumbs[$i]);						
			}			
			$breadcrumbs.='<a href="index.php?pageName='.$pageName.'">'.$crumbs[$i].'</a> &gt; '."\n";
		}
		$breadcrumbs.=$crumbs[$count-1];		
		$html='<div class="mainContent">'."\n";
		$html.='<div class="wrap">'."\n";
		$html.='<div class="pageHeading"><h2>'.$this->rs['pageHeading'].'</h2></div>'."\n";		
		$html.='<div id="breadcrumbs"><div class="bCrumbs">'.$breadcrumbs.'</div></div>'."\n";		
		$html.='<div class="content">';
		$html.=$this->displayContent();
		$html.='</div>';
		$html.='</div><!---end wrap Div/-->'."\n";
		$html.='</div><!---end mainContent Div/-->'."\n";
		$html.='<div class="clear"></div>'."\n";
		return $html;
	}//end displayContentWrap
	
	/*Method is used by all other viewClasses*/
	abstract protected function displayContent();	
	

	/***********************************************************************
	 *Method runs all needed header methods:
	 *html header, user navigation, banner
	 **********************************************************************/
	private function displayHeader(){
		$html=$this->displayHtmlHeader();
		$html.='<div id="header">'."\n";
		$html.=$this->displayUserNav();		
		$html.=$this->displayBanner();
		$html.='</div> <!--End header Div/-->'."\n";
		$html.='<div class="clear"></div>'."\n";
		return $html;
	}//end displayHeader class
	
	/*********************************************************************
	 * Method generates the non-visual html for the header
	 **********************************************************************/
	private function displayHtmlHeader(){		
		$html='<!doctype html">'."\n";
		$html.='<html>'."\n";
		$html.='<head>'."\n";
		$html.='<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />'."\n";
		$html.='<meta name="description" content="'.$this->rs['pageDescription'].'" />'."\n";
		$html.='<meta name="keywords" content="'.$this->rs['pageKeywords'].'" />'."\n";
		$html.='<meta name="author" content="Naomi Guyer, naomi.guyer@gmail.com" />'."\n";
		$html.='<link rel="stylesheet" type="text/css" href="css/reset.css" media="" />'."\n";
		$html.='<link rel="stylesheet" type="text/css" href="css/cmsMenu.css" media="" />'."\n";
		$html.='<link rel="stylesheet" type="text/css" href="css/style.css" media="" />'."\n";
		
		$html.='<!--[if lt IE 7]>'."\n";
		$html.='<link rel="stylesheet" type="text/css" href="css/ie.css" media="" />'."\n";
		$html.='<![endif]-->'."\n";
		$html.='<!--[if lte IE 8]>'."\n";
		$html.='<style type="text/css">';
		$html.='.triangle{border-left: 10px solid transparent; border-right: 10px solid transparent;border-bottom: 10px solid #40078C !important;}';	
		$html.='</style>'."\n";
		$html.='<![endif]-->'."\n";
		
		
		
		$html.='<link rel="alternate" type="application/rss+xml" title="Hydra Feed" href="rss.xml" />'."\n";
		$html.='<script src="js/jquery.js"></script>'."\n";
		$html.='<script src="js/jquery.asmselect.js"></script>';		
		$html.='<script src="js/impromptu.js"></script>'."\n";
		$html.='<script src="cms/parser_rules/advanced.js"></script>';
		$html.='<script src="cms/dist/wysihtml5-0.3.0.min.js"></script>';	
		$html.='<script src="js/script.js"></script>'."\n";
                $html.='<script src="js/cms.js"></script>'."\n";
		
		$pageName=$_GET['pageName'];
		if($pageName=='register'||$pageName=='submitGame'||$_GET['action']=='edit'){
				//$html.='<script type="text/javascript" src="js/formValidation.js"></script>';
				//	$html.="\n";
		}
		if($pageName=='register'){			
	//		$html.='<script type="text/javascript" src="js/sum.js"></script>';
		}
		$html.= '<title>'.$this->rs['pageTitle'].'</title>'."\n";
		$html.= '</head>'."\n";
		$html.= '<body>'."\n";
        $html.='<!--code here/-->';
		return $html;	
	}//end displayHtmlHeader method
	
	
		
	public function displayEdit(){
		$html='<div id="wysihtml5-toolbar" style="display: none;">';
		$html.='<a data-wysihtml5-command="bold"><strong>T</strong></a>';
		$html.='<a data-wysihtml5-command="italic"><em>T</em></a>';	  
		$html.='<a data-wysihtml5-command="createLink">K</a>';
		$html.='<div data-wysihtml5-dialog="createLink" style="display: none;">';
		$html.=' <label>';
		$html.=' Link:';
		$html.=' <input data-wysihtml5-dialog-field="href" value="http://" class="text">';
		$html.=' </label>';	
		$html.=' <a data-wysihtml5-dialog-action="save">OK</a> <a data-wysihtml5-dialog-action="cancel">Cancel</a>';
		$html.='</div>';

		$html.='<a data-wysihtml5-command="insertOrderedList">q</a>';
		$html.='<a data-wysihtml5-command="insertUnorderedList">p</a>';
		$html.='<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" class="plainFont">H3</a>';
		$html.='<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h4" class="plainFont">H4</a>';
		$html.='<a data-wysihtml5-action="change_view" title="Show HTML" class="action wysihtml5-action" href="javascript:;" unselectable="on">H</a>';
		$html.='<a data-wysihtml5-command="insertImage">I</a>';
		$html.='<div data-wysihtml5-dialog="insertImage" style="display: none;">';
  		$html.='<label>';
    	$html.='Image:';
    	$html.='<input data-wysihtml5-dialog-field="src" value="http://">';
  		$html.='</label>';
  		$html.='<a data-wysihtml5-dialog-action="save" class="plainFont">OK</a>';
  		$html.='<a data-wysihtml5-dialog-action="cancel" class="plainFont">Cancel</a>';
		$html.='</div>';
		
		//		$html.='<a data-wysihtml5-command="justifyLeft">n</a>';
		//$html.='<a data-wysihtml5-command="justifyCenter">`</a>';
		//$html.='<a data-wysihtml5-command="justifyRight">o</a>';
		
		
		
		
		
		
		
		
		$html.='</div>';
			
		return $html;
	}
	

	
	/*****************************************************************************
	* Method displays the user navigation tools depending on
	* whether the user is logged in or not
	******************************************************************************/
	private function displayUserNav(){
		$html='<div id="userNav">'."\n";
		$html.='<div class="wrap">'."\n";
		$html.='<div id="position">'."\n";
		$html.='<ul id="mainNav">'."\n";
		$html.=$this->displayAdminLinks(); //displays the links at the top
		$html.='</ul>'."\n";
		$html.=$this->displayLogin();					
		$html.='</div> <!--end position div/-->'."\n";
		$html.='</div> <!--end wrap div/-->'."\n";
		$html.='</div> <!--end userNav div/-->'."\n";	
		return $html;	
	}//end displayUserNav method
	
	/*********************************************************************************
	 * Method displays the login form if there is no logged in user
	 * or the user details if logged in, and the logout option
	 ********************************************************************************/
	private function displayLogin(){
		$model=new Model();
		$result=$model->checkUserSession();
		$pageName=$_GET['pageName'];
		if($pageName=="profile"){
			$pageName=$pageName.'&amp;userID='.$_GET['userID'];
		}elseif($pageName=='game'){
			$pageName=$pageName.'&amp;gameID='.$_GET['gameID'];
		}
		if(!$_SESSION['userID']){	
			if($_POST['login']){
				extract($_POST);
			}
					
			$html='<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'" id="loginForm">'."\n";//login form
			$html.='<span class="navLabel"><label for="uName">User</label></span>'."\n";
			$html.='<input type="text" id="uName" name="userName" value="'.$userName.'" />'."\n";
			$html.='<span class="navLabel"><label for="uPassword">Password</label></span>'."\n";
			$html.='<input type="password" id="uPassword" name="userPassword" value="" />'."\n";
			$html.='<input type="hidden" id="history" name="history" value="'.$pageName.'" />'."\n";//to redirect back to page user was at before login
			$html.='<input type="submit" value="Login" name="login" id="loginSubmit" />'."\n";
			$html.='</form>'."\n";
			if($result){
			$html.='<p id="loginError">'.$result.'</p>'."\n";
			}
		}else{
		
			$profile='profile&amp;userID='.$_SESSION['userID'];
			$html='<p class="login">Logged in as <a href="index.php?pageName='.$profile.'">'.$_SESSION['userName'].'</a> &bull; '."\n";
			$html.='<a href="index.php?pageName=logout&amp;history='.$pageName.'">Logout</a>? &bull;'."\n";
			//history used by logout to send user back to the page where the user came from
			$html.='<a href="index.php?pageName='.$profile.'">View Profile</a></p>'."\n";
			
			if($_SESSION['userType']=='mod'||$_SESSION['userType']=='su'){
			$html.='<p id="adminPanel"><a href="index.php?pageName=admin">View Hydra Administration</a></p>'."\n";
			
			}
		}//end session if/else		
		
		
		
		
		return $html;
	}//end displayLogin
	
	
	/************************************************************************************
	 * Method displays the visual header of the page
	 * Calls the displayHeaderDiagram method if it is the home page
	*************************************************************************************/
	private function displayBanner(){
		$html='<div id="banner">'."\n";
		$html.='<div class="wrap">'."\n";
		$html.='<div id="title">'."\n";
		$html.='<div id="titleText"><h1>HYDRA</h1> &bull;</div>'."\n";
		$html.='<div id="subTitleText"><h2>Live Action Roleplaying Convention</h2></div>'."\n";
		$html.='</div>'."\n";
		$html.=$this->displayHeaderContent();
		$html.='</div>'."\n";
		$html.='</div> <!--End Banner Div/-->'."\n";
		$html.='<div id="extraInfo">'."\n";
		$html.='<div class="wrap">'."\n";
		$html.='Brookfields, Wainuiomata'."\n";
		$html.='<img src="images/logo.png" width="130" height="117" id="logo" alt="hydra logo" />'."\n";
		$html.='Wellington, April 2012'."\n";
		$html.='</div>'."\n";
		$html.='</div><!--End extrainfo Div/-->'."\n";
		return $html;
	}//end displayBanner method
	
	
	/*****************************************************************************************
	 *	Method can be used to generate page specific header info.
	 *	At the moment is just includes a picture at the top of the page. '
	 *	Could be used to include a folder full of images to be randomly loaded
	*****************************************************************************************/
	private function displayHeaderContent(){
		
		$file = scandir("images/header");

		foreach($file as $key => $value) {
		
			 if(is_file($value)) {
			 $total++; // Counter
			 echo "$value<br />\n";
			 }
		
		}
		
		$randomImage=rand(1, $value);
		$html='<div id="bannorImage">'."\n";
		$html.='<img style="border-width: 0px;" src="images/header/'.$randomImage.'.jpg" alt="Larp"  />'."\n";
		$html.='</div>'."\n";
		return $html;		
	}//end displayHeaderContent method
	
	
	/****************************************************************************************************
	 * Method displays the page footer and finished the html for the page	 *
	****************************************************************************************************/
	private function displayFooter(){
			$html='<div id="footer">'."\n";
			$html.='<div class="wrap">'."\n";
			$html.='<div class="sub">'."\n";
			$html.='<ul id="subNav">'."\n";
			$html.='<li><a href="index.php?pageName=about">About</a> &bull;</li>'."\n";
			$html.='<li><a href="index.php?pageName=contact">Contact</a> &bull;</li>'."\n";
			$html.='<li><a href="index.php?pageName=sitemap">Site Map</a></li>'."\n";
			$html.='</ul>'."\n";
			$html.='<ul id="footNav">'."\n"; 
			$html.=$this->displayAdminLinks();
			$html.='</ul>'."\n";
			$html.='</div>'."\n";
			$html.='</div>'."\n";
			$html.='</div>'."\n";
			$html.='</body>'."\n";
			$html.='</html>'."\n";
		return $html;
	}//end displayFooter method
	
		
	/**************************************************************************************
	* Helper Method to show the admin links. Used by displayFooter
	* and displayUserNav
	*************************************************************************************/
	private function displayAdminLinks(){
		$pageNameArray = array('news','details','register','submitGame','games','schedule');  
		$linkNameArray = array('News','Details','Register','Run a Larp','Games','Schedule');	
		$numLinks = count($linkNameArray);			
		for($i=0;$i<$numLinks; $i++){//for all links in array
			if($_GET['pageName']==$pageNameArray[$i]||(!$_GET['pageName']&&$pageNameArray[$i]=='news')){
				$class="set";			
			}else{
				$class="unset";
			}
			$html.='<li class="'.$class.'"><a class="'.$pageNameArray[$i].'" href="index.php?pageName='.$pageNameArray[$i].'">'.$linkNameArray[$i].'</a></li>'."\n";
			if($i<$numLinks-1){ //if not the final link add divider
				$html.=' <li>&bull;</li>'."\n"; 
			}
			$html.="\n";
		}//end loop through array
		return $html;
	}//end displayAdminLinks

	/***************************************************************
	 *Helper method to send users to the login page
	***************************************************************/
	private function sendToLogin(){
		$_GET['pageName']='login';
		$select=new SelectPage();
		$select->run();
	}//end send to Login	
		

}//end view class
?>