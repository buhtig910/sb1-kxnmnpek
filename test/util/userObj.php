<?php

class User {
	
	private $username;
	private $confirmKey;
	private $email;
	
	
	public function requestResetEmail($username) {
		
		if($username == "")
			return 0;
		else {
			$query = "SELECT fldConfirmKey,fldEmail FROM tblB2TBroker WHERE fldUsername=\"".$username."\" AND fldActive=1";
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count > 0) {
				list($confirmKey,$email) = mysqli_fetch_row($result);
				$this->confirmKey = $confirmKey;
				$this->email = $email;
				
				return 1;
			}
		}

	}
	
	
	public function getEmail() {
		return $this->email;	
	}
	
	public function getKey() {
		return $this->confirmKey;
	}
	
	public function verifyKey($uKey) {
		
		$query = "SELECT count(*) FROM tblB2TBroker WHERE fldConfirmKey=\"".$uKey."\" AND fldActive=1";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		
		list($count) = mysqli_fetch_row($result);
		
		if($count == 1) {
			$this->confirmKey = $uKey;
			return true;
		}
		else
			return false;

	}
	
	public function updatePassword($newPassword) {
		
		$query = "UPDATE tblB2TBroker SET fldPassword=\"".md5($newPassword)."\" WHERE fldConfirmKey=\"".$this->confirmKey."\"";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_affected_rows($_SESSION['db']);
		
		if($count == 1)
			return true;
		else
			return false;
			
	}
	
}


?>