<?php
/*********************************************************************************************************
 *The Generate class is a model class which contains all the rules and processes that interact with the main database class
 *that result in the generation of large pieces of html. For the class that deals with non-html generation
 *see the "model" class. Generate contains the following methods:
	General		private function userCheck($userID, $userName, $userPic){
				public function displaySearchBox(){	
	News		public function displayNewsLinks($limit=0, $num=20)
				public function showNewsLink($link)			
				public function displayNewsPost($newsID)
				public function displayNewsPosts($limit=0, $num=20)
				public function showNewsPost($post)
	Games		public function displayGameSchedule()
				private function showGameSchedule($results)
				public function displayUserGames($userID=0)		
				private function showUserGame($result)
				public function showGame($larp)
				private function showCharactersForGame($characters)
				private function showPlayersForGame($gameID)
				private function showGameFooter($gameID)
				public function displayGameBriefs()
				public function displayUserProcess()
				public function displayGMProcess()
	Comments	public function showComments()
				private function commentPermission($result)
				private function commentsAsHtml($result)	
				public function showCommentEditForm($result, $imgsrc, $usersName)
				private function showDeleteComment($result, $imgsrc, $usersName)
				private function showComment($result, $imgsrc, $usersName)
				private function commentAdminFooter($commentID, $commentDate)		
				private function commentUserFooter($commentID, $commentDate, $msg)
				private function commentVisitorFooter()
	Search		public function showSearch($searchFor)	
				private function searchGameResults($searchFor, $userID)
				private function searchCommentResults($searchFor, $userID)
				private function searchUserResults($searchFor, $userID)
				private function searchPageResults($searchFor)
				private function displayCommentResults($resultsArray)
				private function displayUserResults($resultsArray)
				private function displayPageResults($results)
				private function gameResultsAsHtml($result)
				private function pageResultsAsHtml($result)
				private function commentResultsAsHtml($result)
				private function userResultsAsHtml($result)	
	Delete		public function removeGameData()
				public function deleteGame()
				public function deleteCharacters()
				public function showDeleteNews()
		
*********************************************************************************************************/
class Generate extends DataBase
{
   public function __construct(){
		parent::__construct(); //use constructor from dataBase Class
 	}
	
	/****************************************************************************
	 * Helper method to return imgsrc and userName
	****************************************************************************/
	private function userCheck($userID, $userName, $userPic){
		$userID=$this->checkUser($userID);
		$userCheck=array();
		if($userID==0||$userID==1){//set userPic
			$userCheck[0]='<img src="anon.jpg" alt="deleted user" />'; //imgsrc
			$userCheck[1]="Anon";//userName
		}else{
			$userCheck[0]='<img src="users/'.strtolower($userName).'/'.$userPic.'" alt="'.$userName.'" />';//img src
			$userCheck[1]=null;//userName
		}//end set picture		
		return $userCheck;
	}//end userCheck
	
	/***************************************************************
	 *Method shows the search box.
	 *Used in right navigation on every page
	 **************************************************************/
	public function displaySearchBox(){	
		$html.='<div id="search">'."\n";
		$html.='<div class="h2"><h2>Search</h2></div>'."\n";
		$html.='<div class="rightContent">'."\n";
		$html.='<form action="index.php?pageName=search" method="post" id="sForm">'."\n";//search form
		$html.='<ul id="searchForm">'."\n";
		$html.='<li><input type="search" id="searchTerm" name="searchTerm" value="" /></li>'."\n";
		$html.='<li><input type="submit" value="go" name="search" id="searchSubmit" /></li>'."\n";
		$html.='</ul>'."\n";
		$html.='</form>'."\n";
		$html.='</div>'."\n";
		$html.='</div>'."\n";
		$html.='&nbsp;';	
		return $html;
	}//end showSearchBox
	
	/************************************************************************
	 *Method gets and displays links to the top 20 news posts
	 *********************************************************************/
	public function displayNewsLinks($limit=0, $num=20){
		$newsLinks=$this->getNewsLinks($limit, $num);
		if(is_array($newsLinks)){
			foreach($newsLinks as $link){
					$html.=$this->showNewsLink($link);				
			}
		}
		return $html;
	}//end displayNewsLinks
	
	/************************************************************************
	 *Helper method to generate an individual link to a news item.
	 *Used on the main page under archive links
	 ***********************************************************************/
	public function showNewsLink($link){
		extract($link);
		$html.='<li><a href="#'.$newsID.'">'.htmlentities(stripslashes($newsTitle)).'</a></li>'."\n";
		return $html;
	}//end showNewsLink
	
	/***********************************************************************************************************
	* Method grabs the news posts from the database, and cycles through all posts
	**********************************************************************************************************/ 
	public function displayNewsPost($newsID){
		$news=$this->getNewsPost($newsID);
		if(is_array($news)){			
				$html.=$this->showNewsPost($news);		
		}
		return $html;
	}//end displayNewsPosts
		
	/**********************************************************************************************************
	* Method grabs the news posts from the database, and cycles through all posts
	* ********************************************************************************************************/ 
	public function displayNewsPosts($limit=0, $num=20){
		$news=$this->getNewsPosts($limit, $num);
		if(is_array($news)){
			foreach($news as $post){
				$html.=$this->showNewsPost($post);
			}
		}
		return $html;
	}//end displayNewsPosts
		
	/*****************************************************************************************************
	 * Method shows the news Posts 
	*****************************************************************************************************/
	public function showNewsPost($post){
		extract($post);
		$newsTitle=htmlentities(stripslashes($newsTitle),ENT_QUOTES);		
		$newsText=$this->stripHTMLTags(stripslashes($newsText));	
		//$newsText=htmlentities($newsText);
		$newsDate=strtotime($newsDate);
		if($newsDate>time()){//if news date is in the future (used for the About Post)
			$wrap='<div class="pageInfo">'."\n";
			if($_SESSION['userType']=="su"||$_SESSION['userType']=="mod"){
				$footer='<div class="postFooter"><a href="index.php?pageName=news&amp;newsID='.$newsID.'&amp;action=edit">Edit</a> '."\n";
				$footer.='| <a href="index.php?pageName=news&amp;newsID='.$newsID.'&amp;action=delete">Delete</a></div>'."\n";
			}else{
				$footer='<div class="postFooter"></div>'."\n";
			}	
		}else{
			$wrap='<div class="post">'."\n";
			$newsDate=date("jS F Y g:i a", $newsDate);
			if(!$_GET['action']){
				if($_SESSION['userType']=="su"||$_SESSION['userType']=="mod"){
					$footer='<div class="postFooter">'."\n";
					$footer.='<span class="left">'.$newsDate.'</span><a href="index.php?pageName=news&amp;newsID='.$newsID.'&amp;action=edit">Edit</a> '."\n";
					$footer.='| <a href="index.php?pageName=news&amp;newsID='.$newsID.'&amp;action=delete">Delete</a></div>'."\n";
				}else{
					$footer.='<div class="postFooter"><span class="left">'.$newsDate.'</span></div>'."\n";		
				}
			}
		}	
		$html.=$wrap;		
		$html.='<div class="h3"><h3><a name="'.$newsID.'">'.$newsTitle.'</a></h3>'."\n";
		$html.='<em class="author">posted by <a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></em></div>'."\n";
		$html.='<div class="newsContent">'."\n".'<p>'."\n";
		if(strlen($newsText)>0){
			$html.=nl2br($newsText);	//turns all \n into <br />
		}
		$html.='</p>'."\n".'</div><!-- end postContent div /-->'."\n";
		$html.=$footer;
		$html.='<div class="space"></div>'."\n".'</div><!-- post div /-->'."\n";
		return $html;
	}//end showNewsPost
	
	/****************************************************************************
	 *Method to display all accepted games sorted by round/slot
	 *1=Friday Night, 2= Saturday Morning, 3=Saturday Afternoon
	 *4=Saturday Evening, 5= Sunday Morning, 6=Sunday Afternoon
	 *****************************************************************************/
	public function displayGameSchedule(){ 
		$slotName=array("","Friday Night","Saturday Morning","Saturday Afternoon","Saturday Evening","Sunday Morning","Sunday Afternoon");
		for($i=1;$i<=6;$i++){		
			$results=$this->getGameBySlot($i);
			$html.='<h5>'.$slotName[$i].'</h5>'."\n";
			$html.='<ul>'."\n";
			$html.=$this->showGameSchedule($results);
			$html.='</ul>'."\n";
		}
		$html.='<p>&nbsp;</p>'."\n";
		return $html;
	}
	
	/*****************************************************************************
	 *Helper Method to show each game returned for each round
	 ***************************************************************************/
	private function showGameSchedule($results){
		if(is_array($results)){
			foreach($results as $result){
				extract($result);
				$html.='<li><a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a></li>'."\n";				
			}
		}
		return $html;
	}//end showGameSchedule
	
	
	
	/****************************************************************************
	 *Method to display the games a user has signed up to
	 *sorted by round/slot:
	 *1=Friday Night, 2= Saturday Morning, 3=Saturday Afternoon
	 *4=Saturday Evening, 5= Sunday Morning, 6=Sunday Afternoon
	 *****************************************************************************/
	public function displayUserGames($userID=0){		
	   if($userID==0){
         $userID=$_GET['userID'];
      }		  
      $results=$this->getUserGames($userID);
      $userName=$this->getUserFromID($_GET['userID']);          
	   if(is_array($results)){
			foreach ($results as $key=>$row) {
				$gameSlot[$key] = $row['gameSlot'];
			}
			array_multisort($gameSlot, SORT_ASC, $results, SORT_DESC, $results);  //sort by [gameSlot];
			foreach($results as $result){
				if($result&&$result['gameID']!=1){
					$html.=$this->showUserGame($result);
				}
			} 
		}else{
			$html.='<p>'.$userName.' has not signed up for any games yet. </p>'."\n";				
		}			
		return $html;
	}//displayUserGames
	
	/*****************************************************************************
	 *Method shows which games a user has signed up to,
	 *their preferences, and 
	 ***************************************************************************/
	private function showUserGame($result){		
		extract($result);  //$gameID, $gameName, $gameSlot, $userGamesStatus, $userGamesPref, $userGamesCharPref	
		$charSet=$this->getUserCharacters($_GET['userID'], $gameID);
		if(is_array($charSet)){
			extract($charSet);	 //$characterID, $characterName
		}
		$html='<div class="gameNav">'."\n";
		$html.='<h5 class="gameHeading"><a href="index.php?pageName=game&gameID='.$gameID.'">'.$gameName.'</a></h5>'."\n";
     	 $html.='<table>';
		$html.='<tr><td>Status: </td><td><em class="right">'.$userGamesStatus.'</em></td></tr>'."\n";
		$html.='<tr><td>Session: </td><td><em class="right">'.$gameSlot.'</em></td></tr>'."\n";
		$html.='<tr><td>Choice Num: </td><td><em class="right">'.$userGamesPref.'</em></td></tr>'."\n";
      if(!$userGamesCharPref){
         $userGamesCharPref='None Supplied';
      }
		if($_GET['userID']==$_SESSION['userID']||$_SESSION['userType']&&$_SESSION['userType']!='user'){
			$html.='<tr><td colspan="2">Casting Preferences:</td></tr><tr><td colspan="2" class="long"><em class="long">'.$userGamesCharPref.'</em></td></tr>'."\n";
		}
		if($characterName){
		//	$html.='<tr><td colspan="2">Character:</td></tr><tr><td colspan="2" class="long"><em class="long">'.$characterName.'</em></td></tr>'."\n";
		}
      $html.='</table>';         
		$html.='</div>'."\n";
		return $html;
	}//end showGameSchedule
	
	/***********************************************************************************************
	 *The Show Game method generates the information that has been
	 *submitted for a game
	***********************************************************************************************/
	public function showGame($larp){
		$slotName=array("No Preference","Friday Night","Saturday Morning","Saturday Afternoon","Saturday Evening","Sunday Morning","Sunday Afternoon");
		$slotValue=array("0","1","2","3","4","5","6"); 
		
	
		extract($larp);		
		if(!$gameID){
			$gameID=$_GET['gameID'];
		}
		$GMName=$this->getUserFromGame($gameID);
		$GMID=$this->getUserIDFromGame($gameID);
		$GMLink='<a href="index.php?pageName=profile&amp;userID='.$GMID.'">'.$GMName.'</a>'."\n";	
		if(!strlen($gameAuthor)>0){
			$gameAuthor=$this->getUserFullName($GMID);
		}
		$html.='<div class="pageContent">'."\n";
		$html.='<div class="gameDetails">'."\n";
		$html.='<div class="h3"><h3><a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a></h3>'."\n";
		$html.='<em class="author"> run by '.$GMLink.'</em></div>'."\n";
		$html.='<div class="gameContent">'."\n";
		$html.='<h4>Written by: '.nl2br(htmlentities(stripslashes($gameAuthor))).'</h4>'."\n";
		$html.='<p>'.nl2br($this->stripHTMLTags(stripslashes($gameDescription))).'</p>'."\n";
		$html.='<ul class="gameList">'."\n";
		if($_SESSION['userID']==$GMID||($_SESSION['userType']&&$_SESSION['userType']!='user')){
			$html.='<li><span class="label">Status:</span>'.$gameStatus.'</li>'."\n";
		}		
		$html.='<li><span class="label">Session:</span>'.$slotName[$gameSlot].'</li>'."\n";
		$html.='<li><span class="label">Number of Players:</span>'.$gameMaxPlayers.'</li>'."\n";
		if($gameRestriction){
			$html.='<li><span class="label">Restriction:</span>'.htmlentities(stripslashes($gameRestriction)).'</li>'."\n";
		}
		if($gameGenre){
			$html.='<li><span class="label">Genre:</span>'.htmlentities(stripslashes($gameGenre)).'</li>'."\n";
		}
		if($gameCostume){
			$html.='<li class="block"><span class="label">Costume:</span><br /></li>'."\n";
			$html.='<li class="longText">'.$this->stripHTMLTags(stripslashes($gameCostume)).'</li>'."\n";		
		}		
		$html.='</ul>'."\n";
		if($gameExtraInfo){
			$html.='<h5>Extra Info:</h5>'."\n";
			$html.='<p>'.$this->stripHTMLTags(stripslashes($gameExtraInfo)).'</p>'."\n";		
		}
		$html.='<h5>Characters</h5>'."\n";		
		$characters=$this->getCharactersFromID($gameID);//get characters by gameID
		if($characters){
			$html.=$this->showCharactersForGame($characters);
		}else{
			$html.='<span class="noInput">There have been no characters submitted for this game. </span>'."\n";			
		}
		//If GM or admin user: show players for this game
		if($_SESSION['userID']==$GMID||($_SESSION['userType']&&$_SESSION['userType']!='user')){
			$html.=$this->showPlayersForGame($gameID);//get players by gameID
		}
		$html.='</p>'."\n".'</div>'."\n".'</div>'."\n".'</div>'."\n";
		//If GM or admin user: show admin footer 
		if($_SESSION['userID']==$GMID||($_SESSION['userType']&&$_SESSION['userType']!='user')){
			$html.=$this->showGameFooter($gameID);
		}
		$html.='<div class="space"></div>'."\n";	
		return $html;
	}//end ShowGame
	
	/*************************************************************************************
	 *Method generates the HTML for the characters in a game.
	 *Characters are displayed in a table with their name, description,
	 *gender, and cassting status. If the character is cast they will have
	 *a link to the player who is playing the role
	 ************************************************************************************/
	private function showCharactersForGame($characters){
		$html.='<table>'."\n";	
		foreach($characters as $character){
			extract($character);
			$characterDescription=htmlentities(stripslashes($characterDescription));
			if($userID!=0){
				$characterName=htmlentities(stripslashes($characterName));
				$characterName='<a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$characterName.'</a>'."\n";	
			}
			if($bool){
				$alt=' class="alt"';
				$bool=false;
			}else{
				$alt=' class="alt2"';
				$bool=true;
			}			
			$html.='<tr'.$alt.'>'."\n";	
			$html.='<td class="bold">'."\n";	
			$html.=$characterName;
			$html.='</td>'."\n";	
			$html.='<td class="italic">'."\n";	
			$html.=$characterGender;
			$html.='</td>'."\n";	
			$html.='<td class="norm">'."\n";	
			$html.=$characterDescription;
			$html.='</td>'."\n";	
			$html.='<td class="italic">'."\n";	
			$html.=$characterStatus;
			$html.='</td>'."\n";		
			$html.='</tr>'."\n";					
		}
		$html.='</table>'."\n";		
		return $html;
	}//end showCharactersForGame
	
	
	/*****************************************************************************
	 * Method shows all the players who have been assigned
	 * to a game
	 ***************************************************************************/
	private function showPlayersForGame($gameID){
		$players=$this->getPlayersForGame($gameID);
		$html.='<h5>Players</h5>'."\n";	
		if($players){
			$html.='<ul>'."\n";	
			foreach($players as $player){
				$realName=$this->getUserFullName($player['userID']);
				$link='<strong><a href="index.php?pageName=profile&amp;userID='.$player['userID'].'">'.$player['userName'].'</strong></a>';
				$html.='<li>'.$link.' ('.$realName.')</li>'."\n";	
			}
			$html.='</ul>'."\n";	
			}else{
				$html.='<span class="noInput">There are no players assigned to this game yet. </span>'."\n";		
			}		
		return $html;
	}//end showPlayersForGame
	
	
	/***********************************************************************************************
	 *Method shows the footer of a game to those who have editing permissions
	 **********************************************************************************************/
	private function showGameFooter($gameID){
		$html.='<p class="postFooter">';
		$html.='<span class="left"><a href="index.php?pageName=submitGame&amp;gameID='.$gameID.'&amp;action=gamesEdit">Edit Larp</a> | '."\n";			
		$html.='<a href="index.php?pageName=submitGame&amp;gameID='.$gameID.'&amp;action=charactersEdit">Edit Characters</a></span> '."\n";	
		$html.='<a href="index.php?pageName=submitGame&amp;gameID='.$gameID.'&amp;action=gamesDelete">Delete Larp</a> | '."\n";	
		$html.='<a href="index.php?pageName=submitGame&amp;gameID='.$gameID.'&amp;action=charactersDelete">Delete Characters</a>'."\n";	
		return $html;
	}//end showGameFooter
	
	
	/*******************************************************************************
	* Method displays the accepted Game Briefs alphabetically.
	* Used on games page
	* ****************************************************************************/
	public function displayGameBriefs(){
		$games=$this->getGameBriefs();		
		if(is_array($games)){			
			foreach($games as $game){
				extract($game);
				$count=strlen($gameName);
			$gameName=substr($gameName, 0, 50);
			$newCount=strlen($gameName);		
			if($newCount<$count){
				$gameName=$gameName.'...';				
			}
				
				
				
				$GM=$this->getUserFromID($userID);
				$html.='<div class="pageContent">'."\n";	
				$html.='<div class="gameDetails">'."\n";	
				$html.='<div class="h3"><h3><a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a>'."\n";	
				$html.='</h3><em class="author">run by <a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$GM.'</a></em></div>'."\n";			
				$html.='<div class="gameContent">'."\n";	
				$html.='<h4>Written by '.$gameAuthor.'</h4>'."\n";				
				$html.='<p>'.nl2br($gameDescription)."\n";			
				$html.='</p>'."\n";	
				$html.='</div>'."\n";	//end gamecontent
				$html.='</div>'."\n";	//end gamedetails

				$html.='</div>'."\n";	//end pageContent
				$html.='<div class="postFooter">'."\n";	
				$html.='<a href="index.php?pageName=game&amp;gameID='.$gameID.'">View Game</a>'."\n";	
				$html.='</div>'."\n";
				$html.='<div class="space"></div>'."\n";
			}	
		}else{
			
				$html.='<div class="pageContent">'."\n";	
				$html.='<div class="gameDetails">'."\n";
				$html.='<p>Game details pending!</p>';
				$html.='<div class="clear"></div>';
				$html.='</div>'."\n";	//end game details
				$html.='</div>'."\n";	//end pageContent
				
				$html.='<div class="space"></div>'."\n";
			
			
		}
		return $html;	
	}//end displayGamesBriefs
	
	
	/*****************************************************************************
	 *Method to show the user where they are in the registration
	 *process
	 ***************************************************************************/
	public function displayUserProcess(){
		$userCompleted='<a href="index.php?pageName=register">Your current state</a>'."\n";
		if($_SESSION['userID']){			
			$userID=$_SESSION['userID'];
			$userDetails='<a href="index.php?pageName=register&amp;action=userEdit">You have created an Account <em class="right">[edit]</em></a>'."\n";
			if($this->checkUserInfo($userID)){
				$userReg='<a href="index.php?pageName=register&amp;action=regEdit">You have registered for the convention <em class="right">[edit]</em></a>'."\n";	
			}
			if($this->checkUserGames($userID)){
				$userGames='<a href="index.php?pageName=register&amp;action=gamesEdit">You have chosen games <em class="right">[edit]</em></a>';
				$userCompleted='<a href="index.php?pageName=register">View Submitted Details</a>'."\n";
			}		
		}	
		$html='<div class="gameNav">'."\n";		
		$html.='<h5>Personal Account</h5>'."\n";
		$html.='<p>'."\n";
		if($userDetails){
			$html.=$userDetails;
		}else{
			$html.='You do not currently have an account, or you are not logged in.';	
		}
		$html.='</p>'."\n";
		$html.='<h5>Registration</h5>'."\n";
		$html.='<p>'."\n";
		if($userReg){
			$html.=$userReg;
		}else{
			$html.='You have not completed your registration';	
		}
		$html.='</p>'."\n";
		$html.='<h5>Game Selection</h5>'."\n";
		$html.='<p>'."\n";
		if($userGames){
			$html.=$userGames;
		}else{
			$html.='You have not yet selected games';
		}
		$html.="\n".'</p>'."\n";
		$html.='<h5>Continue Registration</h5>'."\n";
		$html.='<p>'."\n";	
		$html.=$userCompleted;		
		$html.='</p>'."\n";			
		$html.='<br /><br /></div>'."\n";		
		return $html;
	}//end displayUserProcess
	
	/*****************************************************************************
	 *Method to show the user where they are in the Larp Submission process
	 ***************************************************************************/
	public function displayGMProcess(){
		$userRunningGame=$this->checkGM($_SESSION['userID']);
		if($userRunningGame){
			$addLarp='<a href="index.php?pageName=submitGame&amp;action=add">Add a Larp</a>'; //only stage a link			
			if($_GET['gameID']){
				$addCharacters= '<a href="index.php?pageName=submitGame&amp;gameID='.$_GET['gameID'].'&amp;action=charactersEdit">Add/Edit Characters</a>';		
			}else{
				$addCharacters= 'To Add/Edit Characters, First <a href="index.php?pageName=submitGame">Select a Game</a>';
			}
			$viewSubmitted='<a href="index.php?pageName=submitGame">View Your Games</a>';
		}else{
			$addLarp='<a href="index.php?pageName=submitGame">Add a Larp</a>'; //only stage a link
			$addCharacters= 'Add/Edit Characters';	
			$viewSubmitted='View your Games';
		}	
	
		$html='<div class="gameNav">'."\n";		
		$html.='<h5>Add a Larp</h5>'."\n";
		$html.='<p>'."\n";
		$html.=$addLarp;
		$html.='</p>'."\n";
		$html.='<h5>Add Characters</h5>'."\n";
		$html.='<p>'."\n";
		$html.=$addCharacters;
		$html.='</p>'."\n";
		$html.='<h5>View Your Games</h5>'."\n";
		$html.='<p>'."\n";
		$html.=$viewSubmitted;
		$html.="\n".'</p>'."\n";
				
		$html.='<br /><br /></div>'."\n";		
		return $html;
	}//end displayGMProcess
	
	
	/***********************************************************************************
	 * Pulls comments from database and displays them as html *
	***********************************************************************************/
	public function showComments($msg){
		$results=$this->getComments();
		$num=count($results);
		if($results){			
			for($i=0;$i<$num;$i++){
				$html.=$this->commentsAsHtml($results[$i], $msg);
			}
		}else{
			$html.='<li class="noInput">There are currently no messages</li>';			
		}
		return $html;
	}//end showComments	
	
	/****************************************************************************************************
	 Helper method to check if user has permission to edit or delete comment
	****************************************************************************************************/
	private function commentPermission($result){
		extract($result);
		$GMID=$this->getGMIDFromGame($_GET['gameID']);
		$allowType=array('su', 'mod');
		$allowUser=array($userID, $GMID);
		//if owner of game, or creater of comment, or moderator, or superuser
		if(in_array($_SESSION['userID'], $allowUser)||in_array($_SESSION['userType'], $allowType)){
			return true;
		}else{
			return false;
		}						
	}//end commentPermission
	
	/******************************************************************************************************
	* Method calls other methods which display comments as html in various states
	* (edit, delete, report, regular etc)
	*****************************************************************************************************/	
	private function commentsAsHtml($result, $msg){	
		if($result){
			extract($result);
			$allow=$this->commentPermission($result);
			$userCheck=$this->userCheck($userID, $userName, $userPic);
			$imgsrc=$userCheck[0];			
			if($userCheck[1]!=null){
				$userName=$userCheck[1];
			}
			if($_GET['action']=="edit"&&$_GET['commentID']==$commentID&&$allow){//if edit comment
				 $html=$this->showCommentEditForm($result, $imgsrc, $userName, $msg);
			}else if(!$_POST['comment']&&$_GET['action']=='delete'&&$_GET['commentID']==$commentID&&$allow){//if comment is being deleted
				 $html=$this->showDeleteComment($result, $imgsrc, $userName);				
			}else{
				if($_GET['action']=='report'&&$_GET['commentID']==$commentID){//report comment
					$model=new Model();
					$result=$model->reportJob("comment");
					$msg=$result['msg'];
				}else{
					$msg=null;
				}
				$html=$this->showComment($result, $imgsrc, $userName);
				if($allow){
					$html.=$this->commentAdminFooter($commentID, $commentDate);
				}else if($_SESSION['userID']){
					$html.=$this->commentUserFooter($commentID, $commentDate, $msg);
				}else{
					$html.=$this->commentVisitorFooter($commentDate);
				}
			}
		}		
		return $html;		
	}//end commentsAsHTML
	
	/**************************************************************************************
	* Shows the form used to edit comments
	************************************************************************************/
	public function showCommentEditForm($result, $imgsrc, $usersName, $msg){
		extract($result);
		$html.='</ul>'."\n";
		$html.='<div id="commentForm">'."\n";
		$html.='<div class="leaveComment">'."\n";
		$html.='<h5>Edit Comment</h5>'."\n";	
		$page=$_SERVER['REQUEST_URI'];
		$page=str_replace("&","&amp;",$page);
		$html.='<form action="'.$page.'" method="post" id="form">'."\n";
		$html.='<div class="formContent">'."\n";
		$html.='<div class="imgWrap">'."\n";
		$html.=$imgsrc;
		$html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>'."\n";
		$html.='</div>'."\n";
		$html.='<div class="info">'."\n";
		$html.='<label for="commentTitle">Title</label>'."\n";
		$html.='<input type="text" name="commentTitle" value="'.htmlentities(stripslashes($commentTitle),ENT_QUOTES).'" id="commentTitle" />'."\n";	
		if($msg!='Success'){
			$html.='<div id="commentError">'."\n";
			$html.=$msg."\n";
			$html.='</div>'."\n";
		}		
		$html.='<div class="textbox">'."\n";
		$html.='<textarea rows="5" cols="50" name="commentText" id="commentText">'."\n";
		$html.=htmlentities(stripslashes($commentText),ENT_QUOTES)."\n";
		$html.='</textarea>'."\n";
		$html.='</div>'."\n";
		$html.='<div class="clear"></div>'."\n";	
		$html.='<input type="hidden" name="gameID" value="'.$_GET['gameID'].'" id="gameID" />'."\n";
		$html.='<input type="hidden" name="userID" value="'.$_SESSION['userID'].'" id="userID" />'."\n";
		$html.='<p class="submit">'."\n";
		$html.='<input type="submit" name="commentEdit" value="Re-Submit Comment" id="cSubmit" /></p>'."\n";
		$html.='</div>'."\n".'</div>'."\n".'</form>'."\n".'</div>'."\n".'</div>'."\n";
		$html.='<ul class="gameComments">'."\n";
		return $html;
	}	//end showCommentEditForm
	
	/**************************************************************************************
	 *Method displays the comment to be deleted and asks user
	 *to confirm
	 *************************************************************************************/
	private function showDeleteComment($result, $imgsrc, $usersName){
		extract($result);
		$html='<li>'."\n";		
		$html.='<div class="commentTitle">'."\n";
		$html.='<h5>'.htmlentities(stripslashes($commentTitle),ENT_QUOTES).'</h5>'."\n";
		$html.='</div>'."\n";//end CommentTitle div		
		$html.='<div class="commentDetail">'."\n";
		$html.='<div class="imgWrap">'."\n";
		$html.=$imgsrc;
		$html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>'."\n";
		$html.='<div class="clear"></div>'."\n";
		$html.='</div>'."\n";//end imgWrap div		
		$html.='<div class="commentText">'."\n";
		$commentText=htmlentities(stripslashes($commentText),ENT_QUOTES);
		$html.="\n";
		if($commentText!=""||$commentText!=null){
			$html.=nl2br($commentText);	//turns all \n into <br />
		}
		$html.="\n";	
		$html.='</div>'."\n";//end comment Text div
		$html.='</div>'."\n";//end comment details Div
		$html.='<div id="delete">'."\n";
		$html.='<div class="postFooter">'."\n";
		$page=$_SERVER['REQUEST_URI'];
		$page=str_replace("&","&amp;",$page);
		$html.='<form action="'.$page.'" method="post" id="confirmDelete">'."\n";
		$html.='Are you sure you want to remove this message?'."\n";
		$html.='<input type="submit" name="confirm" value="Yes" id="confirm" />'."\n";
		$html.='<input type="submit" name="cancelComment" value="Cancel" id="cancel" />'."\n";
		$html.='</form>'."\n".'</div>'."\n".'</div>'."\n".'</li>'."\n";		
		return $html;
	}//end show Delete Comment
	
	/**************************************************************************************
	 *Method displays a single comment as HTML
	*************************************************************************************/
	private function showComment($result, $imgsrc, $usersName){
		extract($result);
		$html='<li>'."\n";
		$html.='<div class="commentTitle">'."\n";
		$html.='<h5>'.htmlentities(stripslashes($commentTitle),ENT_QUOTES).'</h5>'."\n";
		$html.='</div>'."\n";//end CommentTitle div
		$html.='<div class="commentDetail">'."\n";
		$html.='<div class="imgWrap">'."\n";
		$html.=$imgsrc;
		$html.='<p class="userName"><a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'</a></p>'."\n";
		$html.='<div class="clear"></div>'."\n";
		$html.='</div>'."\n";;//end imgWrap div		
		$html.='<div class="commentText">'."\n";
		$commentText=htmlentities(stripslashes($commentText),ENT_QUOTES);
		$html.="\n";
		if($commentText!=""||$commentText!=null){
			$html.=nl2br($commentText);	//turns all \n into <br />
		}
		$html.='</div>'."\n";//end comment Text div
		$html.='</div>'."\n";//end comment detials Div
		return $html; 
	}//end showComment
	
	/**************************************************************************************
	 *Method displays the comment footer with edit permissons 
	*************************************************************************************/
	private function commentAdminFooter($commentID, $commentDate){		
		$html='<!--this user made this comment or an admin is logged in/-->'."\n";
		$html.='<div class="postFooter">'."\n";
		$html.='<span class="left">'.$commentDate.'</span>'."\n";
		$html.='<a href="index.php?pageName=game&amp;gameID='.$_GET['gameID'].'&amp;action=edit&amp;commentID='.$commentID.'" class="highlight">';
		$html.='edit</a>'."\n";
		$html.='| <a href="index.php?pageName=game&amp;gameID='.$_GET['gameID'].'&amp;action=delete&amp;commentID='.$commentID.'" class="highlight">';
		$html.='delete</a></div>'."\n";
		$html.='</li>'."\n";
		return $html;
	}//end commentAdminFooter
	
	/**************************************************************************************
	 *Method displays the comment footer with user permissions
	*************************************************************************************/
	private function commentUserFooter($commentID, $commentDate, $msg){
		$html='<!--another user made this comment/-->'."\n";
		$html.='<div class="postFooter">'."\n";
		$html.='<span class="left">'.$commentDate.'</span>'."\n";	
		if($msg!=null){
			$html.='<span class="highlight">'."\n";
			$html.='<em>You have just reported this comment</em></span>'."\n";
		}else{
			$html.='<a href="index.php?pageName=game&amp;gameID='.$_GET['gameID'].'&amp;action=report&amp;commentID='.$commentID.'">'."\n";
			$html.='report</a>'."\n";
		}
		$html.='</div>'."\n";
		$html.='</li>'."\n";
		return $html;
	}//end commentUserFooter
	
	/**************************************************************************************
	 *Method displays the comment footer with no permissions
	*************************************************************************************/	
	private function commentVisitorFooter(){
		$history='game&amp;gameID='.$_GET['gameID'];
		$html='<!--no current user/-->'."\n";
		$html.='<div class="postFooter">'."\n";
		$html.='<span class="left"></span>'."\n";
		$html.='Is this your comment? <a href="index.php?pageName=login&amp;history='.$history.'">Login</a> to Edit or Delete it'."\n";
		$html.='</div>'."\n";
		$html.='</li>'."\n";		
		return $html;
	}//end commentVisitorFooter	
		
	/******************************************************************************************
	  * Method calls methods for searching and displaying
	  * Game, Comments, Profiles, and Pages on the search page
	 *******************************************************************************************/
	public function showSearch($searchFor){		
		$searchFor=strtolower($searchFor);
		$userID=$this->checkUserName($searchFor);
		$html=$this->searchGameResults($searchFor, $userID);
		$html.=$this->searchCommentResults($searchFor, $userID);		
		$html.=$this->searchPageResults($searchFor);
		$html.=$this->searchUserResults($searchFor, $userID);
      return $html;
   }//end showSearch
	
	private function searchGameResults($searchFor, $userID){		
		$html='<div class="h3"><h3 class="searchHeading">Game Results</h3></div>'."\n";
		$html.='<div class="pageContent">'."\n";
		$resultsArray=array();		
		$results=$this->getGameByName($searchFor);	//get game by name = search criteria	
		if($results!=null){
			array_push($resultsArray, $results);
		}				
		$count=count($resultsArray);
		if($count<1||!$resultsArray){		//if no results
			$html.='<p class="noSearch">'."\n";
			$html.='No Game results for your search'."\n";
			$html.='</p>'."\n";
		}else{ //turn results into html
			foreach($resultsArray as $results){
				$html.=$this->displayPageResults($results);
			}
		}
		$html.='</div>'."\n";
		return $html;
	}//end searchGameResults
	
	/****************************************************************************************************************
	 * Method returns links to game with comment
	****************************************************************************************************************/
	private function searchCommentResults($searchFor, $userID){
		$resultsArray=array(); //new array for comments	
      $html.='<div class="h3"><h3 class="searchHeading">Comment Results</h3></div>'."\n";
		$html.='<div class="pageContent">'."\n";
      $results=$this->getCommentsFromTitle(0, $searchFor);
      if($results!=null){
			array_push($resultsArray, $results);			
		}
		$results=$this->getCommentsFromContent(0, $searchFor);
      if($results!=null){
			array_push($resultsArray, $results);			
		}
		if($userID!=0){
			$results=$this->getCommentsByUser(0, $userID);		
			if($results!=null){
				array_push($resultsArray, $results);
			}
		}		
		$count=count($resultsArray);				
		if($count<1||!$resultsArray){	
			$html.='<p class="noSearch">'."\n";
			$html.='No Comment results for your search'."\n";
			$html.='</p>'."\n";
		}else{		
			$html.=$this->displayCommentResults($resultsArray);
		}
		$html.='</div>'."\n";
		return $html;
	}//end searchCommentResults
	
	/******************************************************************************************************
	 * Method finds all profile results and returns a link to a bio
	******************************************************************************************************/		
	private function searchUserResults($searchFor, $userID){
		$resultsArray=array(); //new array for user	
		$html.="\n";
      $html.='<div class="h3"><h3 class="searchHeading">Profile Results</h3></div>';
			$html.="\n";
		$html.='<div class="pageContent">';
		$html.="\n";
  		if($userID!=0){
			$results=$this->getUserDetails($userID);
			if($results!=null){
				array_push($resultsArray, $results);
			}
		}		
		$count=count($resultsArray);
		if($count<1||!$resultsArray){
			$html.='<p class="noSearch">';
			$html.="\n";
			$html.='No Profile results for your search';
			$html.="\n";
			$html.='</p>';
			$html.="\n";
		}else{
			$html.=$this->displayUserResults($resultsArray);				
		}
		$html.='</div>';
		return $html;
	}//end searchUserResults
	
	/******************************************************************************
	 * Method returns the page result from search criteria
	******************************************************************************/
	private function searchPageResults($searchFor){
      $html.='<div class="h3"><h3 class="searchHeading">Page Results</h3></div>'."\n";
		$html.='<div class="pageContent">'."\n";
 		$results=$this->getPageDetails(0,$searchFor);		
		$count=count($results);
		if($count<1||!$results){		//fi  no results
			$html.='<p class="noSearch">'."\n";
			$html.='No Page results match your search'."\n";
			$html.='</p>'."\n";
		}else{		
			$html.=$this->displayPageResults($results);	
		}
		$html.='</div>'."\n";
		return $html;
	}//end searchPageResults
	
	/*******************************************************************
	* Method displays comment results from search as html *
	******************************************************************/
	private function displayCommentResults($resultsArray){
		$html='<ul class="search">'."\n";
		$lastRecord=Array();
		foreach($resultsArray as $results){
			arsort($results);	//sort the array
			foreach($results as $result){
				if(array_diff($result, $lastRecord)){
				$html.=$this->commentResultsAsHTML($result);
				}
				$lastRecord=$result;
			}		
		}						
		$html.='</ul>'."\n";
		return $html;
	}//end displayCommentResults
	
	/*******************************************************************
	* Method displays user results from search as html *
	******************************************************************/
	private function displayUserResults($resultsArray){
		$html.='<ul class="search">'."\n";
		$lastRecord=Array();
		arsort($resultsArray);	
		foreach($resultsArray as $results){
			if(array_diff($results, $lastRecord)){
				$html.=$this->userResultsAsHTML($results);
			}
			$lastRecord=$results;
		}
		$html.='</ul>'."\n";
		return $html;
	}//end display User Results
	
	/*******************************************************************
	* Method displays page results from search as html *
	******************************************************************/
	private function displayPageResults($results){
		$html.='<ul class="search">'."\n";
		$lastRecord=Array();		
		arsort($results);
		$count=0;
			foreach($results as $result){
				if(array_diff($result, $lastRecord)){
					if($result['gameID']){
						$html.=$this->gameResultsAsHTML($result);
						$count++;
						
					}elseif($result['pageName']!="profile"||$result['pageName']!="game"){
						$html.=$this->pageResultsAsHTML($result);
						$count++;
					}
				}
				$lastRecord=$result;
			}
			if($count==0){
				$html.='<p class="noRealSearch">'."\n";
				$html.='No Page results match your search'."\n";
				$html.='</p>'."\n";
			}		
			$html.='</ul>'."\n";		
		return $html;
	}//end displayPageResults
	
	/*******************************************************************************************************
	 *Method generates html for page results. 
	********************************************************************************************************/
	private function gameResultsAsHtml($result){
		if($result){		
			extract($result);	
			$count=strlen($pageContent);
			$pageContent=substr($pageContent, 0, 300); //limit number of characters allowed
			$newCount=strlen($pageContent);
			if($newCount<$count){
				$pageContent=$pageContent.'...';				
			}
			$pageContent=strip_tags($pageContent);
			$pageContent=htmlentities(stripslashes($pageContent),ENT_QUOTES);
         $html='<li class="searchComment">'."\n";
			$html.='<h5>'."\n";	
			$html.='<a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$this->stripHTMLTags(stripslashes($gameName),ENT_QUOTES).'</a>'."\n";
			$html.='</h5>'."\n";
			$html.=$gameDescription;
			$html.='</p>'."\n";
			$html.='</li>'."\n";
		}else{
			$html="";		
		} 
		return $html;  
   }//end gameResultsAsHtml

	
	/*******************************************************************************************************
	 *Method generates html for page results. 
	********************************************************************************************************/
	private function pageResultsAsHtml($result){
		if($result){		
			extract($result);	
			$count=strlen($pageContent);
			$pageContent=substr($pageContent, 0, 300); //limit number of characters allowed
			$newCount=strlen($pageContent);
			if($newCount<$count){
				$pageContent=$pageContent.'...';				
			}
			$pageContent=strip_tags($pageContent);
			$pageContent=htmlentities(stripslashes($pageContent),ENT_QUOTES);
         $html='<li class="searchComment">'."\n";
			$html.='<h5>'."\n";
			$html.='<a href="index.php?pageName='.$pageName.'">'.$this->stripHTMLTags(stripslashes($pageHeading),ENT_QUOTES).'</a>'."\n";
			$html.='</h5>'."\n";
			$html.=$pageContent;
			$html.='</p>'."\n";
			$html.='<div class="searchCommentBottom">'."\n";
			$html.='<a href="index.php?pageName='.$pageName.'" class="highlight">'.$foot.'</a>'."\n";
			$html.='</div>'."\n";
			$html.='</li>'."\n";
		}
		return $html;  
   }//end pageResultsAsHtml
	
	/**************************************************************************************************************
	* Method generates comment results as html. 
	**************************************************************************************************************/
	private function commentResultsAsHtml($result){ 
		if($result){		
			extract($result);
			$count=strlen($commentText);
			$commentText=substr($commentText, 0, 300); //limit number of characters allowed
			$newCount=strlen($commentText);
			if($newCount<$count){
				$commentText=$commentText.'...';				
			}			
			$count=strlen($commentTitle);
			$commentTitle=substr($commentTitle, 0, 37); //limit number of characters allowed
			$newCount=strlen($commentTitle);
			if($newCount<$count){
				$commentTitle=$commentTitle.'...';				
			}			
         $html='<li>'."\n";
			$html.='<h5>'."\n";
			$html.='<a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.htmlentities(stripslashes($commentTitle),ENT_QUOTES).'</a>'."\n";
			$html.='</h5>'."\n";
			$html.='<p class="searchCommentText">'."\n";
			$html.=htmlentities(stripslashes($commentText),ENT_QUOTES)."\n";
			$html.='</p>'."\n";
			$html.='</li>'."\n";
		}
		return $html;  
   }//end commentResultsAsHtml
	
	/**************************************************************************************************
	 * Method returns profile search results as html.
	**************************************************************************************************/
	private function userResultsAsHtml($result){ 	
		if($result){		
			extract($result);
			$count=strlen($userBio);
			$userBio=substr($userBio, 0, 300); //limit number of characters allowed
			$newCount=strlen($userBio);
			if($newCount<$count){
				$userBio=$userBio.'...';				
			}	
         $html='<li class="profileSearch">'."\n";
			$html.='<h4>'."\n";
			$html.='<a href="index.php?pageName=profile&amp;userID='.$userID.'">'.$userName.'\'s Profile</a>'."\n";
			$html.='</h4>'."\n";
			$html.='<p class="searchCommentText">'."\n";
			$html.=htmlentities(stripslashes($userBio),ENT_QUOTES)."\n";
			$html.='</p>'."\n";		
			$html.='</li>'."\n";
		}
		return $html;  
   }//end  userResultsAsHtml
	
	
	/***********************************************************************************
	 *Method removes the data for a gameID supplied by the
	 *get array
	**********************************************************************************/
	public function removeGameData(){
		$action=$_GET['action'];
		$gameID=$_GET['gameID'];
		$gameID=$this->checkGame($gameID);		
		if($action=="gamesDelete"&&$gameID>0){
			 $html.=$this->deleteGame();
		}elseif($action="characterDelete"&&$gameID>0){
			 $html.=$this->deleteCharacters();		  
		}else{
		  $html.='<p class="note">There is no game to delete</p>';  		  
		}
		return $html;
	}//end remove Game Data	
		
	/**************************************************************************************
	 *Displays the confirmation form if a user has chosen to delete
	 *a game
	 ***********************************************************************************/
	public function deleteGame(){
		$html='<div class="h3Form"><h3>Delete Game?</h3></div>'."\n";
		$html.='<p class="note"><strong>Warning: </strong>'."\n";
		$html.='Deleting games is irreversible.'."\n";
		$html.=' It will remove all Characters related to the game, and may mean some users need to make alternative game choices.'."\n";
		$html.=' If you still plan to offer a game, you might wish to try'."\n";
		$html.='<a href="index.php?pageName=submitGame&amp;gameID='.$_GET['gameID'].'&amp;action=editGame">'."\n";
		$html.='editing the details of the game</a> instead.</p><br /><br />'."\n";
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="deleteForm">'."\n";
		$html.='<p class="centre"><strong>Are you sure you want to delete this game?</strong><br /><br />'."\n";		
		$html.='<input type="hidden" name="gameID" value="'.$_GET['gameID'].'" />'."\n";
		$html.='<input type="submit" name="cancel" value="No" id="cancel" />'."\n";
		$html.='<input type="submit" name="deleteGame" value="Yes, please delete this game" id="delete" />'."\n";
		$html.='</p></form>'."\n";
		return $html;	 
	}//end deleteGame
	
	/**************************************************************************************
	 *Displays the confirmation form if a user has chosen to delete
	 *characters
	 ***********************************************************************************/
	public function deleteCharacters(){
		$html='<div class="h3Form"><h3>Delete Characters?</h3></div>'."\n";
		$html.='<p class="note">'."\n";
		$html.='<strong>Warning:</strong> '."\n";
		$html.='Deleting your characters is irreversible, but you may re-add them manually. It will remove all characters associated with this game.'."\n";
		$html.='</p><br /><br />'."\n";
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="deleteForm">'."\n";
		$html.='<p class="centre"><strong>Are you sure you want to delete this account?</strong><br /><br />'."\n";
		$html.='<input type="hidden" name="gameID" value="'.$_GET['gameID'].'" />'."\n";
		$html.='<input type="submit" name="cancel" value="No" id="cancel" />'."\n";
		$html.='<input type="submit" name="deleteCharacters" value="Yes, please delete the characters for this game" id="delete" />'."\n";
		$html.='</p></form>'."\n";
		return $html;
	}//end delete Chasssracters
	
	/********************************************************************************
	 *Method shows the confrmation if a user has chosen to delete a news post
	 *******************************************************************************/
	public function showDeleteNews(){
		$html='<div class="h3Form"><h3>Delete News Post?</h3></div>'."\n";
		$html.='<div class="pageInfo">'."\n";		
		$html.=$this->displayNewsPost($_GET['newsID']);
		$html.='<p class="note"><strong>Warning: </strong>'."\n";		
		$html.='Deleting news posts is irreversible.'."\n";		
		$html.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="deleteForm">'."\n";
		$html.='<p class="centre"><strong>Are you sure you want to delete this post?</strong><br /><br />'."\n";		 
		$html.='<input type="hidden" name="newsID" value="'.$_GET['newsID'].'" />'."\n";		
		$html.='<input type="submit" name="cancel" value="No" id="cancel" />'."\n";		
		$html.='<input type="submit" name="confirm" value="Yes, please delete this post" id="delete" />'."\n";		
		$html.='</p></form>'."\n";		
		$html.='</div>'."\n";		
		$html.='<div class="clear"></div>'."\n";		
		return $html;	 
	}//end show DeleteNews
	

	
}//end generateClass
?>