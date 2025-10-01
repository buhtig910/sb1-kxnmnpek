<?php

class User {
	
	private $username;
	private $confirmKey;
	private $email;
	
	
	public function requestResetEmail($username) {
		
		if($username == "")
			return 0;
		else {
			// Generate a new reset key and timestamp
			$newKey = md5(uniqid(rand(), true));
			$timestamp = time();
			
			$query = "UPDATE tblB2TBroker SET fldConfirmKey='".$newKey."', fldResetTimestamp=".$timestamp." WHERE fldUsername=\"".$username."\" AND fldActive=1";
			$result = mysqli_query($_SESSION['db'],$query);
			$count = mysqli_affected_rows($_SESSION['db']);
			
			if($count > 0) {
				// Get the email for this user
				$emailQuery = "SELECT fldEmail FROM tblB2TBroker WHERE fldUsername=\"".$username."\" AND fldActive=1";
				$emailResult = mysqli_query($_SESSION['db'],$emailQuery);
				list($email) = mysqli_fetch_row($emailResult);
				
				$this->confirmKey = $newKey;
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
		
		// Check if key exists and is not expired (15 minutes = 900 seconds)
		$expirationTime = 900; // 15 minutes
		$currentTime = time();
		$expiredTime = $currentTime - $expirationTime;
		
		$query = "SELECT count(*) FROM tblB2TBroker WHERE fldConfirmKey=\"".$uKey."\" AND fldActive=1 AND fldResetTimestamp > ".$expiredTime;
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
	
	public function updatePassword($newPassword, $confirmKey = null) {
		
		// Use provided key or fall back to stored key
		$key = $confirmKey ? $confirmKey : $this->confirmKey;
		
		// First, let's find the user by the confirm key to get their broker ID
		$findQuery = "SELECT pkBrokerID FROM tblB2TBroker WHERE fldConfirmKey=\"".$key."\" AND fldActive=1";
		$findResult = mysqli_query($_SESSION['db'], $findQuery);
		
		if($findResult && mysqli_num_rows($findResult) > 0) {
			list($brokerID) = mysqli_fetch_row($findResult);
			
			// Update password using broker ID instead of confirm key
			$query = "UPDATE tblB2TBroker SET fldPassword=\"".md5($newPassword)."\" WHERE pkBrokerID=".$brokerID;
			$result = mysqli_query($_SESSION['db'],$query);
			$count = mysqli_affected_rows($_SESSION['db']);
			
			if($count == 1) {
				// Clear the confirm key after successful password update
				$clearQuery = "UPDATE tblB2TBroker SET fldConfirmKey=NULL WHERE pkBrokerID=".$brokerID;
				mysqli_query($_SESSION['db'], $clearQuery);
				return true;
			}
		}
		
		return false;
			
	}
	
}


?>