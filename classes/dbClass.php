<?php
include_once("conf.php");
/***************************************************************************************************
The database class is the only class to interact directly with the database.
It contains a set of queries, a get Method, and a put method.
The Methods are as follows:
	Helper 			public function stripHTMLTags($items)
	Get				private function get($qry, $QRYID='')
	GetQry			public function getUserSession()
					public function getUserFromID($userID)
					public function getUserFromGame($gameID=0)
					public function getUserIDFromGame($gameID=0)
					public function getUserFullName($userID)
					public function getUserDetails($userID='')
					public function getUserGames($userID)
					public function getUserCharacters($userID, $gameID)
					public function getUserPassword($userID)
					public function getUserEmail($userID)
					public function getUserPic($userID)
					public function getUserName($userName)
					public function getPage($pageName)  
					public function getNameFromHeading($pageHeading)
					public function getPageContent($pageName)  
					public function getUserInfo($userID)
					public function getComments($num=0)
					public function getNewsPosts($limit=0, $num=20)
					public function getNewsPost($newsID)
					public function getNewsLinks($limit=0, $num=20)
					public function getCommentsByUser($num=0, $userID)
					public function getCommentsFromTitle($num=0, $commentTitle)
					public function getCommentsFromContent($num=0, $commentText)
					public function getGameBySlot($gameSlot)
					public function getGameByName($gameName)
					public function getGameLinks()
					public function getPageDetails($num=0, $word)
					public function getMaxPlayers($gameID)	
					public function getGMIDFromGame($gameID)
					public function getCharactersFromID($gameID)
					public function getPlayersForGame($gameID)
					public function getGamesFromGM($userID)
					public function getGames()
					public function getGameBriefs()
					public function getGameFromID($gameID)
					public function getGameNameFromID($gameID)
	CheckQry		public function checkUser($userID)
					public function checkUserName($userName)	
					public function checkUserGames($userID    
					public function checkUserInfo($userID)  
					public function checkInfoForUser($userID) 
					public function checkGame($gameID)    
					public function checkStatus($gameID)  
					public function checkGM($userID)
					public function checkCurrentGames($userID, $gameID)
	Put				public function put($qry)
	PutQry			public function putComment($commentID='')
					public function putReport($reportID)
					public function putContent($pageName)
					public function putProfile()
					public function putInfo($userID)
					public function putGame()
					public function putNews()
					public function putCharacters($gameID)
					public function putUserGames($userID, $gameID, $userGamesPref, $userGamesCharPref, $userGameStatus="pending")
	UpdateQry		public function updateGame()			
					public function updateInfo($userID)
					public function updateProfile($userID=0)      
					public function updateNews()
					public function updateInfoGameComments($infoGameComments, $userID)
	Remove			public function remove($qry)
	RemoveQry		public function removeComment($commentID)
					public function removeNews($newsID)
					public function removeUser($userID)
					public function removeUserGames($userID)
					public function removeUserGame($gameID, $userID)
					public function removeGameFromUserGames($gameID)
					public function removeCharactersByGameID($gameID)
					public function removeGameByID($gameID)
***************************************************************************************************/
class Database{
	
	private $db;
	
   /**********************************************************
   * Constructor establishes connection to database
   ************************************************************/
	public function __construct(){   
		try{
			$this->db = new mysqli(DBHOST,DBUSER,DBPASS,DBNAME); //from data in conf.php
			if(mysqli_connect_errno()) {
            throw new Exception ("Error Connecting to the database");
			}    
		}catch(Exception $e) {
			die($e->getMessage());
		}	
  	}//end constructor
   
   /*****************************************************************************************
   * Helper method. Used by other classes to allow bold, italic, or
   * img tags instead of htmlentities strip slashes
   ****************************************************************************************/
   public function stripHTMLTags($items){        
		$items=strip_tags($items,'<p><a><img><b><i><em></strong><h3><h4>');     
		return $items;
   }//end stripHTMLTags
   
  
   
   /*********************************************************************************************************************
    * Main get query, sends query to database and returns results given.
      * $QRYID is used to decide if the results needed are a 1 or 2 dimensional array.
      * if $QRYID is 1, the method returns a regular results array, to be used if the result set is a single row
      * if $QRYID is blank, it returns a 2d array, to be used if the result set is multiple rows
   *********************************************************************************************************************/
   private function get($qry, $QRYID=''){
      $rs=$this->db->query($qry);	//Result set from database query      
      if($QRYID==1){  
         if($rs){ //if result set exists return an array
            if($rs -> num_rows > 0){	
            	$result = $rs->fetch_assoc();
               return $result;			
            }else{
            	return false;
            }	
         }else{
            die ('Error executing database query '.$qry);
         }
      }else{//  retrieve info for multiple records
          $rs = $this->db->query($qry);		
         if($rs){
            if($rs -> num_rows > 0){	
            	$results = array();
            	while ($result = $rs->fetch_assoc()) {
               	$results[] = $result;
            	}//end while
               return $results;			
            }else{
				$msg = false;
				return $msg;
            }//end if/else	
         }else{
            echo ('Error executing query '.$qry);
         }//end if/else
      }	//end largest if/else    
   }//end get method
   
   //----------------------------------------------------------------------------------------------------------------------------------------------
   
   //The following methods all retrive data from the database using the above get method.
   
   //---------------------------------------------------------------------------------------------------------------------------------------------
   
   /*****************************************************************
    * Method checks hy_user and password with database. returns either the userdata or false
   ****************************************************************/
   public function getUserSession(){
		$userName=$_POST['userName'];
		$password=sha1($_POST['userPassword']."riotPolice");
		$qry="SELECT userID, userName, userPassword, userType FROM hy_user WHERE userName = '$userName' AND userPassword='$password'";
	   $results=$this->get($qry, 1);
     	return $results;
	}//end getUserSession    
  
   /*****************************************************************************************************
   * Method uses the userID in the get Array and returns the associated username
   *****************************************************************************************************/
   public function getUserFromID($userID){
      $qry = "SELECT userName FROM hy_user WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      return $results['userName']; 
   }//end getUserFrom Play
	
	/*****************************************************************************************************
   * Method uses a supplied gameID, or the one in the get array, and
   * returns the associated username
   *****************************************************************************************************/
   public function getUserFromGame($gameID=0){
		if($gameID==0){
			$gameID=$_GET['gameID'];
		}
      $qry = "SELECT userName FROM hy_games LEFT JOIN hy_user ON hy_games.userID=hy_user.userID WHERE gameID='$gameID'";
      $results=$this->get($qry, 1);
      return $results['userName']; 
   }//end getUserFromGame
	
	/*****************************************************************************************************
   * Method uses a supplied gameID, or the one in the get array, and
   * returns the associated userID
   *****************************************************************************************************/
	public function getUserIDFromGame($gameID=0){
		if($gameID==0){
			$gameID=$_GET['gameID'];
		}
      $qry = "SELECT hy_user.userID FROM hy_games LEFT JOIN hy_user ON hy_games.userID=hy_user.userID WHERE gameID='$gameID'";
      $results=$this->get($qry, 1);
      return $results['userID']; 
   }//end getUserIDFromPlay	
	
	/*****************************************************************************************************
   * Method takes a userID and returns the associated full name of the user
   *****************************************************************************************************/
   public function getUserFullName($userID){
      $qry = "SELECT userFullName FROM hy_user WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      return $results['userFullName']; 
   }//end getUserFullName   
  
   /**************************************************************************************
    * Method gets hy_user details from the get array or a passed value
    * Returns all userDetails
   *************************************************************************************/
   public function getUserDetails($userID=''){
      if($userID==''){
         $userID=$_GET['userID'];
      }
      $qry = "SELECT userID, userName, userFullName, userEmail, userPic, userBio FROM hy_user WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      return $results; 
    }//end getUserDetails   
     
   /**************************************************************************************
    * Method finds all games from a given userID
   *************************************************************************************/ 
   public function getUserGames($userID){
      $qry = "SELECT hy_usergames.gameID, gameName, gameSlot, userGamesStatus, userGamesPref, userGamesCharPref FROM hy_usergames LEFT JOIN hy_games ON hy_usergames.gameID=hy_games.gameID WHERE hy_usergames.userID='$userID' ORDER BY userGamesPref";
      $results=$this->get($qry);
      return $results; 
   } //end getUserGames

  /**************************************************************************************
    * Method returns the characters a user is playing in a specific game
    * Takes a userID and a gameID
   ************************************************************************************/ 
	public function getUserCharacters($userID, $gameID){
		$qry = "SELECT characterID, characterName FROM hy_characters WHERE userID='$userID' AND gameID='$gameID'";
      $results=$this->get($qry, 1);
      return $results; 	
	}//end getUserCharacters	 
    
   /**************************************************************************************
    * Method finds the password for a user, by ID. Used so that users do
    * not have to update their passwords when editing hy_user details
   *************************************************************************************/  
   public function getUserPassword($userID){
      $qry = "SELECT userPassword FROM hy_user WHERE userID='$userID'";
      $result=$this->get($qry, 1);
      $result=$result['userPassword'];
      return $result; 
   }//end getUserPassword
   
   /**************************************************************************************
    * Method finds the email for a user by ID
   *************************************************************************************/  
   public function getUserEmail($userID){
      $qry = "SELECT userEmail FROM hy_user WHERE userID='$userID'";
      $result=$this->get($qry, 1);
      $result=$result['userEmail'];
      return $result; 
   }//end getUserEmail
   
   /**************************************************************************************
    * Method finds the picture for a user by ID
   *************************************************************************************/  
   public function getUserPic($userID){
      $qry = "SELECT userPic FROM hy_user WHERE userID='$userID'";
      $result=$this->get($qry, 1);
      $result=$result['userPic'];
      return $result; 
   }//end getUserPic
   
   /*************************************************************************************
   * Method checks whether a username is in the database, and
   * returns true if a result exists
   *************************************************************************************/
   public function getUserName($userName){
      $qry = "SELECT userName FROM hy_user WHERE userName='$userName'";
      $result=$this->get($qry, 1);
      if($result){
         $result=true;
      }
      return $result; 
   }//end getUserName
	
   /*************************************************************************************
   * Method retrieves all page information based on pageName
   *************************************************************************************/
 	public function getPage($pageName){  
  		$qry = "SELECT pageName, pageTitle, pageHeading, pageKeywords, pageDescription, pageContent, pagePath FROM hy_pages WHERE pageName = '$pageName'";
		$results=$this->get($qry, 1);          
      return $results;  
	}//end getPage
	
	/******************************************************************************
	 *Method for getting the pageName from the Heading of a page
	 *Used for breadcrumbs
	 *****************************************************************************/
	public function getNameFromHeading($pageHeading){
		$qry = "SELECT pageName FROM hy_pages WHERE pageHeading = '$pageHeading'";
		$results=$this->get($qry, 1);
      return $results['pageName'];  		
	}//end getNameFromHeading	
	
	
   /**************************************************************************************
   * Method retrieves page content based on pageName.
   * Used by search
   *************************************************************************************/
   public function getPageContent($pageName){  
  		$qry = "SELECT pageContent FROM hy_pages WHERE pageName = '$pageName'";
		$results=$this->get($qry, 1);
      $results=$results['pageContent'];
      return $results;  
	}//end getpageContent
   
   /********************************************************************************************
    * Method grabs all details supplied in a user's registration
   ******************************************************************************************/
   public function getUserInfo($userID){
      $qry = "SELECT infoMemberShip, infoAttend, infoAccom, infoPlayWith, infoNotPlayWith, infoTransport, infoFood, infoComments FROM hy_info WHERE userID='$userID'";
      $results=$this->get($qry, 1);          
      return $results;  
   }//end getUserInfo
	
	 /********************************************************************************************
    * Method grabs all details supplied in a user's registration
   ******************************************************************************************/
   public function getRegistrations(){
      $qry = "SELECT hy_info.userID, userName, userFullName, userEmail, infoMemberShip, infoAttend, infoAccom, infoPlayWith, infoNotPlayWith, infoTransport, infoFood, infoComments FROM hy_info LEFT JOIN hy_user on hy_info.userID=hy_user.userID";
      $results=$this->get($qry);          
      return $results;  
   }//end getUserInfo
	
	//"SELECT userID, userName, userFullName, userEmail, userPic, userBio FROM hy_user WHERE userID='$userID'";
     
   /**********************************************************************************************
    *Method returns first 100 comments on a game from a given lndex or 0
   ***********************************************************************************************/
   public function getComments($num=0){
      $gameID=$_GET['gameID'];
      $qry = "SELECT commentID, commentText, commentTitle, commentDate, userName, hy_user.userID, userPic FROM hy_comment LEFT JOIN hy_user ON hy_comment.userID=hy_user.userID WHERE gameID='$gameID' ORDER BY commentID LIMIT $num, 100";
      $results=$this->get($qry);    
      return $results; 
    }//end getComments
	 
	/*********************************************************************
	  *Method returns 20 most recent news posts from the supplied limit
	********************************************************************/
	 public function getNewsPosts($limit=0, $num=20){
      $qry = "SELECT newsID, newsText, newsTitle, newsDate, hy_news.userID, userName FROM hy_news LEFT JOIN hy_user ON hy_news.userID=hy_user.userID ORDER BY -newsDate LIMIT $limit, $num";
      $results=$this->get($qry);    
      return $results; 
    }//end getComments
	 
	/**************************************************************************************
	 **Method returns a single news post from a supplied newsID
	***************************************************************************************/
	public function getNewsPost($newsID){
      $qry = "SELECT newsID, newsText, newsTitle, newsDate, hy_news.userID, userName FROM hy_news LEFT JOIN hy_user ON hy_news.userID=hy_user.userID WHERE newsID='$newsID'";
      $results=$this->get($qry, 1);   
      return $results; 
    }//end getNewsPost
	 
	/*********************************************************************
	  *Method returns 20 most recent news links from the supplied limit
	*********************************************************************/
	 public function getNewsLinks($limit=0, $num=20){
      $qry = "SELECT newsID, newsTitle, newsDate FROM hy_news ORDER BY -newsDate LIMIT $limit, $num";
      $results=$this->get($qry);
		return $results; 
    }//end getNewsLinks 
   
   /**********************************************************************************************
    *Method used by search to return 100 comments on a game,
    *from an index, by the userID
   ***********************************************************************************************/ 
   public function getCommentsByUser($num=0, $userID){
      $qry = "SELECT gameID, commentText, commentTitle, hy_user.userID, userName, userPic FROM hy_comment LEFT JOIN hy_user ON hy_comment.userID=hy_user.userID WHERE hy_user.userID='$userID' ORDER BY commentID LIMIT $num, 100";
      $results=$this->get($qry);    
      return $results; 
   }//end getCommentsByUser
    
   /**********************************************************************************************
    *Method used by search to return 100 comments on a game,
    *from an index, by the title
   ***********************************************************************************************/  
   public function getCommentsFromTitle($num=0, $commentTitle){
      $qry = "SELECT gameID, commentText, commentTitle, hy_user.userID, userName, userPic FROM hy_comment LEFT JOIN hy_user ON hy_comment.userID=hy_user.userID WHERE commentTitle LIKE '%$commentTitle%' ORDER BY commentID LIMIT $num, 100";
      $results=$this->get($qry);    
      return $results;  
   }//end getCommentsFromTitle
   
   /**********************************************************************************************
    *Method used by search, returns 100 comments on a game,
    *from an index, by the content
   ***********************************************************************************************/  
   public function getCommentsFromContent($num=0, $commentText){
      $qry = "SELECT gameID, commentText, commentTitle, hy_user.userID, userName, userPic FROM hy_comment LEFT JOIN hy_user ON hy_comment.userID=hy_user.userID WHERE commentText LIKE '%$commentText%' ORDER BY commentID LIMIT $num, 100";
      $results=$this->get($qry);    
      return $results;  
   }//end getCommentsFromContent
    
   /**********************************************************************************************
    *Method returns the accepted games when given a slot number
   ***********************************************************************************************/	
   public function getGameBySlot($gameSlot){
      $qry = "SELECT gameID, gameName FROM hy_games WHERE gameSlot='$gameSlot' AND gameStatus='accepted'";
      $result=$this->get($qry);    
      return $result;
   }//end getGameBySlot
	
	/**********************************************************************************************
    *Method returns the games when given a game Name
   ***********************************************************************************************/	
   public function getGameByName($gameName){
      $qry = "SELECT gameID, gameName, gameDescription FROM hy_games WHERE gameName LIKE '%$gameName%' AND gameStatus='accepted' ORDER BY gameID";
      $result=$this->get($qry);    
      return $result;
   }//end getGameByName
	
	 /**********************************************************************************************
    *Method returns the gameID and gameName of all accepted games.
    *These can be used to generate links to the games 
   ***********************************************************************************************/	
   public function getGameLinks(){
		//where gameStatus = accepted
      $qry = "SELECT gameID, gameName FROM hy_games WHERE gameStatus='accepted'";
      $result=$this->get($qry);    
      return $result;
   }//end getGameLinks
	   
   /****************************************************************************************************
   * Method used by search, grabs page details
   *****************************************************************************************************/
   public function getPageDetails($num=0, $word){
      $qry = "SELECT pageName, pageHeading, pageContent FROM hy_pages WHERE pageName LIKE '%$word%' OR pageTitle LIKE '%$word%' OR pageHeading LIKE '%$word%' OR pageKeywords LIKE '%$word%' OR pageDescription LIKE '%$word%' OR pageContent LIKE '%$word%' ORDER BY pageName LIMIT $num, 100"; 
      $results=$this->get($qry);    
      return $results;
   }//end getPageDetails  

   /**********************************************************************************************
    *Method returns the  maximum number of players a game can take
   ***********************************************************************************************/	
   public function getMaxPlayers($gameID){		
	   $qry = "SELECT gameMaxPlayers FROM hy_games WHERE gameID='$gameID'";
      $result=$this->get($qry, 1);
      return $result['gameMaxPlayers'];
   }//end getMaxPlayers
	
	
	/**********************************************************************************************
    *Method takes a gameID and returns the userID of the person who submited a game 
   ***********************************************************************************************/	
	public function getGMIDFromGame($gameID){
		$qry = "SELECT userID FROM hy_games WHERE gameID='$gameID'";
      $result=$this->get($qry, 1);
      return $result['userID'];		
	 }//end getGMIDFromGame
	 
	 /*********************************************************************
	  *Method returns the characters associated with a supplied gameID
	********************************************************************/
	public function getCharactersFromID($gameID){
      $qry = "SELECT characterName, characterGender, characterDescription, characterStatus, userID FROM hy_characters WHERE gameID='$gameID'";
      $results=$this->get($qry);    
      return $results; 
   }//end getComments
	 
	/*********************************************************************
	  *Method returns all users assigned to a game
	********************************************************************/
	public function getPlayersForGame($gameID){
      $qry = "SELECT hy_user.userID, userName FROM hy_usergames LEFT JOIN hy_user ON hy_usergames.userID=hy_user.userID WHERE gameID='$gameID' AND userGamesStatus='confirmed'";
      $results=$this->get($qry);    
      return $results; 
   }//end getPlayersForGame
	 
	/*********************************************************************
	  *Method returns all games submitted by a user
	********************************************************************/ 
	public function getGamesFromGM($userID){
		$qry = "SELECT gameID, gameName, gameDescription, gameCostume, gameMaxPlayers, gameRestriction, gameGenre, gameExtraInfo, gameStatus, gameDateUpdated, gameSlot, gameVenue, gameNumCast, gameAuthor FROM hy_games WHERE userID='$userID'";
      $results=$this->get($qry);    
      return $results;
	}//end getGamesFromGM
	
	/*********************************************************************
	  *Method returns all games 
	********************************************************************/ 
	public function getGames(){
		$qry = "SELECT gameID, gameName, gameDescription, gameCostume, gameMaxPlayers, gameRestriction, gameGenre, gameExtraInfo, gameStatus, gameDateUpdated, gameSlot, gameVenue, gameNumCast, gameAuthor FROM hy_games ORDER BY -gameDateUpdated";
      $results=$this->get($qry);    
      return $results;
	}//end getGames
	
	/*********************************************************************
	  *Method returns data for all accepted games
	********************************************************************/
	public function getGameBriefs(){
		$qry = "SELECT gameID, gameName, gameDescription, gameAuthor, userID FROM hy_games WHERE gameStatus='accepted' ORDER BY gameName";
      $results=$this->get($qry);    
      return $results;
	}//end getGameBriefs
	 
	/*********************************************************************
	  *Method returns all game data when supplied with the gameID
	********************************************************************/
	public function getGameFromID($gameID){
		$qry = "SELECT gameName, gameDescription, gameCostume, gameMaxPlayers, gameRestriction, gameGenre, gameExtraInfo, gameStatus, gameDateUpdated, gameSlot, gameVenue, gameNumCast, gameAuthor FROM hy_games WHERE gameID='$gameID'";
      $results=$this->get($qry, 1);    
      return $results;
	 }//end getGameFromID
	
   /**********************************************************************************************
    *Method returns the game name when supplied with the gameID
   ***********************************************************************************************/	
   public function getGameNameFromID($gameID){
	   $qry = "SELECT gameName FROM hy_games WHERE gameID='$gameID'";
      $result=$this->get($qry, 1);    
      return $result['gameName'];
   }//end getGameNameFromID


 /****************************************************************************************************
   * Method uses the userID to test if the user still exists. Returns 0 if not
   ***************************************************************************************************/
   public function checkUser($userID){
      $qry = "SELECT userID FROM hy_user WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      if(!$results){
        return 0;         
      }
      return $results['userID']; 
   }//end checkUser Play
   
   /******************************************************************************************************
   * Method uses the userName to test if the user still exists. Returns 0 if not
   ******************************************************************************************************/
   public function checkUserName($userName){	
      $qry = "SELECT userID FROM hy_user WHERE userName='$userName'";
      $results=$this->get($qry, 1);      
      if(!$results){
        return 0;         
      }
      return $results['userID']; 
   }//end checkUserName
	
	/****************************************************************************************************
   * Method uses the userID to test if the user has selected games.
   * Returns true or false
   ***************************************************************************************************/
   public function checkUserGames($userID){    
      $qry = "SELECT userID FROM hy_usergames WHERE userID='$userID'";
      $results=$this->get($qry);
      if(!$results){
        return false;         
      }else{
			return true;
		}      
   }//end checkUser Games
	
	/****************************************************************************************************
   * Method uses the userID to test if the user has registered. Return true or false
   ***************************************************************************************************/
   public function checkUserInfo($userID){    
      $qry = "SELECT userID FROM hy_info WHERE userID='$userID'";
      $results=$this->get($qry);
      if(!$results){
        return false;         
      }else{
			return true;
		}      
   }//end checkUserInfo
	
	/****************************************************************************************************
   * Method uses the userID to test if a user has submitted a current registration.
   * Returns false or the userID
   * NOTE: Find out where this is used and why checkUserInfo isn't used instead!
   ***************************************************************************************************/
   public function checkInfoForUser($userID){    
      $qry = "SELECT userID FROM hy_info WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      if(!$results){
        return false;         
      }
      return $results['userID']; 
   }//end checkInfoForUser	
   
   /******************************************************************************************************
   * Method uses the gameID to test if the user who created a game still exists
   ******************************************************************************************************/
   public function checkGame($gameID){    
      $qry = "SELECT gameID FROM hy_games WHERE gameID='$gameID'";
      $results=$this->get($qry, 1);
      if(!$results){
        return 0;         
      }
      return $results['gameID']; 
   }//end checkGame
	
	/******************************************************************************************************
   * Method uses the gameID and finds whether the game is accepted or not
   ******************************************************************************************************/
   public function checkStatus($gameID){    
      $qry = "SELECT gameStatus FROM hy_games WHERE gameID='$gameID'";
      $result=$this->get($qry, 1);
      if(!$result){
        return 0;         
      }elseif($result['gameStatus']=='accepted'){
			return true;
		}else{
			return false;
		}
   }//end checkStatus
	
    /***************************************************************************************************
    *Method checks to see if a user has submitted games to run
   ****************************************************************************************************/   
   public function checkGM($userID){
      $qry = "SELECT userID FROM hy_games WHERE userID='$userID'";
      $results=$this->get($qry, 1);
      if(!$results){
        return false;         
      }else{
			return true; 
		}      
   }//end checkGM     
   
	/********************************************************************************************************
    *Method checks whether a user has signed up to a specific game
   *********************************************************************************************************/
	public function checkCurrentGames($userID, $gameID){
		$qry = "SELECT userID, gameID FROM hy_usergames WHERE userID='$userID' AND gameID='$gameID'";
      $results=$this->get($qry);
      if($results){
         return true;
      }
		return false;		
	}//end checkCurrentGames
	
	
	/********************************************************************************************************/
  /*********************************************************************************************************
   *  Main put query, writes query data to database and returns a success
   *  message or false. This method is used by all following methods in this class.
   *******************************************************************************************************/
  	/********************************************************************************************************/
   public function put($qry){
      $rs=$this->db->query($qry); //Result set from database query      
      if($rs){
			$result['RID']=$this->db->insert_id;
			$result['msg']="Success";
			return $result;			
		}else{
			echo "Error inserting record: ".$qry;
			return false;
		} 
   }//end put method
   
   
   //----------------------------------------------------------------------------------------------------------------------------------------------
   
   //The following methods write data to the database using the above put method.
   
   //---------------------------------------------------------------------------------------------------------------------------------------------
       
   /******************************************************************************************************
    *Method sanatizes user input and writes a comment to the database
   *******************************************************************************************************/
   public function putComment($commentID=''){
		extract($_POST);    
		if(!get_magic_quotes_gpc()){
			$commentTitle=$this->db->real_escape_string($commentTitle);
			$commentText=$this->db->real_escape_string($commentText);
		}
      if($commentID!=''){        
         $qry = "UPDATE hy_comment SET commentTitle='$commentTitle', commentText='$commentText' WHERE commentID='$commentID'";         
      }else{
      	$qry = "INSERT INTO hy_comment VALUES ('$commentID', '$commentTitle', '$commentText', '$userID', CURRENT_TIMESTAMP, '$gameID')";
      }
		$results=$this->put($qry);      
      return $results;
   }//end putComment   
   
   /********************************************************************************************************
    *Method writes data to the hy_jobs table
   *********************************************************************************************************/
   public function putReport($reportID){
      $userID=$_SESSION['userID'];
      if($_GET['pageName']=="profile"){
         $jobType='profile';         
      }else{
         $jobType='hy_comment';
      }    
      $qry="INSERT INTO hy_job VALUES ('', '$reportID', '$userID', CURRENT_TIMESTAMP, 'incomplete', '$jobType')";
      $results=$this->put($qry);
      return $results;
   }//end putReport
        
   /********************************************************************************************************
    *Method writes data to hy_pages table
   *********************************************************************************************************/
   public function putContent($pageName){
      extract($_POST);
      if(!get_magic_quotes_gpc()){
			$pageContent=$this->db->real_escape_string($pageContent);
      }
      $qry = "UPDATE hy_pages SET pageContent='$pageContent' WHERE pageName='$pageName'";  
      $results=$this->put($qry);
      return $results;
   }//end putContent
   
   /********************************************************************************************************
    *Method creates a profile/entry in the hy_user table
   *********************************************************************************************************/
   public function putProfile(){
		$infoPrice=0;
      extract($_POST);
      if($userPic==null){         
          $userPic="user.png";
      }		
		if(!get_magic_quotes_gpc()){
			$userFullName=$this->db->real_escape_string($userFullName);
			$userName=$this->db->real_escape_string($userName);
		}		
      $userBio="This user has not yet added a bio.";
      $userPassword=sha1($_POST['userPassword']."riotPolice");
      $qry="INSERT INTO hy_user VALUES ('', '$userName', '$userFullName', '$userPassword', '$userEmail', 'user', '$userPic', '$userBio')";
      $results=$this->put($qry);
		return $results['RID'];
   } //end putProfile
	
	/********************************************************************************************************
    *Method writes the users registration details to the info table
   *********************************************************************************************************/
	public function putInfo($userID){
		$uID=$userID;
		extract($_POST);
		$userID=$uID;
		if(!get_magic_quotes_gpc()){
			$infoFoodExtra=$this->db->real_escape_string($infoFoodExtra);
			$infoPlayWith=$this->db->real_escape_string($infoPlayWith);
			$infoNotPlayWith=$this->db->real_escape_string($infoNotPlayWith);
			$infoComments=$this->db->real_escape_string($infoComments);
      }
		if(strlen($infoFoodExtra)>0){
		$infoFood=$infoFood.':'.$infoFoodExtra;
		}		
		$qry="INSERT INTO hy_info VALUES ('', '$userID', '$infoMembership', '$infoAttend', '$infoAccom', '$infoPlayWith', '$infoNotPlayWith', '$infoTransport', '$infoFood', '$infoComments', 'NULL', '$infoPrice')";
		$results=$this->put($qry);
		return $results;
	}//end putInfo
	
	 /********************************************************************************************************
    *Method creates a game entry in the hy_games table
   *********************************************************************************************************/
	public function putGame(){
		extract($_POST);
		if(!get_magic_quotes_gpc()){
			$gameName=$this->db->real_escape_string($gameName);
			$gameDescription=$this->db->real_escape_string($gameDescription);
			$gameCostume=$this->db->real_escape_string($gameCostume);
			$gameRestriction=$this->db->real_escape_string($gameRestriction);
			$gameGenre=$this->db->real_escape_string($gameGenre);
			$gameExtraInfo=$this->db->real_escape_string($gameExtraInfo);
			$gameAuthor=$this->db->real_escape_string($gameAuthor);
      }
		$qry="INSERT INTO hy_games VALUES ('', '$gameName', '$gameDescription', '$gameCostume', '$gameMaxPlayers', '$gameRestriction', '$gameGenre', '$gameExtraInfo', 'pending', CURRENT_TIMESTAMP, '$gameSlot', 'NULL', '0', '$gameAuthor', '$userID')";
		$results=$this->put($qry);
		return $results;
	}//end putGame
	
   /********************************************************************************************************
    *Method creates a news entry in the hy_news table
   *********************************************************************************************************/
	public function putNews(){
		
		extract($_POST); // userID, newsID, newsTitle, newsText
		if(!get_magic_quotes_gpc()) {
			$newsTitle=$this->db->real_escape_string($newsTitle);
			$newsText=$this->db->real_escape_string($newsText);
		}   
      $qry="INSERT INTO hy_news VALUES ('', '$newsTitle', '$newsText', CURRENT_TIMESTAMP, '$userID')";
      $result=$this->put($qry);
      return $result;
   }//end putNews
	
	/********************************************************************************************************
   *Method writes characters to the character table
   *********************************************************************************************************/
	public function putCharacters($gameID){
		$result=$this->removeCharactersByGameID($gameID);
		if($result){
			extract($_POST);
			$maxPlayers=$this->getMaxPlayers($gameID);
			for($i=0;$i<$maxPlayers;$i++){
				if(strlen($characterName[$i])>0){
					if(!get_magic_quotes_gpc()){
						$characterName[$i]=$this->db->real_escape_string($characterName[$i]);					
						$characterDescription[$i]=$this->db->real_escape_string($characterDescription[$i]);
					}
					$qry="INSERT INTO hy_characters VALUES ('', '$gameID', '$characterName[$i]', '$characterGender[$i]', '$characterDescription[$i]', '$characterStatus[$i]', '$userID[$i]')";
					$results=$this->put($qry);
				}
			}
		}
		return $results;		
	}
	
	/********************************************************************************************************
    *Method wriotes users game choices to the usergames table
   *********************************************************************************************************/
	public function putUserGames($userID, $gameID, $userGamesPref, $userGamesCharPref, $userGameStatus="pending"){
		if(!get_magic_quotes_gpc()){
			$userGamesCharPref=$this->db->real_escape_string($userGamesCharPref);
		}		
		if($this->checkCurrentGames($userID, $gameID)){
			$qry="UPDATE hy_usergames SET userGamesPref = '$userGamesPref', userGamesCharPref
			= '$userGamesCharPref' WHERE userID = '$userID' AND gameID='$gameID'";
		}else{
			$qry="INSERT INTO hy_usergames VALUES ('', $userID, $gameID, $userGamesPref, '$userGameStatus', '$userGamesCharPref', CURRENT_TIMESTAMP)";
		}
		$results=$this->put($qry);
		return $results;
	}//end putUserGames
	
	/********************************************************************************************************
    *Method updates a game entry in the hy_games table
   *********************************************************************************************************/
	public function updateGame(){				
		$oldData=$this->getGameFromID($gameID);
		foreach($_POST as $key=>$value){
			if(!strlen($_POST[$key])>0){
				$_POST[$key]=$oldData[$key];				
			}			
		}
		extract($_POST);
		if(!get_magic_quotes_gpc()){
			$gameName=$this->db->real_escape_string($gameName);
			$gameDescription=$this->db->real_escape_string($gameDescription);
			$gameCostume=$this->db->real_escape_string($gameCostume);
			$gameRestriction=$this->db->real_escape_string($gameRestriction);
			$gameGenre=$this->db->real_escape_string($gameGenre);
			$gameExtraInfo=$this->db->real_escape_string($gameExtraInfo);
			$gameAuthor=$this->db->real_escape_string($gameAuthor);
      }
		$qry="UPDATE hy_games SET gameName='$gameName', gameDescription='$gameDescription', gameCostume='$gameCostume', gameMaxPlayers='$gameMaxPlayers', gameRestriction='$gameRestriction', gameGenre='$gameGenre', gameExtraInfo='$gameExtraInfo', gameSlot='$gameSlot', gameAuthor='$gameAuthor' WHERE gameID='$gameID'";
		$results=$this->put($qry);
		return $results;
	}//end updateGame
	
	
		/********************************************************************************************************
    *Method updates a game entry in the hy_games table
   *********************************************************************************************************/
	public function updateGameStatus($gameStatus){
		$gameID=$_GET['gameID'];
		$qry="UPDATE hy_games SET gameStatus='$gameStatus' WHERE gameID='$gameID'";
		$results=$this->put($qry);
		return $results;
	}//end updateGame	
	
	
	

   
	/*******************************************************************************
	 *Updates the info record for a user, overwriting past data
	 *Form needs to re-populate with user's old data
	******************************************************************************/
	public function updateInfo($userID){
		extract($_POST);		
		if(!get_magic_quotes_gpc()){
			$infoFoodExtra=$this->db->real_escape_string($infoFoodExtra);
			$infoPlayWith=$this->db->real_escape_string($infoPlayWith);
			$infoNotPlayWith=$this->db->real_escape_string($infoNotPlayWith);
			$infoComments=$this->db->real_escape_string($infoComments);
      }	
		$infoFood=$infoFood.':'.$infoFoodExtra;		
		$qry="UPDATE hy_info SET infoMembership = '$infoMembership', infoAttend= '$infoAttend', infoAccom = '$infoAccom', infoPlayWith = '$infoPlayWith', infoNotPlayWith = '$infoNotPlayWith', infoTransport = '$infoTransport', infoFood = '$infoFood', infoComments = '$infoComments' WHERE userID = '$userID'";
		$results=$this->put($qry);
		return $results['msg'];
	}//end updateInfo
 
   /********************************************************************************************************
    *Method updates a profile in the hy_user table
   *********************************************************************************************************/
   public function updateProfile($userID=0){      
      extract($_POST);
		if($userID==0){
			$userID=$_GET['userID'];
		}
      if($userPic==null){
         $userPic=$this->getUserPic($userID);
        if(!$userPic){
         $userPic=$userName.".png";
        }
      }  
      if(strlen($_POST['userPassword'])>0){
       $userPassword=sha1($_POST['userPassword']."riotPolice");
      }else{
       $userPassword=$this->getUserPassword($userID);  
      }
		if(strlen($_POST['userFullName'])<1){          
       $userFullName=$this->getUserFullName($userID);  
      }	
      if(!get_magic_quotes_gpc()) {
			$userFullName=$this->db->real_escape_string($userFullName);
			$userEmail=$this->db->real_escape_string($userEmail);
			$userBio=$this->db->real_escape_string($userBio);
		}   
		$qry = "UPDATE hy_user SET userFullName = '$userFullName', userPassword = '$userPassword', userEmail = '$userEmail', userPic='$userPic', userBio = '$userBio' WHERE userID = '$userID'";
      $result=$this->put($qry);
      return $result;		
	}//end updateProfile   
   
   /*********************************************************************************************************
    *Method updates a news posts
   **********************************************************************************************************/
   public function updateNews(){
	
		extract($_POST); // userID, newsID, newsTitle, newsText
		if(!get_magic_quotes_gpc()) {
			$newsTitle=$this->db->real_escape_string($newsTitle);
			$newsText=$this->db->real_escape_string($newsText);
		}   
      $qry = "UPDATE hy_news SET newsTitle='$newsTitle', newsText='$newsText' WHERE newsID='$newsID'";
      $result=$this->put($qry);
      return $result;
   }//end updateNews
	
	/*********************************************************************************
	 *Method updates the general comments suppleid with
	 *game sign-up info
	*************************************************************************************/
	public function updateInfoGameComments($infoGameComments, $userID){
		if(!get_magic_quotes_gpc()) {
			$infoGameComments=$this->db->real_escape_string($infoGameComments);
		}   
      $qry = "UPDATE hy_info SET infoGameComments='$infoGameComments' WHERE userID='$userID'";
      $result=$this->put($qry);
      return $result;
	}//end updateInfoGameComments
	
	
	/*********************************************************************************************************
   *  Main remove query, writes query data to database and returns a success
   *  message or false. This method is used by all following methods in this class.
   *  Note: Very very similar to the put method, but returns a different error message
   *******************************************************************************************************/
   public function remove($qry){
      $rs=$this->db->query($qry); //Result set from database query      
      if($rs){
			$result['RID']=$this->db->insert_id;
			$result['msg']="Success";
			return $result;			
		}else{
			echo "Error deleting record: ".$qry;
			return false;
		} 
   }//end remove method	
	
   /********************************************************************************************************
    *Method removes a comment from the comments table
   *********************************************************************************************************/
   public function removeComment($commentID){
      $qry="DELETE FROM hy_comment WHERE commentID='$commentID'";
      $results=$this->remove($qry);
      return $results; 
   }//end removeComment
	
	/********************************************************************************************************
    *Method removes a hy_news entry 
   *********************************************************************************************************/
   public function removeNews($newsID){
      $qry="DELETE FROM hy_news WHERE newsID='$newsID'";
      $results=$this->remove($qry);
      return $results; 
   }//end removeComment
	
	/********************************************************************************************************
    *Method removes a user from the user table
   *********************************************************************************************************/
   public function removeUser($userID){
		$tableName=array(hy_user, hy_comment, hy_games, hy_info, hy_news, hy_usergames);
		foreach($tableName as $table){
			$qry="DELETE FROM $table WHERE userID='$userID'";
         $results=$this->remove($qry);			
		}
		$qry="UPDATE hy_characters SET userID = NULL WHERE userID='$userID'";
		$results=$this->put($qry);		
      return $results; 
   }//end removeUser
	
	/********************************************************************************************************
   *Method removes all games a user has signed up for from the usergames table
   *********************************************************************************************************/
   public function removeUserGames($userID){
      $qry = "DELETE FROM hy_usergames WHERE userID='$userID'";
      $result=$this->remove($qry);
      return $result;
   } //end removeUserGames
	
	/********************************************************************************************************
   *Method removes a single user game by gameID
   *********************************************************************************************************/
   public function removeUserGame($gameID, $userID){
      $qry = "DELETE FROM hy_usergames WHERE userID='$userID' AND gameID='$gameID'";
      $result=$this->remove($qry);
      return $result;
   } //end removeUserGame
	
	/********************************************************************************************************
   *Method removes a game from a users chosen games
   *********************************************************************************************************/
	public function removeGameFromUserGames($gameID){
      $qry = "DELETE FROM hy_usergames WHERE gameID='$gameID'";
      $result=$this->remove($qry);
      return $result;
   } //end removeGameFromUserGames
	
	/********************************************************************************************************
   *Method removes all characters from a game
   *********************************************************************************************************/
	public function removeCharactersByGameID($gameID){
		$qry = "DELETE FROM hy_characters WHERE gameID='$gameID'";
      $result=$this->remove($qry);
		return $result;
	}//end removecharactersbygameID
	
	/********************************************************************************************************
   *Method removes a game by a upplied ID
   *********************************************************************************************************/
	public function removeGameByID($gameID){
		$result=$this->removeCharactersByGameID($gameID);
		$result=$this->removeGameFromUserGames($gameID);
		$qry = "DELETE FROM hy_games WHERE gameID='$gameID'";
      $result=$this->remove($qry);
		return $result;
	}//end remove Game by ID
	

   
  
     
}//end dbClass
?>