<?php
/***********************************************************************************************
 * The Admin View Class contains all the methods needed to display and run the tasks required 
of an Hydra administrator, including game approval, and game assignment
 ********************************************************************************************/
class Admin extends View{   
        private $model;
        private $msg;
        private $generated;
    
        /*Main, and required, method for the Admin view class*/ 
        protected function displayContent(){
		if(!$_SESSION['userType']||$_SESSION['userType']=='user'){//if only user rights, send to front page
			  header('Location: index.php');
			exit;	
		}else{
		  $this->generated=new Generate();
		   $this->model=new Model();
		   if(isset($_GET['gameStatus'])){
				$gameStatus=$this->model->getGameStatus($_GET['gameID']);
				$this->changeGameStatus($gameStatus);				
				if($gameStatus=="accepted"){
					$otherStatus="pending";
				}else{
					$otherStatus="accepted";
				}
				
					$url='<a href="index.php?pageName=admin&gameID='.$_GET['gameID']."&amp;gameStatus=";
					$url.=$otherStatus;
					$url.='&amp;js=true" class="buttonImg '.$otherStatus.'" title="';
					$url.=$otherStatus.': change to '.$gameStatus.'?">'.$otherStatus.'</a>';
					echo $url;
					exit;
				
				header('Location: index.php?pageName=admin');
						exit;
		   }else if(isset($_GET['userType'])){
		   		if($_GET['userType']=='mod'){
					$this->model->updateUserType($_GET['userID'], 'mod');
				}else{
				 	$this->model->updateUserType($_GET['userID'], 'user');
				}
		   }
		   if(isset($_GET['edit'])){
		   		if($_GET['edit']=='userReg'){
		   			$html=$this->userRegAdmin();
		   			//updateUserType
		   		}else{
					$form=$this->editGameAdmin();
					echo $form;
					exit;
				}
			}else{
				
				if(isset($_GET['download'])&&$_GET['download']=="reg"){
						$this->getRegCSV();
						header('Location: index.php?pageName=admin');
						exit;
				}
				if(isset($_GET['download'])&&$_GET['download']=="games"){
						$this->getGamesCSV();
						header('Location: index.php?pageName=admin');
						exit;
				}
				
				$html.=$this->displayLeftContent();   				   
		  		$html.=$this->displayRightContent();
                                $html.='<div class="clear"></div>';
		  		 
			} 
		}	
		  return $html;
	 }//end displayContent
	 
	 	 private function userRegAdmin(){
	 	 	//retrieve a list of info from database users table
	 	 	//show to user in a form they can select from need username + name
	 	 	//submit and process via model class
	 	 	$results=$this->generated->getUserRegistration($_GET['userID']);
			if(is_array($results)){
				extract($results);
			}
			
	 	 	 $html='<div class="left">'."\n";
			 $html.='<div class="pageContent">';
			 $html.='<div class="h3"><h3>Admin for '.$userName.'</h3></div><br>';
			 $html.='<table>';
			 $html.='<tr><td><strong>User Type</strong></td><td>This user is of type '.$userType;
			 $html.='</td><td>';
			 if($userType=='user'){
				$html.='<a href="index.php?pageName=admin&amp;userID='.$_GET['userID'].'&amp;userType=mod" class="button user">Make a moderator</a></p>';
			 }else if($userType=='mod'){
			 	$html.='<a href="index.php?pageName=admin&amp;userID='.$_GET['userID'].'&amp;userType=user" class="button user">Remove mod rights</a></p>';
			 }
			 $html.='</td></tr></table></div>';
                         $html.='<h4>Game choices</h4>';
                         $html.=$this->generated->displayUserGames($_GET['userID']);
			 $html.='<p class="note">More Coming soon... <br /><a href="index.php?pageName=admin">Return to administration page</a></p>';
			 
			 $html.='</div>';
                         $html.='<div class="clear"></div>';
	 	 	return $html;
	 }
	 
	 private function editGameAdmin(){
	 	 	//retrieve a list of info from database users table
	 	 	//show to user in a form they can select from need username + name
	 	 	//submit and process via model class
	 	 	$allUsers=$this->model->getAllUserNames();
			
			/*SELECT gameName, gameDescription, gameCostume, gameMaxPlayers, 
			 * gameRestriction, gameGenre, gameExtraInfo, gameStatus, gameDateUpdated, 
			 * gameSlot, gameVenue, gameNumCast, gameAuthor */
			$gameDetails=$this->model->getGameFromID($_GET['gameID']);
			if(is_array($gameDetails)){
				extract($gameDetails);
			}
			$assignGM=$this->model->getGMIDFromGame($_GET['gameID']);
			$html= '<div class="pageHeading"><h2>'.$gameName.'</h2></div>';
			$html.='<div class="content">'; 
			$html.='<ul class="form">';
			
			if(is_array($allUsers)){

				$html.='<li>';
				$html.='<label for="assignGM">GM:</label>';
				$html.='<select name="assignGM" id="assignGM">';
				
					foreach($allUsers as $result){
						extract($result);//userID, userName, userFullName
							//<option >userFullName (userName)</option>
						$user=$userFullName." (".$userName.")";						
						$html.='<option value="'.$userID.'"';
						if($assignGM==$userID){
							 $html.=' selected="selected"';
						}
						$html.=' >'."\n";
						$html.=$user.'</option>';						
					}			
				
				$html.='</select>';
				$html.='</li>';
			}
			$html.='<li>';
			$html.='<label for="gameVenue">Venue:</label>';
			$html.='<input type="text" id="gameVenue" name="gameVenue" value="'.$gameVenue.'" />';
			$html.='</li>';
			
			
			$players=$this->model->getPlayersForGame($_GET['gameID'], "All");
		
			if(is_array($players)){				
				$html.='<li>';
				$html.='<label for="userID">Select Players for this game</label>';
				$html.='<select id="userID" multiple="multiple" name="userID[]" title="Players">';			
				foreach($players as $player){
					extract($player);		
					$html.='<option id="'.$userID.'">'.$userName." ".$userGamesPref.'</option>';
				}
				$html.='</select>';
				$html.='</li>';
			}
			$html.='</ul>';
			$html.='</div>';				 
			return $html;
	 }
	 
	 private function changeGameStatus($gameStatus){
		  if($gameStatus=='pending'){
				$gameStatus='accepted';
		  }else{
				$gameStatus='pending';
		  }
		  $this->generated->updateGameStatus($gameStatus);
		  return;
	 }
   
   	/*Method to display the main content */
  	  private function displayLeftContent(){
		 $html='<div class="left">'."\n";
		 $html.='<div class="pageInfo rego">'."\n";
		 $html.='<div class="h3"><h3>Administrative Tasks</h3></div>';
		 $html.='<p>Note: Game administration is not yet functional</p>';
		 $html.='</div>';
		 $html.='<div id="rego">';
		  $html.=$this->viewGameSubmissions();
		 $html.='<div class="clear"><p>&nbsp;</p></div>';
		 $html.=$this->viewRegistrations();
		 $html.='</div>';
		  $html.='</div>';
		 return $html;
   	 }//end displaySearch
	
	/*******************************************************************
	* Will contain the generated HTML of all user registrations and an option
	 to download the file as a csv file
	 userID, userName, userFullName, userEmail,
	 infoMemberShip, infoAttend, infoAccom, infoPlayWith, infoNotPlayWith, infoTransport, infoFood, infoComments
	********************************************************************/
	private function viewRegistrations(){ 
	 
		  $html='<div class="h3"><h3>Registrations</h3></div>';
		  $html.='<table class="gameAdmin rego">';
		  $html.='<tr><th class="editRow"></th><th>Name</th><th>User Name</th><th>Email</th><th class="details">Details</th>';
		  
	   $results=$this->generated->getRegistrations();
		//print_r($results);
		if(is_array($results)){
		  $count=0;
		  foreach($results as $person){
			if($count==1){
				$alt="alt";
		   }else{
				$alt="alt2";
		   }	
			extract($person);
		  
			$html.='<tr class="'.$alt.'">';
			$html.='<td class="editRow"><a href="index.php?pageName=admin&amp;userID='.$userID.'&amp;edit=userReg"';
			$html.=' class="buttonImg edit" title="Edit">Admin</a></td>';	
			$html.='<td class="name">';//index.php?pageName=profile&userID=3
			$html.='<a href="index.php?pageName=profile&userID='.$userID.'">'.$userFullName.'</a>';
			$html.='</td>';
			$html.='<td class="userName">';//index.php?pageName=profile&userID=3
			$html.=$userName;
			$html.='</td>';
			$html.='<td class="email"><a href="mailto:'.$userEmail.'">'.$userEmail.'</a></td>';
			$html.='<td class="details"><a href="#" class="button show noView" id="'.$userName.'">show</a></td>';
			$html.='</tr>';
			$html.='<tr class="'.$userName.'"><td class="toggle" colspan="5">';
			$html.='<ul class="gameList hidden">';
			$html.='<li><span class="label">Membership:</span> '.$infoMemberShip.'</li>'; // infoMemberShip, infoAttend, infoAccom, infoPlayWith, infoNotPlayWith, infoTransport, infoFood, infoComments
			$html.='<li><span class="label">Attending:</span>'.$infoAttend.'</li>';
			$html.='<li><span class="label">Accommodation:</span>'.$infoAccom.'</li>';
		   $html.='<li><span class="label">Play With:</span>'.$infoPlayWith.'</li>';
		   $html.='<li><span class="label">Not Play With:</span>'.$infoNotPlayWith.'</li>';
		   $html.='<li><span class="label">Transport:</span>'.$infoTransport.'</li>';
		   $html.='<li><span class="label">Food:</span>'.$infoFood.'</li>';
		   $html.='<li><span class="label">Comments:</span><span class="tableText">'.$infoComments.'</span></li>';
		  
			$html.='</ul>';
			$html.='</td></tr>';
			if($count==1){
				$count=0;
		   }else{
				$count=1;
		   }
		  }
		}
		$html.='</table>';
		$html.='<div class="rego">';
		$html.='<div class="downloadFile"><a href="index.php?pageName=admin&download=reg" class="expand"><span class="buttonImg download">Download</span>Download CSV file</a></div>';
		 $html.='</div>';
		 $html.='<p class="note">Note: Ignore all reg details associated with the admin account</p>';
		return $html;
	}//end viewRegistrations
	
	
	/*******************************************************************
	* Will contain the  generated HTML for the game choices of registered users
	and an option to download the file as a csv file
	********************************************************************/
	 private function viewUserGames(){

		return $html;
	}//end viewUserGames
	
	
	/*******************************************************************
	* Will contain the actions and interface to assign Users to Games
	********************************************************************/
	private function assignUserGames(){
	
		return $html;
	}//end assignUserGames
	
	
	/*******************************************************************
	* Will contain the generated HTML for all games that have been
	* submitted (in brief).  With a link to the full details
	* gameID, gameName, gameDescription, gameCostume, gameMaxPlayers, gameRestriction, gameGenre,
	* gameExtraInfo, gameStatus, gameDateUpdated, gameSlot, gameVenue, gameNumCast, gameAuthor
	********************************************************************/
    private function viewGameSubmissions(){
		  $html='<div class="h3"><h3>Game Submissions</h3></div>';
		  $html.='<table class="gameAdmin gameSub">';
		  $html.='<tr><th class="editRow"></th><th><h4>Game</h4></th><th><h4>Status</h4></th><th><h4>GM</h4></th>';
		  $html.='<th><h4>RD</h4></th><th><h4>Venue</h4></th>';
		  $html.='<th class="details"><h4>Details</h4></th></tr>';
	   $results=$this->generated->getGames();
		if(is_array($results)){
		  $count=0;
		  foreach($results as $game){
			if($count==1){
				$alt="alt";
		   }else{
				$alt="alt2";
		   }	
			extract($game);
			
			$otherStatus='pending';
			if($gameStatus=='pending'){
				$otherStatus='accepted';
			}
			$html.='<tr class="'.$alt.'">';
			$html.='<td class="editRow"><a href="index.php?pageName=admin&amp;gameID='.$gameID.'&amp;edit=gameAdmin" class="buttonImg edit" title="Edit">Admin</a></td>';
			$html.='<td>';
			$html.='<a href="index.php?pageName=game&amp;gameID='.$gameID.'">'.$gameName.'</a>';
			$html.='</td>';
			$html.='<td><a href="index.php?pageName=admin&amp;gameID='.$gameID.'&amp;gameStatus='.$gameStatus.'" class="buttonImg '.$gameStatus.'" title="'.$gameStatus.': change to  '.$otherStatus.'?">'.$gameStatus.'</a></td>';
			$html.='<td>';
			$html.=$this->generated->getUserFromGame($gameID).'</td>';
			$html.='<td>'.$gameSlot.'</td>';
			$html.='<td>'.$gameVenue.'</td>';			
			$html.='<td class="details"><a href="#" class="button show noView">show</a></td>';
			$html.='</tr>';
			$html.='<tr class="admin">';
			$html.='<td class="toggle" colspan="7">';
			$html.='<ul class="gameList wide hidden">';
			$html.='<li><h5>Description</h5> '.nl2br($this->generated->stripHTMLTags(stripslashes($gameDescription))).'</li>';
			$html.='<li><span class="label">Genre</span> '.$gameGenre.'</li>';
			$html.='<li><span class="label">Restriction</span> '.$gameRestriction.'</li>';
			$html.='<li><span class="label">Costume</span><span class="tableText"> '.$gameCostume.'</span></li>';
			$html.='<li><span class="label">Player Spots</span> '.$gameMaxPlayers.'</li>';
			$html.='<li><span class="label">Number Cast</span> '.$gameNumCast.'</li>';
			$html.='</ul>';
			$html.='</td></tr>';
			if($count==1){
				$count=0;
		   }else{
				$count=1;
		   }
		  }
		}
		$html.='</table>';
		$html.='<div class="rego">';
		$html.='<div class="downloadFile"><a href="index.php?pageName=admin&download=games" class="expand"><span class="buttonImg download">Download</span>Download CSV file</a></div>';
		 $html.='</div>';
		$html.='</div>';
		return $html;
	}//end viewGameSubmissions
	

	
	/*********************************************************************
	* Will contain the date in the pages table under Admin. 
	* This area will be used by admin's to leave notes on their tasks, 
	and progress reports
	**********************************************************************/
	private function displayAdminComments(){
	
	
	
		return $html;
	}//end displayAdminComments
	
	
	/*********************************************************************
	* Will contain the  interface  to edit the content of the Admin Page,
	*  used for admins to leave notes
	**********************************************************************/
	private function editAdminComments(){
	
	
	
		return $html;
	}//end editAdminComments
	
	
	
	public function getRegCSV(){
		$fileName="registrations.csv";
   		$results=$this->generated->getRegistrations();
		$success=false;
		$titles="id,UserName,Name,Email,Member?,Attending,Accommodation,Play with?,Not play with?,Transport,Food,Comments ";
		if(is_array($results)){
			$success=$this->model->writeToFile($results, $fileName, $titles);		
		
		}
			return $success;	
	}
	
	public function getGamesCSV(){
		$fileName="games.csv";
   		$results=$this->generated->getGames();
		$success=false;
		$titles="id,GM,Game Name,Description,Costume,Max Players,Restriction,Genre,Extra Info,Status,Date Updated,Slot,Venue,Num Cast,Author ";
		
		if(is_array($results)){
			$games=array();
			foreach($results as $result){
				$result['userID']=$this->model->getUserFullName($result['userID']);
				array_push($games,$result);
			}
			
			
			$success=$this->model->writeToFile($games, $fileName, $titles);		
		}
			return $success;	
	}
	

	
	
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
			
		$html.='<div class="keyNote">';
		$html.='<h4>Files:</h4>';
		$html.='<table class="adminFiles">';
		$html.='<tr><td class="key"><a href="index.php?pageName=admin&download=games"><span class="buttonImg download"></span></a></td>';
		$html.='<td><a href="index.php?pageName=admin&download=games" class="expand">Games</a> <sub>(.csv)</sub></td></tr>';
		$html.='<tr><td class="key"><a href="index.php?pageName=admin&download=reg" class="expand"><span class="buttonImg download"></span></a></td>';
		$html.='<td><a href="index.php?pageName=admin&download=reg" class="expand">Registered</a> <sub>(.csv)</sub</td></tr>';		
		$html.='</table>';
		$html.='</div>';
		
			
		$html.='<div class="keyNote">';
		$html.='<h4>Key:</h4>';
		$html.='<table class="adminKey">';
		$html.='<tr><td class="key"><span class="buttonImg edit"></span></td><td>Admin (edit)</td></tr>';
		$html.='<tr><td class="key"><span class="buttonImg accepted"></span></td><td>Accepted</td></tr>';
		$html.='<tr><td class="key"><span class="buttonImg pending"></span></td><td>Pending</td></tr>';

		
		$html.='</table>';
		$html.='</div>';
		
		
		
		 
		$html.='</div> <!-- end right div /-->'."\n";
		return $html;
	 }  
   
}//end search class
?>