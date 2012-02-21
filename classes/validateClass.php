<?php
/*******************************************************************************************************************
 * This Class contains the methods needed to check and validate user submitted content
	 * checkRequired($field)
	 * checkName($name)
	 * userName($name)
	 * checkEmail($email)
	 * checkRepeat($field1, $field2)
**********************************************************************************************************************/
class Validate{
   
	 /*Checks that a generic field is not left blank*/
    public function checkRequired($field){
		if (!$field||strlen($field)<1) {//if field is empty
			$msg = "* Missing required field: ";
			return $msg;
		}else{
		  return false;
		}
    }//end check required
   
	/*Checks that the name given only contains legal characters:
	 letters, numbers, and undescores                     */
	public function checkName($name){ // checking name fields
		if(!preg_match("/^[[:word:][:blank:][:punct:]-]+$/",$name)){
		 //if used by a "Name" Field
		
				$msg = "* Please enter a valid Name <br />";
	
		  return $msg;
		}else{
		  return false;
		}		
	}//end checkName
	
	
	/*Checks that the user name only has numbers, letters, underscores and hyphens*/
	public function userName($name){ // checking names
		if(!preg_match('/^[A-Za-z0-9_\-\']+$/',$name)){
			$msg = '* Invalid User Name:<span class="unem">User Names may only contain numbers, letters, underscores and hyphens</span> <br />';
				return $msg;
		}else{
		  return false;
		}		
	}//end userName
	
	/*Checks that the supplied email address is valid*/
	public function checkNumbers($number)	{ // checking for a valid email
	   if(!preg_match('/^[[:digit:]]+$/',$number)&&strlen($number)>0){
			$msg = "* Invalid Number Entered: ";
			return $msg;
      }else{
		  return false;
	   }	 		
   }//end check email

   
	/*Checks that the supplied email address is valid*/
	public function checkEmail($email)	{ // checking for a valid email
      if(!preg_match('/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9_\-\.]+\.[a-zA-Z0-9_\-]+$/',$email)){
			$msg = "* Invalid Email Address <br />";
			return $msg;
      }else{
		  return false;
	   }	 		
   }//end check email
	
	/*Method checks that repeated fields match each other*/
	public function checkRepeat($field1, $field2){
		if($field1!=$field2){
         $msg="* Passwords do not match <br />";
		  return $msg;
       }else{
		  return false;
	   }		
	}//end check repeat
   
	
}//end of class validate
?>