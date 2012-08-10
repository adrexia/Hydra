<?php
/*****************************************************************************
 * SumbitGame class displays:
	 * The form to submit a Larp
	 * Larps submitted by the logged in user (and everyone if the user is an admin)
	 * The forms to edit or delete the submitted Larps

******************************************************************************/
class SubmitGame extends View
{
	 private $model;
	 private $generated;
	 private $msg;
	 private $gameID;
	
	/**************************************************************************************************
	* Main Method to displayContent. All View extentions must have this class
	************************************************************************************************/
	 protected function displayContent(){
		  $this->msg=null; //set the msg to null so it can be tested
		  $this->model=new Model();
		  $this->generated=new Generate();		  
		  $html=$this->testForms();		 
		  $html.=$this->displayHandle();	  
		  return $html;  
   	 }//end display content
	
	/****************************************************************************************
	 ** This method checks if any of the pages forms have been submitted
	 ****************************************************************************************/
	 private function testForms(){
		  if($_POST['cancel']){ //tests if a delete has been canceled
				unset($_GET['action']);
				header('Location: index.php?pageName=submitGame');
				exit;
		  }
		  if($_POST['deleteGame']){//tests if game delete has been confirmed
				unset($_GET['action']);
				$result=$this->model->removeGameByID($_POST['gameID']);
				header('Location: index.php?pageName=submitGame');
				exit;
		  }
		  if($_POST['deleteCharacters']){//tests if character delete has been confirmed				
				$result=$this->model->removeCharactersByGameID($_POST['gameID']);
				unset($_GET['action']);
				header('Location: index.php?pageName=submitGame');
				exit;
		  }
		  if($_GET['gameID']){//tests if there is a game ID in the get array (used fro editing or delting submitted games)
				$this->gameID=$_GET['gameID'];
				$authorID=$this->model->getGMIDFromGame($this->gameID);//get the userID of the GM
				if($_SESSION['userType']=="user"){
					 if($authorID!=$_SESSION['userID']){
						  unset($_GET['gameID']);
						  header('Location: index.php?pageName=submitGame');
						  exit;
					 }
		      }		
		  }	//end get GameID
		  if($_POST['submitGame']){//process form
				$result=$this->model->processGame(); //needs to contain ['msg']. if ['msg']="Success", needs to contain ['gameID']
				$this->msg=$result['msg'];				
				if($result['RID']){
					 $this->gameID=$result['RID'];
					 unset($_GET['action']);
				}			
		  }	//end if Post Submit Game 
		  if($_POST['characters']){//process form				
				$result=$this->model->processCharacters(); 
				$this->msg=$result['msg'];
				unset($_GET['action']);
		  }	//end if form has been submitted	 
		  return $html; 
	 }//end test forms
	 
   /************************************************************************************************
   * Checks if form has been processed, if user is already running a game,
   * what part (if any) the user wishes to edit and runs the appropriate methods 
   *************************************************************************************************/
    private function displayHandle(){		  
		  $userRunningGame=$this->model->checkGM($_SESSION['userID']);
		  if($this->msg=="Success"){ //then processing has been successful. Unset any actions in Get array and reload page
				if($_POST['characters']){//shows the page with all submitted game on it
					 unset($_GET['action']);
					 header('Location: index.php?pageName=submitGame');
					 exit;
				}else{ //shows the success message with add chracters, view gae, iew all games links
					 header('Location: index.php?pageName=submitGame&gameID='.$this->gameID);
					 exit;					
				}
		  }else{//if form not submitted or has returned an error	
				
				$html.='<div class="left"><div class="post">';
				if($userRunningGame&!$_GET['gameID']&&$_GET['action']!="add"){		
					 $html.=$this->displayGMGames();
					 $html.='</div>';//end left
					 $html.='<div class="clear"></div>'."\n";
				}elseif($_GET['action']=="gamesDelete"||$_GET['action']=="charactersDelete"){
					$html.=$this->generated->removeGameData();					 
				}elseif($this->gameID==0||$_GET['action']=="gamesEdit"||$_GET['action']=="add"){
					$html.=$this->addGame();//if newgame of game edit selected
				}else{ 
					$html.=$this->handleFinish();
				}//end user session if/else		
				 $html.='</div><!-- end left div /-->'."\n";			
		 
		  		$html.='</div>'."\n";	
				$html.=$this->displayRightContent();		
		  }
		 
		  return $html;
	 }//end displayContent
	 
	 /***********************************************************
	  *Method handles the sucess method for adding
	  *a Larp and lets users add characters
	  *********************************************************/
	 private function handleFinish(){
		  if($_GET['action']=="charactersEdit"){
				$html.=$this->addCharacters()."\n";
		  }else{
				if($_SESSION['userType']=='su'||$_SESSION['userType']=='mod'){
					 $allGamesText='View all Submitted Larps';
				}else{
					 $allGamesText='View all the Larps you have Submitted';
				}
				$html.='<div class="h3"><h3>Success!</h3></div>'."\n";
				$html.='<p>Your Game has been successfully Created or Updated</p>'."\n";
				$html.='<p class="note">'."\n";
				$html.='<strong><a href="index.php?pageName=submitGame&amp;gameID='.$_GET['gameID'].'&amp;action=charactersEdit">Add and Edit Characters for this Larp</a></strong></p>'."\n";
				$html.='<p class="note"><strong><a href="index.php?pageName=game&amp;gameID='.$_GET['gameID'].'">View this Larp</a></strong></p>'."\n";
				$html.='<p class="note"><strong><a href="index.php?pageName=submitGame">'.$allGamesText.'</a></strong></p>'."\n";	 
		  }		  
		  return $html;
	 }//end handleFinish
	 
	 /****************************************************************************************
	  *Method shows the form to add a game if a user is logged in,
	  *else shows a login option
	  ***************************************************************************************/
	 private function addGame(){
		  $this->gameID!=0;
		  if(!$_SESSION['userID']){//if a non-logged in user reaches this page
				$html.='<p class="note">'."\n";
				$html.='<a href="index.php?pageName=login&amp;history=submitGame">Login</a> or ';
				$html.='<a href="index.php?pageName=register">Register</a> to submit a Larp'."\n";
				$html.='</p>'."\n";					 
		  }else{
				$html.='<div class="h3"><h3>Larp Details</h3></div>';
				if($_POST['submitGame']){
					 $html.='<p class="note"><strong>Game Submission Failed:</strong><br />'.$this->msg.'</p>'."\n";
				}
				$html.=$this->showForm();
		  }
		  return $html;
	 }//end add Game
	
	/*********************************************************************
	* Method to show the form to add a Larp
	*********************************************************************/
	private function showForm(){
		  //set-up arrays for select based on whether the uer has admin rigths or not
		  if($_SESSION['userType']=='su'||$_SESSION['userType']=='mod'){
				$slotName=array("No Preference","Friday Night","Saturday Morning","Saturday Afternoon","Saturday Evening","Sunday Morning","Sunday Afternoon");
				$slotValue=array("0","1","2","3","4","5","6"); 
		  }else{	  	 
				$slotName=array("No Preference","Friday Night","Saturday Morning","Saturday Afternoon","Sunday Morning","Sunday Afternoon");
				$slotValue=array("0","1","2","3","5","6");
		  }
		  $gameID=0;		  
		  $oldGameData=$this->model->getGameFromID($_GET['gameID']);
		  if($oldGameData){
				extract($oldGameData);
				$gameID=$_GET['gameID'];
		  }
		  if($_POST){
				extract($_POST);
		  }		  
		  $html='<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data" method="post" id="mainForm">'."\n";
		  $html.='<ul class="form">'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="gameName">Title*</label>'."\n";
		  $html.='<input type="text" value="'.stripslashes($gameName).'" name="gameName" id="gameName" />'."\n";
		  $html.='<div class="formExtra">What is your Larp called?</div>';
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="gameAuthor">Author(s)</label>'."\n";
		  $html.='<input type="text" value="'.stripslashes($gameAuthor).'" name="gameAuthor" id="gameAuthor" />'."\n";
		  $html.='<div class="formExtra">Who wrote your Larp?</div>';
		  $html.='</li>'."\n";	
		  $html.='<li class="commentBox">'."\n";
		  $html.='<label for="gameDescription">Description*</label>'."\n";
		  $html.='<textarea rows="3" cols="50" name="gameDescription" id="gameDescription">'.stripslashes($gameDescription).'</textarea>'."\n";
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="gameMaxPlayers">Number of Players*</label>'."\n";
		  $html.='<input type="text" value="'.$gameMaxPlayers.'" name="gameMaxPlayers" id="gameMaxPlayers" />'."\n";
		  $html.='<div class="formExtra">Please enter the Maximum only</div>';
		  $html.='</li>'."\n";
		  $html.='<li>';
		  $html.='<label for="gameRestriction">Restriction</label>'."\n";
		  $html.='<input type="text" value="'.stripslashes($gameRestriction).'" name="gameRestriction" id="gameRestriction" />'."\n";
		  $html.='<div class="formExtra">(i.e. R18, G, PG, et al)</div>';
		  $html.='</li>'."\n";
		  $html.='<li>';
		  $html.='<label for="gameGenre">Genre</label>'."\n";
		  $html.='<input type="text" value="'.stripslashes($gameGenre).'" name="gameGenre" id="gameGenre" />'."\n";
		  $html.='<div class="formExtra">Optional extra</div>';
		  $html.='</li>'."\n";	
		  $html.='<li class="commentBox">'."\n";
		  $html.='<label for="gameCostume">Costuming</label>'."\n";
		  $html.='<textarea rows="3" cols="50" name="gameCostume" id="gameCostume">'.stripslashes($gameCostume).'</textarea>'."\n";		
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</li>'."\n";
		  $html.='<li>'."\n";
		  $html.='<label for="gameSlot">Preferred Session:</label>'."\n";
		  $html.='<select name="gameSlot" id="gameSlot">'."\n";	
		  $html.=$this->populateSelect($slotValue, $slotName, $gameSlot);
		  $html.='</select>'."\n";	
		  $html.='</li>'."\n";		  
		  $html.='<li class="commentBox">'."\n";
		  $html.='<label for="gameExtraInfo">Extra Info</label>'."\n";
		  $html.='<textarea rows="3" cols="50" name="gameExtraInfo" id="gameExtraInfo">'.stripslashes($gameExtraInfo).'</textarea>'."\n";		
		  $html.='<div class="clear"></div>'."\n";
		  $html.='</li>'."\n";		
		  $html.='<li class="submit">'."\n";
		  $html.='<input type="hidden" name="gameID" value="'.$gameID.'" id="gameID" />'."\n";
		  $html.='<input type="hidden" name="userID" value="'.$_SESSION['userID'].'" id="userID" />'."\n";
		  $html.='<input type="hidden" name="userName" value="'.$_SESSION['userName'].'" id="userName" />'."\n";
		  $html.='<input type="submit" name="submitGame" value="Submit Larp" id="pageSubmit" />'."\n";		  
		  $html.='</li>'."\n";
		  $html.='</ul>'."\n";
		  $html.='<p>&nbsp;</p>'."\n";
		  $html.='</form>'."\n";	
		  return $html;
	}//end showForm
	
	
	 /**************************************************************************
	 * Method to display form to add chartacters
	 ****************************************************************************/
	 private function addCharacters(){
		  $gender=array("Either","Female","Male");
		  $status=array("Uncast","Cast","Other");
		  $numCharacters=$this->model->getMaxPlayers($this->gameID);
		  $gameName=$this->model->getGameNameFromID($this->gameID);
		  $currentCharacters=$this->model->getCharactersFromID($this->gameID);
		  $currentPlayers=$this->model->getPlayersForGame($this->gameID);
		  $userIDs=array('0');
		  $userNames=array('');
		  if($currentPlayers){
				foreach($currentPlayers as $player){
					 array_push($userIDs, $player['userID']);
					 array_push($userNames, $player['userName']);
				}
		  }
		  if($currentCharacters){
				$i=0;
				foreach($currentCharacters as $character){ //characterName, characterGender, characterDescription, characterStatus, userID
					 $characterName[$i]=$character['characterName'];
					 $characterGender[$i]=$character['characterGender'];
					 $characterDescription[$i]=$character['characterDescription'];
					 $characterStatus[$i]=$character['characterStatus'];
					 $userID[$i]=$character['userID']; //Tricky! (actually surprisingly straight forward)
					 $i++;
				}
		  }
		  extract($_POST);
		  $html.='<div class="h3"><h3>Add Characters to '.$gameName.'</h3></div>';
		  $html.='<form action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data" method="post" id="mainForm">'."\n";
		  $html.='<ul class="form">'."\n";		  
		  for($i=0;$i<$numCharacters;$i++){
				$j=$i+1;
				$html.='<li class="charName">'."\n";
				$html.='<label for="characterName['.$i.']">Character '.$j.'</label>'."\n";
				$html.='<input type="text" value="'.$characterName[$i].'" name="characterName['.$i.']" id="characterName['.$i.']"/>'."\n";
				$html.='<div class="formExtra">Enter the Character\'s Name</div>';
				$html.='</li>'."\n";
				$html.='<li>'."\n";
				$html.='<label for="characterGender['.$i.']">Gender:</label>'."\n";
				$html.='<select name="characterGender['.$i.']" id=""characterGender['.$i.']">'."\n";	
				$html.=$this->populateSelect($gender, $gender, $characterGender[$i]);
				$html.='</select>'."\n";	
				$html.='</li>'."\n";
			   $html.='<li class="commentBox">'."\n";
				$html.='<label for="characterDescription['.$i.']">Description:</label>'."\n";
				$html.='<textarea rows="3" cols="50" name="characterDescription['.$i.']" id=""characterDescription['.$i.']">'.$characterDescription[$i].'</textarea>'."\n";				 $html.='<div class="clear"></div>'."\n";
				$html.='</li>'."\n";
				$html.='<li>'."\n";
				$html.='<label for="characterStatus['.$i.']">Casting Status:</label>'."\n";
				$html.='<select name="characterStatus['.$i.']" id=""characterStatus['.$i.']">'."\n";	
				$html.=$this->populateSelect($status, $status, $characterStatus[$i]);
				$html.='</select>'."\n";	
				$html.='</li>'."\n";
				if($currentPlayers){					 
					 $html.='<li>'."\n";
					 $html.='<label for="userID['.$i.']">Cast Player:</label>'."\n";
					 $html.='<select name="userID['.$i.']" id=""userID['.$i.']">'."\n";	
					 $html.=$this->populateSelect($userIDs, $userNames, $userID[$i]);
					 $html.='</select>'."\n";
					 $html.='<div class="formExtra">Who is playing this character?</div>';
					 $html.='</li>'."\n";
				}else{
					 $html.='<li>'."\n";
					 $html.='<span class="label">Cast Player:</span>'."\n";
					 $html.='<span class="noInput">There are currently no players to cast</span>'."\n";	
					 $html.='</li>'."\n";					 
				}
				$html.='<li class="divide">&nbsp;</li>';
				
		  }
		  $html.='<li class="submit">'."\n";
		  $html.='<input type="hidden" name="gameID" value="'.$_GET['gameID'].'" id="userName" />'."\n";
		  $html.='<input type="submit" name="characters" value="Update Characters" id="pageSubmit" />'."\n";		  
		  $html.='</li>'."\n";
		  $html.='</ul>'."\n";
		  $html.='<p>&nbsp;</p>'."\n";
		  $html.='</form>'."\n";
		  $html.='<div class="clear"></div>'."\n";
		  return $html;
	 }//end addCharacters
	
	/*******************************************************************************
	 *Method displays the games for the logged in GM,
	 *or all games if user is an admin
	 ******************************************************************************/
	 private function displayGMGames(){	 
		  $html=$this->displayGMInfo();
		  if($_SESSION['userType']=='su'||$_SESSION['userType']=='mod'){//if su or mod, get all games
				 $larpDetails=$this->model->getGames();
		  }else{
				$larpDetails=$this->model->getGamesFromGM($_SESSION['userID']);
		  }
		  if(is_array($larpDetails)){
				foreach($larpDetails as $larp){
					 $html.=$this->generated->showGame($larp);
				}
		  }	 
		  return $html;
	 }//end displayGMGames
	
	 /*************************************************************************************
	  *Method displays the note at the top of the page to GM's 
	  ************************************************************************************/
	 public function displayGMInfo(){
		  $html.='<div class="pageInfo">'."\n";
		  $html.='<div class="h3"><h3>Add or Edit a Larp</h3></div>'."\n";
		  $html.='<h5 class="pageTitle">I want to add another Larp:</h5>'."\n";
		  $html.='<ul class="main"><li><a href="index.php?pageName=submitGame&amp;action=add">Submit a Larp!</a></li></ul>'."\n";
		  $html.='<h5 class="pageTitle">I want to add information or edit the details of my Current Larps</h5>'."\n";
		  $html.='<ol class="main">'."\n";
		  $html.='<li>To edit the general details of your Larp, find the game below and hit "<strong>Edit Larp</strong>"</li>'."\n";
		  $html.='<li>To edit your characters, find the game below, and hit "<strong>Edit Characters</strong>"</li>'."\n";
		  $html.='<li>To add extra characters, you first need to increase the number of players in your Larp. To do this, Edit your Larp(#1) and change the "Number of Players" field. Once you have edited your Larp details, you will have as many character fields as the number you supplied. </li>'."\n";
		  $html.='</ol>'."\n";	  
		  $html.='</div>'."\n";	 
                  $html.='<div class="clear"></div>';
		  return $html;
	 }//end displayGMInfo
	
	 /**********************************************************************************
	 * Helper method to populate select options.
	 * Takes an array of values, an array of displayNames and
	 * the field history to make the select sticky
	 *********************************************************************************/
	 private function populateSelect($optionValue, $optionName, $field){	
		  $i=0;
		  foreach($optionValue as $value){
				$html.='<option value="'.$value.'"';
				if($field==$value){
					 $html.=' selected="selected"';
				}
				$html.=' >'."\n";
				$html.=$optionName[$i].'</option>'."\n";
				$i++;
		  } 	 
		  return $html;
	 }//end populateSelect
		
	
	 /**************************************************************************************
	  *Method to display the content on the right
	  **Note: These will likely be moved to the generate class and
	  **combined with a switch statement to serve different content
	 *************************************************************************************/
	 private function displayRightContent(){
		  $pageDetails=$this->model->getPage('submitGame');
		  $pageDetails=$pageDetails['pageContent'];
		  $html='<div class="right">'."\n";
		  $html.=$this->generated->displaySearchBox();	
		  $html.='<div class="pageNav">'."\n";
		  $html.='<div class="h2"><h2>Details</h2></div>'."\n";
		  $html.='<div class="rightContent">'."\n";
		  $html.='<div class="gameNav">'."\n";
		  $html.='<p>Submitted games will be reviewed by the organisers before being made available for sign-up.</p> '."\n";
		  $html.='<p>&nbsp;</p> '."\n";
	 /*	  $html.='<ul>';  //May need to add this functionality later
		  $html.='<li>';
		  $html.='<a href="">View Game Criteria</a>';
		  $html.='</li>';
		  $html.='<li>';
		  $html.='<a href="">View Venue Information</a>';
		  $html.='</li>';
		  $html.='<li>';
		  $html.='<a href="">View Current Games</a>';
		  $html.='</li>';	
		  $html.='</ul>';		  */
		  $html.='<br /><br />'."\n";
		  $html.='</div>'."\n";
		  $html.='</div>'."\n";
		  $html.='</div>'."\n"; //end pageNav
		
		  $html.='<div class="pageNav">'."\n";
		  $html.='<div class="h2"><h2>Game Submission Process</h2></div>'."\n";
		  $html.='<div class="rightContent">'."\n";
		  $html.=$this->generated->displayGMProcess();			 		
		  $html.='</div>'."\n";
		  $html.='</div>'."\n";			
		  if($this->model->checkGM($_SESSION['userID'])){ //if user is a GM, display their games
				$games=$this->model->getGamesFromGM($_SESSION['userID']);
				$html.='<div class="pageNav">'."\n";
				$html.='<div class="h2"><h2>Your Games</h2></div>'."\n";
				$html.='<div class="rightContent">'."\n";
				$html.='<div class="gameNav">'."\n";
				$html.='<p>Games you have offered to run:</p>'."\n";
				$html.='<ul>'."\n";					
				foreach($games as $game){
					 extract($game);
					 $html.='<li><a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a><em class="right">'.$gameStatus.'</em></li>'."\n";		 		 }
				$html.='</ul>'."\n";	
				$html.='<br /><br />'."\n";
				$html.='</div>'."\n";
				$html.='</div>'."\n";
				$html.='</div>'."\n"; //end pageNav				
		  }
		  $html.='</div> <!-- end right div /-->'."\n";
		  $html.='<div class="clear"></div>';	
		  return $html;	 	  
	 }//end display Right content	
	
}//end Create Account Class
?>