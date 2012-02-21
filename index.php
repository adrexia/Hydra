<?php
session_start();//Start the session
/*index.php runs all other classes*/
/*Classes to include*/
include_once 'classes/viewClass.php';
include_once 'classes/db/modelClass.php';
include_once 'classes/db/generateClass.php';
ini_set("display_errors", 0);
ini_set("log_errors", 1);
$page=new SelectPage;
$page->run();
/***************************************************************************************************
 *The SelectPage class has the job of finding out which page the user
 *has called and loading the appropriate helper classes to display the page.
*************************************************************************************************/
class SelectPage{	
	/*Method includes all needed files and generates pages*/
	public function run(){				
		/*Grab the page name from the GET array, and hold in $pageName variable.
		If there is no page name in the get array, set $pageName to home */
		if ($_GET['pageName']){		
			$pageName = $_GET['pageName'];		
			$check=strpos($pageName, "/");			
			/*if get array contains /, split at / & reload. This is so that links can be shared easily via twitter*/
			if($check==true){
				$catch=explode("/",  $_GET['pageName'] );
				$gameID=$catch[1];		
				header("Location:  index.php?pageName=game&$gameID"); /* Redirect browser */			
				exit;	//Make sure that code below does not get executed when we redirect.
			}					
		}
		else{
			$pageName = 'news'; //News is the main "home page" of the site
		}		
		$model = new Model;
		if ($pageName != 'logout') {
				$rs = $model->getPage($pageName);
		}		
		switch($pageName) { //switch pulls in the files for the given page
			case 'logout':	
			case 'login': include('classes/vw/loginClass.php');
				$view = new Login($rs, $model);
				break;
			case 'news': include('classes/vw/newsClass.php');
				$view = new News($rs);
				break;
			case 'register': include('classes/vw/registerClass.php');
				$view = new Register($rs, $model);
				break;
			case 'about': include('classes/vw/aboutClass.php');
				$view = new About($rs, $model);
				break;
			case 'admin': include('classes/vw/adminClass.php');
				$view = new Admin($rs, $model);
				break;	
			case 'contact': include('classes/vw/contactClass.php');
				$view = new Contact($rs, $model, $_GET['PID']);
				break;	
			case 'sitemap': include('classes/vw/sitemapClass.php');
				$view = new Sitemap($rs, $model);
				break;	
			case 'submitGame': include('classes/vw/submitGameClass.php');
				$view = new SubmitGame($rs, $model, $_GET['page']);
				break;	
			case 'game': include('classes/vw/gameClass.php');
				$view = new Game($rs, $model);
				break;
			case 'schedule': include('classes/vw/scheduleClass.php');
				$view = new Schedule($rs, $model);
				break;
			case 'profile': include('classes/vw/profileClass.php');
				$view = new Profile($rs, $model);
				break;	
			case 'details': include('classes/vw/detailsClass.php');
				$view = new Details($rs, $model);
				break;	
			case 'games': include('classes/vw/viewGamesClass.php');
				$view = new ViewGames($rs, $model);
				break;							
			case 'search': include('classes/vw/searchClass.php');
				$view = new Search($rs, $model);
				break;
		}
		echo $view->displayPage();	
	}//End Run Method

}//end SelectPage Class
?>