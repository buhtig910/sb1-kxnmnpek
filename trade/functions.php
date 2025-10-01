<?php

require_once("vars.inc.php");

// CSRF Protection Functions
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Input Validation Functions
function sanitizeInput($input, $type = 'string') {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateInteger($value, $min = null, $max = null) {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false) return false;
    
    if ($min !== null && $int < $min) return false;
    if ($max !== null && $int > $max) return false;
    
    return true;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validateRequired($value) {
    return !empty(trim($value));
}

// Database Security Functions
function safeQuery($db, $query, $params = array(), $types = '') {
    try {
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) {
            error_log("Database prepare error: " . mysqli_error($db) . " Query: " . $query);
            return false;
        }
        
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params)); // Default to string type
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            error_log("Database execute error: " . mysqli_stmt_error($stmt) . " Query: " . $query);
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } catch (Exception $e) {
        error_log("Database exception: " . $e->getMessage() . " Query: " . $query);
        return false;
    }
}

function safeInsert($db, $table, $data) {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    $types = str_repeat('s', count($columns));
    
    $query = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    return safeQuery($db, $query, array_values($data), $types);
}

function safeUpdate($db, $table, $data, $where, $whereParams = array()) {
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    $whereClause = $where;
    
    $query = "UPDATE $table SET $setClause WHERE $whereClause";
    $params = array_merge(array_values($data), $whereParams);
    $types = str_repeat('s', count($params));
    
    return safeQuery($db, $query, $params, $types);
}

function safeDelete($db, $table, $where, $params = array()) {
    $query = "DELETE FROM $table WHERE $where";
    $types = str_repeat('s', count($params));
    
    return safeQuery($db, $query, $params, $types);
}

function formatDate($date){
	/* 01/05/2007 */
	if($date != ""){
		$pieces = explode("-",$date);
		$date_formatted = $pieces[1]."/".$pieces[2]."/".$pieces[0];

		return $date_formatted;
	}
}

function formatDate2($date){
	/* January 5, 2007 */
	$pieces = explode("-",$date);
	return date("F j, Y",mktime(0,0,0,$pieces[1],$pieces[2],$pieces[0]));
}

function formatDate3($date){
	/* Jan 5 */
	$pieces = explode("-",$date);
	return @date("M j",mktime(0,0,0,$pieces[1],$pieces[2],$pieces[0]));
}

function reverseDate($date){
	/* YYYY-MM-DD */
	$pieces = explode("/",$date);
	if(count($pieces) > 1)
		$date_formatted = $pieces[2]."-".$pieces[0]."-".$pieces[1];
	else
		$date_formatted = $date;

	return $date_formatted;
}

function formatTimestamp($timestamp) {
	//2008-01-01T08:54:00-06:00
	//used for ConfirmHub timestamping
	
	//return date("Y-m-d H:i:s P",$timestamp);
	return substr(date("c",$timestamp),0,19); //remove timezone offset
}
function formatTimestamp2($timestamp) {
	//2008-01-01T08:54:00-06:00
	//used for ConfirmHub timestamping
	
	return date("Y-m-d H:i:s",$timestamp);
}


function addEmailToDatabase($email) {
	$today = date("Y-m-d");
	if($mailingList == "yes")
		$optIn = 1;
	else
		$optIn = 0;
	$query = "INSERT INTO tblB2TMailingList VALUES('',\"$name\",\"$email\",\"$today\",\"$optIn\")";
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
}

function validateSecurity(){
	
	// Check if user is logged in using current session variables
	if(isset($_SESSION["loggedIn42"]) && $_SESSION["loggedIn42"] == "true1"){
		//echo("logged in: yes");
		return true;
	}
	// Also check if we have a valid userType (backup check)
	elseif(isset($_SESSION["userType"]) && $_SESSION["userType"] >= 0){
		//echo("logged in via userType: yes");
		return true;
	}
	else {
		//echo("logged in: no");
		return false;
	}
	
}

function isAdmin() {
	// Super Admins ALWAYS have admin access regardless of temporary overrides
	if($_SESSION["userType"] == "2") {
		return true;
	}
	
	// Check if there's a temporary user type override (for testing)
	if(isset($_SESSION["tempUserType"])) {
		if($_SESSION["tempUserType"] == "1")
			return true;
		else
			return false;
	}
	
	// Check if there's a cookie override (for persistence across page refreshes)
	if(isset($_COOKIE["tempUserType"])) {
		$_SESSION["tempUserType"] = $_COOKIE["tempUserType"];
		if($_COOKIE["tempUserType"] == "1")
			return true;
		else
			return false;
	}
	
	// Default to actual user type - Administrator (1)
	if($_SESSION["userType"] == "1")
		return true;
	else
		return false;
}



function login($username,$password) {

	if($username != "" && $password != ""){
		$query = "SELECT pkBrokerID,fldUserType,fldFirstLogin FROM tblB2TBroker WHERE fldUsername=\"$username\" AND fldPassword=\"".md5($password)."\" AND fldActive=1";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_num_rows($result);
		
		if($count == 1){
			//echo("<p>logged in</p>");
			list($broker,$userType,$firstLogin) = mysqli_fetch_row($result);
			$_SESSION["loggedIn42"] = "true1";
			$_SESSION["brokerID42"] = $broker;
			$_SESSION["username"] = $username;
			$_SESSION["pass78"] = $password;
			$_SESSION["userType"] = $userType;
			$_SESSION["userID"] = $broker;
			$_SESSION["firstLogin"] = $firstLogin;
			
			// Update last login timestamp
			// updateLastLogin($broker); // Temporarily disabled until fldLastLogin field is added
		}
		else {
			return "Invalid username/password";
		}		
	}
	else {
		return "Invalid username/password";
	}
	
}

function logout() {
	$_SESSION["loggedIn42"] = null;
	$_SESSION["brokerID42"] = null;
	session_destroy();
}

if($action == "logout"){
	logout();
}


function sendConfirmation($email) {
	$headers = "From: donotreply@greenlightcommodities.com";
	
	$sendTo = $email;
	$subject = "B2T Technologies - Trade Confirmation";
	
	$message = "This messages confirms that a trade has been entered.\n\n";
	$message .= "Please log in to TradeConnect to confirm your trade information.\n\n";
	$message .= "http://www.tradeconnect.com/";
	//$message .= "";
	//$message .= "<p>";
	
	//$sendMailResult = mail($sendTo,$subject,$message,$headers);
}


function sendEmail($name,$email,$comments,$howHeard,$otherHeard) {
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

		$headers .= "From: ";

		if(isset($name))
			$headers .= $email."\r\n";
		else
			$headers .= "[blank]\r\n";
		
		//$sendTo .= "chris@baldweb.com";
		$sendTo .= "jake+contactform@greenlightcommodities.com";
		
		if(isset($mySubject))
			$subject = $mySubject;
		else
			$subject = "GreenlightCommodoties.com Contact Form";

		$message = "<html><body>";
		$message .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\">\n";
		$message .= "<tr><td width=\"150\">Name:</td><td>$name</td></tr>\n";
		$message .= "<tr><td>E-mail:</td><td>$email</td></tr>\n";
		$message .= "<tr><td>Comments:</td><td>$comments</td></tr>\n";
		$message .= "</table></body></html>";

		$sendMailResult = mail($sendTo,$subject,$message,$headers);
		//$sendMailResult = true;
		return $sendMailResult;
}


function populateTraderList($traderID) {
	$queryTrader = "SELECT pkClientID,fldTraderName FROM tblB2TClient WHERE fldActive=1 ORDER BY fldTraderName ASC";
	$resultTrader = mysqli_query($_SESSION['db'],$queryTrader);
	@$traderCount = mysqli_num_rows($resultTrader);
	
	if($traderCount > 0){
	
		echo("<option value=\"\">Select</option>\n");
		
		while($resultSet = mysqli_fetch_row($resultTrader)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($traderID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function populateTraderListFiltered($companyID) {
	$queryTrader = "SELECT pkClientID,fldTraderName FROM tblB2TClient WHERE fldActive=1 AND fkCompanyID=$companyID ORDER BY fldTraderName ASC";
	//echo($queryTrader);
	$resultTrader = mysqli_query($_SESSION['db'],$queryTrader);
	@$traderCount = mysqli_num_rows($resultTrader);
	
	if($traderCount > 0){
		$loopCount = 0;
		//echo("<option value=\"\">Select</option>\n");
		echo("{\"companies\":[{\"id\":\"\",\"value\":\"Select\"},");
		
		while($resultSet = mysqli_fetch_row($resultTrader)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($traderID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			if($loopCount > 0)
				echo(",");
			//echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
			echo("{\"id\":\"$resultSet[0]\",\"value\":\"$resultSet[1]\"}");
			
			$loopCount++;
		}
		echo("]}");
	}
}


function populateBrokerList($brokerID) {
	$queryBuyer = "SELECT pkBrokerID,fldName FROM tblB2TBroker WHERE fldActive=1";
	$resultBuyer = mysqli_query($_SESSION['db'],$queryBuyer);
	@$buyerCount = mysqli_num_rows($resultBuyer);
	
	if($buyerCount > 0){
	
		echo("<option value=\"\">Select</option>\n");
		
		while($resultSet = mysqli_fetch_row($resultBuyer)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($brokerID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}

function getBrokerName($brokerID) {
	
	if($brokerID == "all") {
		$brokerName = "All brokers";	
	}
	else {
		$query = "SELECT fldName FROM tblB2TBroker WHERE pkBrokerID=$brokerID";
		$result = mysqli_query($_SESSION['db'],$query);
		@list($brokerName) = mysqli_fetch_row($result);
	}
	
	return $brokerName;	
}

function populateProductGroup($groupID) {
	$queryGroup = "SELECT DISTINCT fldProductGroup FROM tblB2TTradeType ORDER BY fldProductGroup ASC";
	$resultGroup = mysqli_query($_SESSION['db'],$queryGroup);
	@$groupCount = mysqli_num_rows($resultGroup);
	
	if($groupCount > 0){
	
		echo("<option value=\"\">Select</option>\n");
		
		while($resultSet = mysqli_fetch_row($resultGroup)){
			/*
			0 = name
			*/
			
			$selected = "";
			if($groupID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[0]</option>\n");
		}
	}
}
function getProductGroup($groupID) {
	$queryGroup = "SELECT DISTINCT fldProductGroup FROM tblB2TTradeType WHERE pkTypeID=".$groupID;
	$resultGroup = mysqli_query($_SESSION['db'],$queryGroup);
	@list($groupName) = mysqli_fetch_row($resultGroup);
	
	return $groupName;
	
}	


function populateProductList($index,$tradeType) {
	$queryProduct = "SELECT pkProductID,fldProductName FROM tblB2TProduct WHERE fldActive=1 ORDER BY fldProductName ASC";
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@$productCount = mysqli_num_rows($resultProduct);
	
	$defaultQuery = "SELECT fkProductIDDefault FROM tblB2TTradeType WHERE pkTypeID=".$tradeType;
	//echo($defaultQuery);
	$defaultResult = mysqli_query($_SESSION['db'],$defaultQuery);
	@list($defaultID) = mysqli_fetch_row($defaultResult);
	
	if($productCount > 0){
		if($index == "" && $defaultID == "")
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"\" $selected>Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultProduct)){
			/*
			0 = productID
			1 = name
			*/
			$selected = "";
			if($index == $resultSet[0])
				$selected = "selected=\"selected\"";
			else if($defaultID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getProduct($index){
	$queryProduct = "SELECT fldProductName FROM tblB2TProduct WHERE pkProductID=".$index;
	//echo($queryProduct);
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@list($productName) = mysqli_fetch_row($resultProduct);
	
	return $productName;
	
}

function populateProductType($index) {
	$queryProduct = "SELECT pkProductTypeID,fldProductTypeName FROM tblB2TProductType WHERE fldActive=1 ORDER BY fldProductTypeName ASC";
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@$productCount = mysqli_num_rows($resultProduct);
	
	if($productCount > 0){	
		if($index == "")
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"\" $selected>Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultProduct)){
			/*
			0 = brokerID
			1 = name
			*/
			$selected = "";
			if($index == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getProductType($index) {
	$queryProduct = "SELECT fldProductTypeName FROM tblB2TProductType WHERE pkProductTypeID=".$index;
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@list($productType) = mysqli_fetch_row($resultProduct);
	
	return $productType;
}

function isPhysicalProductType($index) {
	$prodType = getProductType($index);
	
	if( strpos($prodType,"Physical") !== false)
		return true;
		
	return false;
}

function populateCurrencyList($currencyID) {
	$queryCurrency = "SELECT pkCurrencyID,fldCurrency FROM tblB2TCurrency WHERE fldActive=1";
	$resultCurrency = mysqli_query($_SESSION['db'],$queryCurrency);
	@$currencyCount = mysqli_num_rows($resultCurrency);
	
	if($currencyCount > 0){		
		while($resultSet = mysqli_fetch_row($resultCurrency)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($currencyID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getCurrencyList($currencyID) {
	$queryCurrency = "SELECT fldCurrency FROM tblB2TCurrency WHERE pkCurrencyID=".$currencyID;
	$resultCurrency = mysqli_query($_SESSION['db'],$queryCurrency);
	@list($currencyName) = mysqli_fetch_row($resultCurrency);
	
	return $currencyName;
}

function populateOptionStyle($optionID) {
	$queryProduct = "SELECT pkOptionStyleID,fldName FROM tblB2TOptionStyle WHERE fldActive=1 ORDER BY fldName ASC";
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@$productCount = mysqli_num_rows($resultProduct);
	
	if($productCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultProduct)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($optionID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getOptionStyle($optionID) {
	$queryProduct = "SELECT fldName FROM tblB2TOptionStyle WHERE pkOptionStyleID=".$optionID;
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@list($optionName) = mysqli_fetch_row($resultProduct);
	
	return $optionName;
}

function populateOptionType($optionID) {
	$queryProduct = "SELECT pkOptionTypeID,fldName FROM tblB2TOptionType WHERE fldActive=1 ORDER BY fldName ASC";
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@$productCount = mysqli_num_rows($resultProduct);
	
	if($productCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultProduct)){
			/*
			0 = brokerID
			1 = name
			*/
			
			$selected = "";
			if($optionID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getOptionType($optionID) {
	$queryProduct = "SELECT fldName FROM tblB2TOptionType WHERE pkOptionTypeID=".$optionID;
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@list($optionName) = mysqli_fetch_row($resultProduct);
	
	return $optionName;
}

function populateExercise($exerciseID) {
	$queryProduct = "SELECT pkExerciseID,fldName FROM tblB2TExercise WHERE fldActive=1 ORDER BY fldName ASC";
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@$productCount = mysqli_num_rows($resultProduct);
	
	if($productCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultProduct)){
			/*
			0 = brokerID
			1 = name
			*/
			$selected = "";
			if($exerciseID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getExercise($exerciseID) {
	$queryProduct = "SELECT fldName FROM tblB2TExercise WHERE pkExerciseID=".$exerciseID;
	$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
	@list($exerciseName) = mysqli_fetch_row($resultProduct);
	
	return $exerciseName;
}

//Build location dropdown list on trade entry screen
function populateLocationList($locationID,$productGroup) {
	
	$productGroup = preg_replace("/US/","",$productGroup);
	$productGroup = trim(strtoupper($productGroup));
	
	$queryLocation = "SELECT pkLocationID,fldLocationName FROM tblB2TLocation WHERE fldProductGroup=\"$productGroup\" AND fldActive=1 ORDER BY fldLocationName ASC";
	//echo($queryLocation);
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@$locationCount = mysqli_num_rows($resultLocation);
	
	if($locationCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultLocation)){
			/*
			0 = locationID
			1 = name
			*/
			
			$selected = "";
			if($locationID == $resultSet[0])
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getLocationList($locationID) {
	$queryLocation = "SELECT fldLocationName FROM tblB2TLocation WHERE pkLocationID=".$locationID;
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@list($locationName) = mysqli_fetch_row($resultLocation);
	
	return $locationName;
}

function populateOffsetList($offsetID, $tradeType = null) {
	$queryLocation = "SELECT pkLocationID,fldLocationName FROM tblB2TLocation WHERE fldActive=1 ORDER BY fldLocationName ASC";
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@$locationCount = mysqli_num_rows($resultLocation);
	
	if($locationCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultLocation)){
			/*
			0 = locationID
			1 = name
			*/
			
			$selected = "";
			if($offsetID == $resultSet[0])
				$selected = "selected=\"selected\"";
			// For New Product trades (Type 9 and 10), don't auto-select NYMEX Natural Gas
			else if($offsetID == "" && $resultSet[1] == "NYMEX Natural Gas (LD1)" && $tradeType !== null) {
				// Check if this is a New Product trade type
				$newProductQuery = "SELECT pkTypeID FROM tblB2TTradeType WHERE fldTradeName IN ('New Product 1', 'New Product 2')";
				$newProductResult = mysqli_query($_SESSION['db'], $newProductQuery);
				$newProductTypeIDs = array();
				if($newProductResult && mysqli_num_rows($newProductResult) > 0) {
					while($row = mysqli_fetch_row($newProductResult)) {
						$newProductTypeIDs[] = $row[0];
					}
				}
				
				// Only auto-select NYMEX Natural Gas for non-New Product trades
				if(!in_array($tradeType, $newProductTypeIDs)) {
					$selected = "selected=\"selected\"";
				}
			}
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getOffsetList($offsetID) {
	$queryLocation = "SELECT fldLocationName FROM tblB2TLocation WHERE pkLocationID=".$offsetID;
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@list($locationName) = mysqli_fetch_row($resultLocation);
	
	return $locationName;
}

function populateFrequency($freqID) {
	$queryLocation = "SELECT pkFrequencyID,fldName,fldValue FROM tblB2TFrequency WHERE fldActive=1 ORDER BY fldValue ASC";
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@$locationCount = mysqli_num_rows($resultLocation);
	
	if($locationCount > 0){	
		echo("<select name=\"unitFreq\" id=\"unitFreq\" onchange=\"updateQuantity();\">");
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultLocation)){
			/*
			0 = locationID
			1 = name
			*/
			
			$selected = "";
			if($freqID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
		echo("</select>");
	}
	else {
		echo("Hourly");
	}
}
function getFrequency($freqID) {
	$queryLocation = "SELECT fldName,fldValue FROM tblB2TFrequency WHERE pkFrequencyID=".$freqID;
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@list($freqName) = mysqli_fetch_row($resultLocation);
	
	return $freqName;
}

function populateBlock($blockID,$region,$locationID) {
		
	$queryBlock = "SELECT fldValue,fldName FROM tblB2TBlocks WHERE fldActive=1 ORDER BY fldValue ASC";
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@$blockCount = mysqli_num_rows($resultBlock);
	
	$queryLocation = "SELECT fldHourTemplate,fldGeoRegion FROM tblB2TLocation WHERE pkLocationID=$locationID";
	
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@list($hoursTemp,$geoRegion) = mysqli_fetch_row($resultLocation);
		
	$westBlocks = array(5,6,7,8); //available west region blocks
	$westOnly = array(7,8); //west specific blocks
	
	if($blockCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultBlock)){
			/*
			0 = blockValue
			1 = name
			*/
			
			$selected = "";
			if($blockID == $resultSet[0])
				$selected = "selected=\"selected\"";			
			
			if(strtoupper($hoursTemp) == "WEST" && !in_array($resultSet[0],$westBlocks)) {}
			else if(strtoupper($hoursTemp) != "WEST" && in_array($resultSet[0],$westOnly)) {}
			else
				echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}

/* Modify hours block text for East/Electricity */
function getBlockDisplayText($region,$block,$locationID,$timezone){
	
	$query2 = "SELECT fldDisplayText FROM tblB2TBlocks WHERE fldValue=$block";
	$result2 = mysqli_query($_SESSION['db'],$query2);
	@list($defaultText) = mysqli_fetch_row($result2);
	
	$query3 = "SELECT fldGeoRegion,fldProductGroup FROM tblB2TLocation WHERE pkLocationID=$locationID";
	$result3 = mysqli_query($_SESSION['db'],$query3);
	@list($geoRegion,$prodGroup) = mysqli_fetch_row($result3);
	
	$defaultText = preg_replace("/(CPT)/","$timezone",$defaultText);
	
	if(strcasecmp($geoRegion,"EAST") == 0 && (strtoupper($prodGroup) == "ELECTRICITY")) //modify east hours slightly for electricity only
		return preg_replace("/HE700-HE2200/","HE800-HE2300",$defaultText); //replace to east hours
	else
		return $defaultText;
}


function getBlock($blockID) {
	$queryBlock = "SELECT fldName FROM tblB2TBlocks WHERE fldValue=".$blockID;
	//echo($queryBlock);
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@list($blockName) = mysqli_fetch_row($resultBlock);
	
	return $blockName;
}

function populateDeliveryList($deliveryID) {
	$queryBlock = "SELECT fldValue,fldName FROM tblB2TDelivery WHERE fldActive=1 ORDER BY fldValue ASC";
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@$blockCount = mysqli_num_rows($resultBlock);
	
	if($blockCount > 0){	
	//	echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultBlock)){
			/*
			0 = blockValue
			1 = name
			*/
			
			$selected = "";
			if($deliveryID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}  
function getDeliveryList($deliveryID) {
	$queryBlock = "SELECT fldName FROM tblB2TDelivery WHERE fldValue=".$deliveryID;
	//echo($queryBlock);
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@list($deliveryName) = mysqli_fetch_row($resultBlock);
	
	return $deliveryName;
}                     


function getStatusList($indexPos) {
	$statusArray = array(0,1,2,3,4,5,6,7);
	/*
	0 = pending
	1 = confirmed
	2 = live
	3 = settled
	4 = cancelled
	5 = confirmed/cancelled
	6 = live/cancelled
	7 = void
	*/
	
	if(!isset($indexPos))
		$selected = "selected=\"selected\"";
	echo("<option value=\"-1\" $selected>All</option>\n");
		
	for($i=0; $i<count($statusArray); $i++){
		/*
		0 = id
		1 = value
		*/
		
		//echo("i: ".$statusArray[$i]."\n");
		
		$selected = "";
		if($indexPos == $statusArray[$i] && isset($indexPos))
			$selected = "selected=\"selected\"";
		if($i==5){
			if(isAdmin()){
				echo("<option value=\"$statusArray[$i]\" $selected>".showStatus($statusArray[$i])."</option>\n");
			}
		}
		else {
			echo("<option value=\"$statusArray[$i]\" $selected>".showStatus($statusArray[$i])."</option>\n");
		}
	}

}

function showStatus($statusInt){
	if($statusInt == $_SESSION["CONST_CANCELLED"]){
		return "Cancelled";
	}
	else if($statusInt == $_SESSION["CONST_SETTLED"]){
		return "Settled";
	}
	else if($statusInt == $_SESSION["CONST_LIVE"]){
		return "Live";
	}
	else if($statusInt == $_SESSION["CONST_CONFIRMED"]){
		return "Confirmed";
	}
	else if($statusInt == $_SESSION["CONST_CONFIRM_CANCELLED"]){
		return "Confirmed/Cancelled";
	}
	else if($statusInt == $_SESSION["CONST_LIVE_CANCELLED"]){
		return "Live/Cancelled";
	}
	else if($statusInt == $_SESSION["CONST_VOID"]){
		return "Void";
	}
	else{
		return "Pending";
	}
}

function getUserTypeList($userTypeValue) {
	// Debug: Show what user type value we're looking for
	echo "<!-- Debug getUserTypeList: Looking for userTypeValue = '" . htmlspecialchars($userTypeValue) . "' -->";
	
	$queryLocation = "SELECT pkTypeID,fldName,fldValue FROM tblB2TUserType WHERE fldActive=1 ORDER BY fldName ASC";
	$resultLocation = mysqli_query($_SESSION['db'],$queryLocation);
	@$locationCount = mysqli_num_rows($resultLocation);
	
	if($locationCount > 0){	
		echo("<option value=\"\">Select</option>\n");
			
		while($resultSet = mysqli_fetch_row($resultLocation)){
			/*
			0 = type ID
			1 = name
			2 = value
			*/
			
			$selected = "";
			if($userTypeValue == $resultSet[2]) {
				$selected = "selected=\"selected\"";
				echo "<!-- Debug: Found match! Setting selected for " . htmlspecialchars($resultSet[1]) . " (value: " . htmlspecialchars($resultSet[2]) . ") -->";
			}
			
			echo("<option value=\"$resultSet[2]\" $selected>$resultSet[1]</option>\n");
		}
	}
}

function getDaysList($day){
	$queryBlock = "SELECT fldValue,fldDay FROM tblB2TDays ORDER BY fldValue ASC";
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@$blockCount = mysqli_num_rows($resultBlock);
	
	if($blockCount > 0){
			
		while($resultSet = mysqli_fetch_row($resultBlock)){
			/*
			0 = blockValue
			1 = name
			*/
			
			$selected = "";
			if($day == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}
function getDay($day) {
	$queryBlock = "SELECT fldDay FROM tblB2TDays WHERE fldValue=".$day;
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@list($dayName) = mysqli_fetch_row($resultBlock);

	return $dayName;
}

function getDayType($day) {
	$queryBlock = "SELECT fldName FROM tblB2TDayType WHERE fldValue=".$day;
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@list($dayName) = mysqli_fetch_row($resultBlock);

	return $dayName;
}

function getDayTypeList($dayID){
	$queryBlock = "SELECT fldValue,fldname FROM tblB2TDayType ORDER BY fldValue DESC";
	$resultBlock = mysqli_query($_SESSION['db'],$queryBlock);
	@$blockCount = mysqli_num_rows($resultBlock);
	
	if($blockCount > 0){
			
		while($resultSet = mysqli_fetch_row($resultBlock)){
			/*
			0 = blockValue
			1 = name
			*/
			
			$selected = "";
			if($dayID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1]</option>\n");
		}
	}
}

function getHoursList($hour){
	
	for($i=100; $i<=2400; $i=$i+100){
		$selected = "";
		if($hour == $i)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$i\" $selected>$i</option>\n");
	}

}

function getUserType($type){
	// This function is used for displaying user types in lists
	// It should always show the ACTUAL stored user type, not testing overrides
	if($type == "0"){
		return "General User";
	}
	else if($type == "1"){
		return "Administrator";
	}
	else if($type == "2"){
		return "Super Administrator";
	}
}

function getCurrentUserType($type, $brokerID = null){
	// This function is used for the current logged-in user
	// It can show testing overrides when appropriate
	if($brokerID && isset($_SESSION["brokerID42"]) && $brokerID == $_SESSION["brokerID42"]) {
		// This is the current logged-in user, check for testing overrides
		if(isset($_SESSION["tempUserType"])) {
			if($_SESSION["tempUserType"] == "0"){
				return "General User (Testing)";
			}
			else if($_SESSION["tempUserType"] == "1"){
				return "Administrator (Testing)";
			}
			else if($_SESSION["tempUserType"] == "2"){
				return "Super Administrator (Testing)";
			}
		}
		
		// Check if there's a cookie override (for persistence across page refreshes)
		if(isset($_COOKIE["tempUserType"])) {
			$_SESSION["tempUserType"] = $_COOKIE["tempUserType"];
			if($_COOKIE["tempUserType"] == "0"){
				return "General User (Testing)";
			}
			else if($_COOKIE["tempUserType"] == "1"){
				return "Administrator (Testing)";
			}
			else if($_COOKIE["tempUserType"] == "2"){
				return "Super Administrator (Testing)";
			}
		}
	}
	
	// Default to actual user type
	if($type == "0"){
		return "General User";
	}
	else if($type == "1"){
		return "Administrator";
	}
	else if($type == "2"){
		return "Super Administrator";
	}
}

// Function to check if current user is Super Admin
function isSuperAdmin($userType = null) {
	// If userType parameter is provided, check that specific value
	if($userType !== null) {
		return $userType == "2";
	}
	
	// Super Admins ALWAYS retain their privileges regardless of temporary overrides
	if($_SESSION["userType"] == "2") {
		return true;
	}
	
	// Check if there's a temporary user type override (for testing)
	if(isset($_SESSION["tempUserType"])) {
		if($_SESSION["tempUserType"] == "2")
			return true;
		else
			return false;
	}
	
	// Check if there's a cookie override (for persistence across page refreshes)
	if(isset($_COOKIE["tempUserType"])) {
		$_SESSION["tempUserType"] = $_COOKIE["tempUserType"];
		if($_COOKIE["tempUserType"] == "2")
			return true;
		else
			return false;
	}
	
	return false;
}

// New function to update last login timestamp
function updateLastLogin($brokerID) {
	global $tblBroker;
	$currentTime = date('Y-m-d H:i:s');
	$query = "UPDATE $tblBroker SET fldLastLogin = '$currentTime' WHERE pkBrokerID = '$brokerID'";
	return mysqli_query($_SESSION['db'], $query);
}

function addSortArrow($columnName,$last) {
	if($columnName == $last) {
		echo("&nbsp;<img src=\"images/sort_".strtolower($_SESSION["sortOrder2"]).".gif\" height=\"10\" width=\"10\" alt=\"\" class=\"center\" />\n");
	}
}


function showAuditHistory($auditResult,$transNum){
	//audit resultset query object in
	echo("<p>History for trade number <strong>$transNum</strong></p>\n");
	
	echo("<table class=\"dataTable\" style=\"width: 400px\"><tr><th width=\"140\">Date Modified</th><th width=\"50\">Leg</th><th>Modified By</th>\n");
	
	$i=0;
	$rowClass = Array("","evenRow");
	while($resultSet = mysqli_fetch_row($auditResult)){
		/*
		0 = audit id
		1 = transaction ID
		2 = transaction number
		3 = timestamp
		*/
		
		$queryDetail = "SELECT fldName,fldLegID FROM tblB2TBroker b, tblB2TTransaction t WHERE t.fkEntryUserID=b.pkBrokerID AND t.pkTransactionID=$resultSet[1]";
		//echo("<p>".$queryDetail."</p>");
		$resultDetail = mysqli_query($_SESSION['db'],$queryDetail);
		@list($tempUser,$legID) = mysqli_fetch_row($resultDetail);

		$temp = $i % 2;
		echo("<tr class=\"$rowClass[$temp]\"><td>$resultSet[3]</td><td>$legID</td><td>$tempUser</td></tr>\n");
	
	}

}

//hours logic for custom block
function buildColumns($startHour,$endHour) {
	
	$queryStr = "sum(fldHE".$startHour.")";
	
	for($i=$startHour+100; $i<$endHour; $i=$i+100){
		$queryStr.= "+sum(fldHE".$i.")";
	}
	
	return $queryStr;
	
}

//days logic for custom block
function buildColumns2($startDay,$endDay) {
	$query = "SELECT * FROM tblB2TDays ORDER BY fldValue ASC";
	$result = mysqli_query($_SESSION['db'],$query);
	
	$queryStr = "(";
	$loop = 0;
	$sep = "";
	
	if($endDay < $startDay){
		$endDay = $endDay + 7; //end day # is less than start day, so include everything in between
	}
	
	for($i=1; $i<=7; $i++){ //loop 7 days
		list($id,$day,$value) = mysqli_fetch_row($result);	

		if($value>=$startDay || $value<=($endDay % 7)){

			if($loop>0)
				$sep = " OR ";

			$queryStr.= $sep."fld".$day."=1";
			$loop++;
		}
		
	}
	
	/*while(list($id,$day,$value) = mysqli_fetch_row($result)){
		if($value>=$startDay && $value<=($endDay % 7)){
			if($loop>0)
				$sep = " OR ";
				
			$queryStr.= $sep."fld".$day."=1";
			$loop++;
		}
	}*/
	
	$queryStr.= ")";
	return $queryStr;
	
}

//days logic for custom block
function buildColumns2a($startDay,$endDay) {
	$query = "SELECT * FROM tblB2TDays ORDER BY fldValue ASC";
	$result = mysqli_query($_SESSION['db'],$query);
	
	$queryStr = "(";
	$loop = 0;
	$sep = "";
	
	if($endDay < $startDay){
		$endDay = $endDay + 7; //end day # is less than start day, so include everything in between
	}
	
	for($i=1; $i<=7; $i++){ //loop 7 days
		list($id,$day,$value) = mysqli_fetch_row($result);	
		//echo("i: ".$value.", startDay: ".$startDay.", endDay: ".$endDay."<br>");
		if($value>=$startDay && $value<=$endDay) {
			
			if($loop>0)
				$sep = " OR ";

			$queryStr.= $sep."fld".$day."=1";
			$loop++;
		}	
	}
	
	$queryStr.= ")";
	return $queryStr;

}

//days logic for custom block
function buildColumns3($dayType) {
	
	$startDay = "";
	$endDay = "";
	
	if($dayType == 5) {
		$queryStr = "fldWestPeak=1";	
	}
	else if($dayType == 4) {
		$queryStr = "fldWestPeak=0";
	}
	else if($dayType == 2) {
		//all days
		$queryStr = "(fldNERCDAY=0 OR fldNERCDAY=1)";
	}
	else if($dayType == 1){
		//nerc days
		$queryStr = "fldNERCDAY=1";
	}
	else if($dayType == 0) {
		//non-nerc days
		$queryStr = "fldNERCDAY=0";
	}
	
	//echo($queryStr);
	return $queryStr;
	
}


function populateExistingClients() {
	
	$query = "SELECT DISTINCT pkClientID,fldCompanyName,fldShortName FROM tblB2TClient ORDER BY fldShortName ASC";
	$result = mysqli_query($_SESSION['db'],$query);
	
	echo("<option value=\"\" selected=\"selected\">SELECT</option>\n");
	while($resultSet = mysqli_fetch_row($result)){
		if($resultSet[2] != "")
			echo("<option value=\"$resultSet[0]\">$resultSet[2] (ID: ".$resultSet[0].")</option>\n");
	}
	
}


function populateExistingCompanies($companyID) {
	
	$query = "SELECT co.pkCompanyID,co.fldCompanyName,count(cl.pkClientID) FROM tblB2TCompany as co LEFT JOIN tblB2TClient as cl on cl.fkCompanyID=co.pkCompanyID AND cl.fldActive=1 GROUP BY pkCompanyID ORDER BY co.fldCompanyName ASC";
	$result = mysqli_query($_SESSION['db'],$query); //get active trader count for each company
	
	echo("<option value=\"\">SELECT</option>\n");
	while($resultSet = mysqli_fetch_row($result)){
		$selected = "";
		if($resultSet[1] != "") {
			if($resultSet[0] == $companyID)
				$selected = "selected=\"selected\"";
				
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[1] (Traders: ".$resultSet[2].")</option>\n");
		}
	}
	
}

function populateExistingCompaniesForSelection() {
	
	$query = "SELECT pkCompanyID,fldCompanyName FROM tblB2TCompany ORDER BY fldCompanyName ASC";
	$result = mysqli_query($_SESSION['db'],$query);
	
	echo("<option value=\"\">SELECT</option>\n");
	while($resultSet = mysqli_fetch_row($result)){
		if($resultSet[1] != "") {
			echo("<option value=\"$resultSet[1]\">$resultSet[1]</option>\n");
		}
	}
	
}

function formatCurrency($value,$precision,$symbol) {
	if($precision > 2) {
		//pad value to a specific precision greater than 2
		$pieces = explode(".",$value);
		if(count($pieces) <= 1)
			$cents = "0";
		else
			$cents = $pieces[1];
		
		$newDec = str_pad($cents,$precision,"0",STR_PAD_RIGHT);
		$newValue = $pieces[0].".".$newDec;
		
		if($symbol == 1)
			return "$".$newValue;
		else
			return $newValue;
	}
	else if($precision == -1) {
		//use the decimal precision of the value that was entered
		$pieces = explode(".",$value);
		if(count($pieces) <= 1)
			$length = 2;
		else
			$length = strlen($pieces[1]);
		
		return formatCurrency($value,$length,$symbol);
	}
	else {
		//standard currency formar, 2 decimal places
		setlocale(LC_MONETARY, 'en_US');
		$val = money_format('%n', floatval($value));
		
		if($symbol == 0)
			$val = str_replace("$","",$val);

		return $val;
	}
	
	return $newValue;

}


function formatIndexPrice($value) {
	 $output = str_replace("$","",formatCurrency($value,2,1));
	 return $output;
}

function formatDecimal($value,$precision) {

	setlocale(LC_MONETARY, 'en_US');
	return str_replace("$","",money_format('%n', floatval($value)));

}

function formatDecimal2($value,$precision) {

	return preg_replace("/,/","", formatDecimal($value,$precision) );

}


function buildCompanyList() {
	
	$query = "SELECT pkCompanyID,fldCompanyName FROM tblB2TCompany ORDER BY fldCompanyName ASC";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count > 0) {
				
		echo("<select name=\"companyID\">\n");
		
		while($resultSet = mysqli_fetch_row($result)) {
			/*
			0 = id
			1 = name
			*/
			
			echo("<option value=\"$resultSet[0]\">$resultSet[1]</option>\n");
			
		}
		
		echo("</select>\n");
	}
}



function getTraderName($clientID) {
	$query = "SELECT fldTraderName FROM tblB2TClient WHERE pkClientID=".$clientID;
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@$traderName = mysqli_fetch_row($result);
	
	return $traderName[0];
}



function getCompanyInfo($clientID) {
	$query = "SELECT fldTraderName,fldCompanyName,fldConfirmStreet,fldConfirmStreet2,fldConfirmCity,fldConfirmState,fldConfirmZip,fldConfirmPhone,fldConfirmFax,fldEmail FROM tblB2TClient WHERE pkClientID=".$clientID;
	//echo($queryBuyer);
	$result = mysqli_query($_SESSION['db'],$query);
	@$companyInfo = mysqli_fetch_row($result);
	
	return $companyInfo;
}


function getInvoiceHistory($companyID,$selectedID) {
	
	$query = "SELECT fldInvoiceNum,fldDate FROM tblB2TInvoice WHERE fkCompanyID=$companyID ORDER BY fldDate DESC";
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	echo("<select name=\"invoiceNum\">\n");
		echo("<option value=\"0\">Unsettled</option>\n");
		
		if($count > 0 ){
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = id
				1 = date
				*/
				$selected = "";
				if($resultSet[0] == $selectedID)
					$selected = "selected=\"selected\"";
				
				echo("<option value=\"$resultSet[0]\" $selected>".formatDate($resultSet[1])."</option>\n");
			}
		}
		
	echo("</select>\n");

}

function lastInvoiceDate($companyID) {

	$query = "SELECT fldDate FROM tblB2TInvoice WHERE fkCompanyID=$companyID ORDER BY fldDate DESC LIMIT 1";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 0) {
		return "N/A";
	}
	else {
		list($lastDate) = mysqli_fetch_row($result);
		return formatDate($lastDate);		
	}

}


function settleTrade($transID){

	$checkQuery = "SELECT fldStatus FROM tblB2TTransaction WHERE pkTransactionID=".$transID;
	//echo($checkQuery);
	$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
	@list($tradeStatus) = mysqli_fetch_row($resultQuery);
	
	if($tradeStatus == $_SESSION["CONST_LIVE"]) { //trade is live, settle it
		$query = "UPDATE tblB2TTransaction SET fldStatus=\"".$_SESSION["CONST_SETTLED"]."\" WHERE pkTransactionID=".$transID;
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
	}
	
	if(mysqli_affected_rows($_SESSION['db']) == 1)
		return true;
	else
		return false;

}

function addBrokers($transID,$broker,$brokerRate,$brokerTotal,$buyer) {
	//loop over any additional brokers
	//echo("buy: $buyer; count: ".count($broker)."<br />");
	for($i=0; $i<count(array($broker)); $i++) {
		$query = "INSERT INTO tblB2TTransaction_Brokers VALUES ('',\"$broker[$i]\",\"$brokerRate[$i]\",\"$brokerTotal[$i]\",\"$buyer\",\"$transID\");";
		//echo("adding: $query<br />\n");
		$result = mysqli_query($_SESSION['db'],$query);
	}
}

function updateBrokers($transID,$broker,$brokerRate,$brokerTotal,$buyer) {
	
	//loop over any additional brokers
	for($i=0; $i<count($broker); $i++) {		
		$brokerExists = checkBroker($transID,$broker[$i]);
		if($brokerExists != 0) {
			$query = "UPDATE tblB2TTransaction_Brokers SET fldBrokerRate=\"$brokerRate[$i]\",fldBrokerComm=\"$brokerTotal[$i]\",fldBuyer=\"$buyer\" WHERE pkRecordID=\"$brokerExists\"";
		}
		else {
			$query = "INSERT INTO tblB2TTransaction_Brokers VALUES ('',\"$broker[$i]\",\"$brokerRate[$i]\",\"$brokerTotal[$i]\",\"$buyer\",\"$transID\");";		
		}
		
		$result = mysqli_query($_SESSION['db'],$query);
	}	
}

function checkBroker($transID,$brokerID) {
	$query = "SELECT pkRecordID FROM tblB2TTransaction_Brokers WHERE fkBrokerID=$brokerID AND fkTransactionID=$transID";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 1) {
		list($recordID) = mysqli_fetch_row($result);
		return $recordID;
	}
	else
		return 0;
	
}

function getBrokers($brokerType,$transID,$showClearID,$tradeID,$status) {
	//get any additional brokers
	//$transactionID

	if($showClearID) {
		if($brokerType == "buy")
			$limit = "3,1";
		else
			$limit = "1";
	}
	else
		$limit = "2";


	if($_SESSION["PROD"]) {
		/*  removed on 8/2/23 - not sure why this was originally needed, but prevented add'l brokers from showing up properly
		
		if($tradeID % 2 == 1 && $brokerType == "buy")
			$tradeID = $tradeID-1;
		else if($tradeID % 2 == 0 && $brokerType == "sell")
			$tradeID = $tradeID+1;
			*/
	}
	else {
		if($tradeID % 2 == 0 && $brokerType == "buy")
			$tradeID = $tradeID-1;
		else if($tradeID % 2 == 1 && $brokerType == "sell")
			$tradeID = $tradeID+1;
	}
	
		
	if($brokerType == "buy") {
		//echo("buy");
		//$brokerQuery = "SELECT tb.* FROM tblB2TTransaction_Brokers tb, tblB2TTransaction t WHERE tb.fkTransactionID=t.pkTransactionID AND t.fldTransactionNum=$transactionID AND fldBuyer=1";
		//$brokerQuery = "SELECT tb.* FROM tblB2TTransaction_Brokers tb, (SELECT * FROM tblB2TTransaction WHERE pkTransactionID=$tradeID ORDER BY pkTransactionID DESC LIMIT $limit) t WHERE tb.fkTransactionID=t.pkTransactionID AND fldBuyer=1";
		$brokerQuery = "SELECT DISTINCT tb.fkBrokerID,tb.fldBrokerRate,tb.fldBrokerComm FROM tblB2TTransaction_Brokers tb, tblB2TTransaction t WHERE tb.fkTransactionID=t.pkTransactionID AND fldBuyer=1 AND fldTransactionNum=$transID AND pkTransactionID=$tradeID AND fldStatus=$status";
	}
	else if($brokerType == "sell") {
		//echo("sell");
		//$brokerQuery = "SELECT tb.* FROM tblB2TTransaction_Brokers tb, tblB2TTransaction t WHERE tb.fkTransactionID=t.pkTransactionID AND t.fldTransactionNum=$transactionID AND fldBuyer=0";
 		//$brokerQuery = "SELECT tb.* FROM tblB2TTransaction_Brokers tb, (SELECT * FROM tblB2TTransaction WHERE pkTransactionID=$tradeID ORDER BY pkTransactionID DESC LIMIT $limit) t WHERE tb.fkTransactionID=t.pkTransactionID AND fldBuyer=0";
		$brokerQuery = "SELECT DISTINCT tb.fkBrokerID,tb.fldBrokerRate,tb.fldBrokerComm FROM tblB2TTransaction_Brokers tb, tblB2TTransaction t WHERE tb.fkTransactionID=t.pkTransactionID AND fldBuyer=0 AND fldTransactionNum=$transID AND pkTransactionID=$tradeID AND fldStatus=$status";
	}
	
	//echo($brokerQuery);
	$brokerResult = mysqli_query($_SESSION['db'],$brokerQuery);
	$i=1;
	$output = "";
	//echo("<script language=\"javascript\" type=\"text/javascript\">\n");
	//echo("function initBrokers() {\n");
	while($brokerSet = mysqli_fetch_row($brokerResult)) {
		//$brokerSet = mysqli_fetch_row($brokerResult);
		/*
		0 = broker id
		1 = rate
		2 = commission
		*/
		//echo($brokerSet[1]);
		if(!$_SESSION["PROD"])
			$requestURL = "http://test.greenlightcommodities.com/util/ajax.php?addBroker=1&brokerType=".$brokerType."&brokerCount=".$i."&selectedBroker=".$brokerSet[0]."&brokerRate=".$brokerSet[1]."&brokerComm=".$brokerSet[2];
		else
			$requestURL = "http://trade.greenlightcommodities.com/util/ajax.php?addBroker=1&brokerType=".$brokerType."&brokerCount=".$i."&selectedBroker=".$brokerSet[0]."&brokerRate=".$brokerSet[1]."&brokerComm=".$brokerSet[2];
		$output.= file_get_contents($requestURL);
		//echo("<p>".$requestURL."</p>");
		
		/*$ch = curl_init($target_url);
		$output .= curl_exec($ch);
		curl_close($ch);*/
		//echo("url: ".$requestURL);
		/*$handle = fopen($requestURL, "r");
		$output.= fread($handle, filesize($target_url));
		fclose($handle);*/
		
		//echo("var params".$i." = new Array(\"$brokerSet[1]\",\"$brokerSet[2]\",\"$brokerSet[3]\",\"$brokerSet[4]\");\n");
		/*if($brokerSet[4] == 1)
			$type = "Buy";
		else
			$type = "Sell";*/
		//echo("addBroker(\"more".$type."Brokers\",params".$i.");\n");
		$i++;
	}

	echo("<script language=\"javascript\" type=\"text/javascript\">\n");
	if($brokerType == "buy")
		echo("buyBrokerCount = ".$i.";\n");
	else if($brokerType == "sell")
		echo("sellBrokerCount = ".$i.";\n");
	echo("</script>\n");
	
	//echo("}\n");
	//echo("window.onload = setTimeout(\"initBrokers()\",3000);\n");
	/*echo("</script>\n");*/
	echo($output);
}

function getAdditionalBrokerFees($transactionID) {
	$query = "SELECT sum(fldBrokerComm) FROM tblB2TTransaction_Brokers WHERE fkTransactionID=".$transactionID;
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count > 0){
		list($totalAmt) = mysqli_fetch_row($result);
	}
	else
		$totalAmt = 0;
	
	return $totalAmt;
}

function getAdditionalBrokerFeesByCompany($companyID,$buy_sell,$startDate,$endDate) {
	if($buy_sell == "buy"){
		//additional buy brokers
		$queryAdditional = "SELECT sum(tb.fldBrokerComm) FROM tblB2TTransaction t, tblB2TClient c, tblB2TCompany co, tblB2TTransaction_Brokers tb WHERE tb.fkTransactionID=t.pkTransactionID AND t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID AND co.pkCompanyID=$companyID AND t.fldStatus=".$_SESSION["CONST_LIVE"]." AND fldBuyBrokerComm > 0";
		if(isset($startDate))
			$queryAdditional.= " AND fldConfirmDate>='".reverseDate($startDate)."'";
		if(isset($endDate))
			$queryAdditional.= " AND fldConfirmDate<='".reverseDate($endDate)."'";
		$resultAdditional = mysqli_query($_SESSION['db'],$queryAdditional);
		if(mysqli_num_rows($resultAdditional) > 0)
			list($additionalBrokerFees) = mysqli_fetch_row($resultAdditional);
		else
			$additionalBrokerFees = 0;
	}
	else if($buy_sell == "sell") {
		//additional sell brokers
		$queryAdditional2 = "SELECT sum(tb.fldBrokerComm) FROM tblB2TTransaction t, tblB2TClient c, tblB2TCompany co, tblB2TTransaction_Brokers tb WHERE tb.fkTransactionID=t.pkTransactionID AND t.fkSellerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID AND co.pkCompanyID=$companyID AND t.fldStatus=".$_SESSION["CONST_LIVE"]." AND fldSellBrokerComm > 0";
		if(isset($startDate))
			$queryAdditiona2.= " AND fldConfirmDate>='".reverseDate($startDate)."'";
		if(isset($endDate))
			$queryAdditiona2.= " AND fldConfirmDate<='".reverseDate($endDate)."'";
		$resultAdditional2 = mysqli_query($_SESSION['db'],$queryAdditional2);
		if(mysqli_num_rows($resultAdditional2) > 0)
			list($additionalBrokerFees) = mysqli_fetch_row($resultAdditional2);
		else
			$additionalBrokerFees = 0;	
	}
	
	return $additionalBrokerFees;
}


function showClearedTrade($clearID,$buyerID,$sellerID) {
	
	//echo("<p>BuyerID: $buyerID | SellerID: $sellerID | NYMEX: ".$_SESSION["NYMEX_ID"]." | ICE: ".$_SESSION["ICE_ID"]."</p>\n");	
	
	// Initialize all selection variables
	$defaultSelected = "";
	$nymexSelected = "";
	$iceSelected = "";
	$ngxSelected = "";
	$nodalSelected = "";
	$nasdaqSelected = "";
	$otcSelected = "";
	$cleared = false;
	
	// Only auto-select if we have actual buyer/seller IDs (existing trades)
	if($buyerID && $sellerID) {
		if($buyerID == $_SESSION["NYMEX_ID"] || $sellerID == $_SESSION["NYMEX_ID"]) {
			 $nymexSelected = "selected=\"selected\"";
			 $cleared = true;		
		}
		else if($buyerID == $_SESSION["ICE_ID"] || $sellerID == $_SESSION["ICE_ID"]) {
			$iceSelected = "selected=\"selected\"";
			$cleared = true;
		}
		else if($buyerID == $_SESSION["NGX_ID"] || $sellerID == $_SESSION["NGX_ID"]) {
			$ngxSelected = "selected=\"selected\"";
			$cleared = true;
		}
		else if($buyerID == $_SESSION["NODAL_ID"] || $sellerID == $_SESSION["NODAL_ID"]) {
			$nodalSelected = "selected=\"selected\"";
			$cleared = true;
		}
		else if($buyerID == $_SESSION["NASDAQ_ID"] || $sellerID == $_SESSION["NASDAQ_ID"]) {
			$nasdaqSelected = "selected=\"selected\"";
			$cleared = true;
		}
		else {
			// If none of the specific clearing house IDs match, it's an OTC trade
			$otcSelected = "selected=\"selected\"";
			$cleared = false; // OTC trades are not cleared through an exchange
		}
	} else {
		// For new trades, default to no selection
		$defaultSelected = "selected=\"selected\"";
	}
	
	echo("<select name=\"clearedTrade\" id=\"clearedTrade\" onchange=\"showClearField(this);\">\n");
	echo("<option value=\"0\" $defaultSelected>-</option>\n");
	echo("<option value=\"OTC\" $otcSelected>OTC</option>\n");
	echo("<option value=\"NYMEX\" $nymexSelected>NYMEX</option>\n");
	echo("<option value=\"ICE\" $iceSelected>ICE</option>\n");
	echo("<option value=\"NGX\" $ngxSelected>NGX</option>\n");
	echo("<option value=\"NODAL\" $nodalSelected>NODAL</option>\n");
	echo("<option value=\"NASDAQ\" $nasdaqSelected>NASDAQ</option>\n");
	echo("</select>\n");
	
	return $cleared;
}

function getContraParty($clearedTrade) {
	if($clearedTrade == "OTC")
		return null; // OTC trades don't have a clearing house contra party
	else if($clearedTrade == "NYMEX")
		return $_SESSION["NYMEX_ID"];
	else if($clearedTrade == "ICE")
		return $_SESSION["ICE_ID"];
	else if($clearedTrade == "NGX")
		return $_SESSION["NGX_ID"];
	else if($clearedTrade == "NODAL")
		return $_SESSION["NODAL_ID"];
	else if($clearedTrade == "NASDAQ")
		return $_SESSION["NASDAQ_ID"];
}

function verifyParty($tradeID,$party) {
	
	$query = "SELECT fldLegID,fldTransactionNum FROM tblB2TTransaction WHERE pkTransactionID=$tradeID";
	//echo($query."<br />");
	$result = mysqli_query($_SESSION['db'],$query);
	list($legID,$transID) = mysqli_fetch_row($result);
	
	if(substr($legID,0) % 2 == 1) { //buy leg
		$buyLegID = substr($legID,0,1);
		$sellLegID = $buyLegID + 1;
	}
	else {
		$sellLegID = substr($legID,0,1);
		$buyLegID = $sellLegID - 1;
	}
	
	if($party == "buyer") {
		$query = "SELECT DISTINCT fkBuyerID FROM tblB2TTransaction WHERE fldTransactionNum=".$transID." AND (fldLegID='".$buyLegID."')";
		//echo($query."<br />");
		$result = mysqli_query($_SESSION['db'],$query);
		list($partyID) = mysqli_fetch_row($result);
	
		return $partyID;
	}
	else if($party == "seller") {
		$query = "SELECT DISTINCT fkSellerID FROM tblB2TTransaction WHERE fldTransactionNum=".$transID." AND (fldLegID='".$sellLegID."b')";
		//echo($query."<br />");
		$result = mysqli_query($_SESSION['db'],$query);
		list($partyID) = mysqli_fetch_row($result);
	
		return $partyID;
	}
		
}

function verifyBroker($tradeID,$party) {
	
	$query = "SELECT fldLegID,fldTransactionNum FROM tblB2TTransaction WHERE pkTransactionID=$tradeID";
	$result = mysqli_query($_SESSION['db'],$query);
	list($legID,$transID) = mysqli_fetch_row($result);
	
	if(substr($legID,0) % 2 == 1) { //buy leg
		$buyLegID = substr($legID,0,1);
		$sellLegID = $buyLegID + 1;
	}
	else {
		$sellLegID = substr($legID,0,1);
		$buyLegID = $sellLegID - 1;
	}
	
	if($party == "buyer") {
		$query = "SELECT DISTINCT fkBuyingBroker,fldBuyBrokerRate,fldBuyBrokerComm FROM tblB2TTransaction WHERE fldTransactionNum=".$transID." AND fldLegID='".$buyLegID."'";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		list($brokerID,$brokerRate,$brokerComm) = mysqli_fetch_row($result);
		$resultSet = array("$brokerID","$brokerRate","$brokerComm");
	
		return $resultSet;
	}
	else if($party == "seller") {
		$query = "SELECT DISTINCT fkSellingBroker,fldSellBrokerRate,fldSellBrokerComm FROM tblB2TTransaction WHERE fldTransactionNum=".$transID." AND fldLegID='".$sellLegID."b'";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		list($brokerID,$brokerRate,$brokerComm) = mysqli_fetch_row($result);
		$resultSet = array("$brokerID","$brokerRate","$brokerComm");
		
		return $resultSet;
	}
	
}

/* check if broker is flag for cleared trade*/
function isCleared($brokerID) {
	
	if($brokerID == $_SESSION["NYMEX_ID"] || $brokerID == $_SESSION["ICE_ID"] || $brokerID == $_SESSION["NGX_ID"] || $brokerID == $_SESSION["NODAL_ID"] || $brokerID == $_SESSION["NASDAQ_ID"])
		return true;
	
	return false;
}

/* check if broker is flag for OTC trade*/
function isOTC($brokerID) {
	
	if($brokerID == $_SESSION["OTC_ID"])
		return true;
	
	return false;
}

/* check if its nymex cleared specifically*/
function isNymexCleared($brokerID) {
		 if($brokerID == $_SESSION["NYMEX_ID"])
      		return true;
      	
      	return false;
}

function removeSpecialChars($input) {
	
	$charsArr = array(",","$");
	return str_replace($charsArr,"",$input);
		
}

//populate custom fields for crude oil trades using custom fields table
function populateCustomFields($post,$transID) {
    $query = "INSERT INTO tblB2TCustomFields VALUES ('',\"".$post["productCustom"]."\",\"".$post["priceCustom"]."\",\"".$post["deliveryPoint"]."\",\"".
            $post["additionalInfo"]."\",\"".$transID."\")";
    //echo($query."<br>");
    $result = mysqli_query($_SESSION['db'],$query);
    if(!$result)
        echo("An error occurred on custom field insert");
}

//update custom fields for crude oil trades using custom fields table
function updateCustomFields($post,$updateID) {
    $query = "UPDATE tblB2TCustomFields SET fldProduct=\"".$post["productCustom"]."\",fldPrice=\"".$post["priceCustom"]."\",fldDeliveryPoint=\"".$post["deliveryPoint"]."\",".
            "fldAdditionalInfo=\"".$post["additionalInfo"]."\" WHERE fkTransID=\"".$updateID."\"";
    //echo($query)."<br>";
    $result = mysqli_query($_SESSION['db'],$query);
    if(!$result)
        echo("An error occurred on custom field insert");
}

function verifyLimit($legID,$buyerID,$sellerID) {
	
	if(isCleared($buyerID) || isCleared($sellerID)){
		if(intval($legID) % 2 == 0)
			return "1";
		else
			return "3,1";
	}
	else {
		if($legID % 2 == 0)
			return "1"; //limit
		else if($legID % 2 == 1)
			return "1"; //offset, limit
	}
}


function checkHub($locationID) {
	
	$locSet = array("139","140","141","142","143","144","145","146"); //location id's for all kv hub financial deals
	
	return in_array($locationID,$locSet);
	
}

function locationStatus($status) {
	
	if($status == 1)
		return "Active";
	else
		return "Inactive";
}


function translateProduct($product) {
	
	$query = "SELECT fldProductGroup FROM tblB2TTradeType WHERE pkTypeID=".$product;
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@list($productGroup) = mysqli_fetch_row($result);
	
	// All trades now use generic "Trade Confirmation"
	return "Trade";
	
}

function getCommodityTypeID($index) {
	
	if( translateProduct($index) == "Power" )
		return 2;
	else
		return 1;
	
}

function getTradeUnits($tradeTypeID) {
	$query = "SELECT fldUnits FROM tblB2TTradeType WHERE pkTypeID = $tradeTypeID";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count > 0)
		list($units) = mysqli_fetch_row($result);
	else
		$units = "";
	
	return $units;
}

function getTradeUnitsID($tradeTypeID) {
	$query = "SELECT fldCHUnitsID FROM tblB2TTradeType WHERE pkTypeID = $tradeTypeID";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count > 0)
		list($units) = mysqli_fetch_row($result);
	else
		$units = "";
	
	return $units;
		
}

function deleteAddlBroker($tradeID,$brokerID,$buyer) {
	
	if($buyer == "buy")
		$buyer = 1;
	else if($buyer == "sell")
		$buyer = 0;
		
	$query = "DELETE FROM tblB2TTransaction_Brokers WHERE fkTransactionID=$tradeID AND fkBrokerID=$brokerID AND fldBuyer=$buyer";
	//echo($query."<br>");
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_affected_rows($_SESSION['db']);
	
	if($count > 0)
		return true;
	
	return false;
}

/*function clearedTrade($transID) {
	
	$query = "SELECT fkBuyingBroker,fkSellingBroker FROM tblB2TTransaction WHERE fldTransactionNum=$transID";
	echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	
	while($resultSet = mysqli_fetch_row($result)){
		if(isCleared($resultSet[0]))
			return true;
		else if(isCleared($resultSet[1]))
			return true;
		else
			return false;
	}	
	
}*/


function generateResend($clientID, $transID, $resendType) {

	$sendCount = 0;
	
	//added for new company prompt
	$companyArr = array();
	$coTotal = @count($clientID); //checkbox selection for who to send to

	//echo("total: ".$coTotal."<br><br>");

	if(@count($coTotal) > 0){
		//append additional query params for company specified
		$query_extra = "(";
		for($j=0; $j<$coTotal; $j++) {
			$query_extra .= "co.pkCompanyID=$clientID[$j] ";
			
			if($j < ($coTotal -1))
				$query_extra .= " OR ";
		}
		$query_extra .= ")";
	}
	
	if($resendType == "void" || $resendType == "sendToMe") //override for void transaction
		$coTotal = count($transID);
	
	for($i=0; $i<$coTotal; $i++){
		//$checkQuery = "SELECT t.fldStatus FROM $tblTransaction t, tblB2T WHERE t.pkTransactionID=".$transID[$i];
		$checkQuery = "SELECT t.fldStatus, co.pkCompanyID FROM tblB2TTransaction t, tblB2TClient c, tblB2TCompany co WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID) OR
			(t.fkSellerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID)) AND t.pkTransactionID=".$transID[$i];
		if($query_extra != "")
			$checkQuery.= " AND ".$query_extra;

		//echo($checkQuery);
		$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
		@list($tradeStatus,$companyID) = mysqli_fetch_row($resultQuery); //get trade status

		
		if($resendType == "void") {
			$sendCount++;
			resendTradeConfirm($transID[$i],2);
		}
		else if($resendType == "sendToMe") {
			$sendCount++;
			resendTradeConfirm($transID[$i],1);
		}
		else if($tradeStatus == $_SESSION["CONST_LIVE"] || $tradeStatus == $_SESSION["CONST_VOID"]) {
			//echo("<p>resending...</p>");
			$sendCount++;
			
			resendTradeConfirm($transID[$i],0);
		}
	}
	
	return $sendCount;

}

//check to see if a leg is buy or sell
function isBuyLeg($legID) {
	$integer = intval($legID);
	
	if($integer % 2 == 1)
		return 1;
	else
		return 0;

}

function tradeIsActive($tradeStatus) {
	
	if($tradeStatus == $_SESSION["CONST_LIVE"] || $tradeStatus ==$_SESSION["CONST_SETTLED"])
		return 1;
	else
		return 0;
	
}

// Custom commodity fields functions removed - table not being used properly

function populateGeoRegion($regionID) {
	$queryRegion = "SELECT DISTINCT fldGeoRegion FROM tblB2TLocation WHERE fldGeoRegion IS NOT NULL AND fldGeoRegion != '' ORDER BY fldGeoRegion ASC";
	$resultRegion = mysqli_query($_SESSION['db'],$queryRegion);
	@$regionCount = mysqli_num_rows($resultRegion);
	
	if($regionCount > 0){
		echo("<option value=\"\">Select</option>\n");
		
		while($resultSet = mysqli_fetch_row($resultRegion)){
			$selected = "";
			if($regionID == $resultSet[0])
				$selected = "selected=\"selected\"";
			
			echo("<option value=\"$resultSet[0]\" $selected>$resultSet[0]</option>\n");
		}
	}
}

function populateTimezone($timezoneID) {
	$timezones = array(
		"EST" => "Eastern Standard Time",
		"CST" => "Central Standard Time", 
		"MST" => "Mountain Standard Time",
		"PST" => "Pacific Standard Time",
		"GMT" => "Greenwich Mean Time",
		"UTC" => "Coordinated Universal Time"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($timezones as $value => $label) {
		$selected = "";
		if($timezoneID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label ($value)</option>\n");
	}
}

function populateHourTemplate($templateID) {
	$templates = array(
		"EAST" => "Eastern Hours",
		"WEST" => "Western Hours",
		"24H" => "24 Hour",
		"CUSTOM" => "Custom Hours"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($templates as $value => $label) {
		$selected = "";
		if($templateID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label</option>\n");
	}
}

function populateUnit($unitID) {
	$units = array(
		"MMBTU" => "MMBTU",
		"Block" => "Block",
		"MWH" => "MWH",
		"Bbls" => "Barrels"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($units as $value => $label) {
		$selected = "";
		if($unitID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label</option>\n");
	}
}

function populateGlobalRegion($regionID) {
	$regions = array(
		"NORTH_AMERICA" => "North America",
		"EUROPE" => "Europe",
		"ASIA" => "Asia",
		"AUSTRALIA" => "Australia"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($regions as $value => $label) {
		$selected = "";
		if($regionID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label</option>\n");
	}
}

function populateSubRegion($subRegionID) {
	$subRegions = array(
		"NORTHEAST" => "Northeast",
		"SOUTHEAST" => "Southeast",
		"MIDWEST" => "Midwest",
		"SOUTHWEST" => "Southwest",
		"NORTHWEST" => "Northwest"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($subRegions as $value => $label) {
		$selected = "";
		if($subRegionID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label</option>\n");
	}
}

function populateRegion($regionID) {
	$regions = array(
		"EAST" => "East",
		"WEST" => "West",
		"CENTRAL" => "Central"
	);
	
	echo("<option value=\"\">Select</option>\n");
	foreach($regions as $value => $label) {
		$selected = "";
		if($regionID == $value)
			$selected = "selected=\"selected\"";
		
		echo("<option value=\"$value\" $selected>$label</option>\n");
	}
}

// RECs functions removed - not being used

?>