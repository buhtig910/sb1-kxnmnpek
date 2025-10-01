<?php

if(strpos($PHP_SELF,"dashboard.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}

if($p == ""){
	?>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<?php
}

$message = "";

// CSRF Protection temporarily disabled for debugging

//echo("action: ".$_POST["action"]);

if($_POST["action"] == "cancelTrade") {
	//REMOVE-CANCEL TRADE IN DASHBOARD
	$total = count($_POST["transID"]);
	if($total > 0){
		for($i=0; $i<$total; $i++){
		
			$checkQuery = "SELECT fldStatus,fldTransactionNum FROM $tblTransaction WHERE pkTransactionID = ?";
			$resultQuery = safeQuery($_SESSION['db'], $checkQuery, array($_POST["transID"][$i]), 'i');
			@list($tradeStatus,$transactionNum) = mysqli_fetch_row($resultQuery);
		
			if($tradeStatus == $_SESSION["CONST_LIVE"]) { //cancel a live trade
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_LIVE_CANCELLED"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_LIVE_CANCELLED"]), 
					'fldTransactionNum = ? AND fldStatus = ?', array($transactionNum, $_SESSION["CONST_LIVE"]));
			}
			else if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //cancel a confirmed trade
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_CONFIRM_CANCELLED"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_CONFIRM_CANCELLED"]), 
					'fldTransactionNum = ? AND fldStatus = ?', array($transactionNum, $_SESSION["CONST_CONFIRMED"]));
			}
			else { //cancel a pending trade
				//echo("Trans Num: ".$transactionNum);
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_CANCELLED"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_CANCELLED"]), 
					'fldTransactionNum = ? AND (fldStatus = ? OR fldStatus = ?)', array($transactionNum, $_SESSION["CONST_PENDING"], $_SESSION["CONST_CONFIRMED"]));
			}
		}
				
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully cancelled $total trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to cancel trades</p></div>";
	}
}
else if($_POST["action"] == "deleteTrade") {
	//DELETE TRADE IN DASHBOARD (Super Admin only)
	if(!isSuperAdmin()) {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Access Denied: Only Super Administrators can delete trades</p></div>";
	} else {
		$total = 0;
		$transIDs = $_POST["transID"];
		foreach($transIDs as $transID) {
			// Get trade details for logging
			$query = "SELECT fldTransactionNum, fldBuyBroker, fldSellBroker, fldProduct, fldQuantity, fldPrice FROM tblB2TTransaction WHERE pkTransactionID = $transID";
			$result = mysqli_query($_SESSION['db'], $query);
			
			if ($result && mysqli_num_rows($result) > 0) {
				$tradeData = mysqli_fetch_row($result);
				
				// Log the deletion
				$logQuery = "INSERT INTO tblB2TLogging (fldBrokerID, fldAction, fldDetails, fldTimestamp) VALUES (" . $_SESSION["brokerID"] . ", 'DELETE_TRADE', 'Deleted trade " . $tradeData[0] . " (Buyer: " . $tradeData[1] . ", Seller: " . $tradeData[2] . ", Product: " . $tradeData[3] . ", Qty: " . $tradeData[4] . ", Price: " . $tradeData[5] . ")', NOW())";
				mysqli_query($_SESSION['db'], $logQuery);
			} else {
				// Log deletion without details if trade not found
				$logQuery = "INSERT INTO tblB2TLogging (fldBrokerID, fldAction, fldDetails, fldTimestamp) VALUES (" . $_SESSION["brokerID"] . ", 'DELETE_TRADE', 'Deleted trade ID: $transID (trade not found in database)', NOW())";
				mysqli_query($_SESSION['db'], $logQuery);
			}
			
			// Delete the trade
			$deleteQuery = "DELETE FROM tblB2TTransaction WHERE pkTransactionID = $transID";
			$deleteResult = mysqli_query($_SESSION['db'], $deleteQuery);
			
			if($deleteResult) {
				$total++;
			}
		}
		
		if($total > 0) {
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully deleted $total trades</p></div>";
		} else {
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to delete trades</p></div>";
		}
	}
}
else if($_POST["action"] == "voidTrade") {
	//VOID TRADE IN DASHBOARD
	$sendCount = 0;
	$total = count($_POST["transID"]);
	
	if($total > 0){
		require("util/confirm.php"); //get file containing resend function
		
		for($i=0; $i<$total; $i++){
		
			$checkQuery = "SELECT fldStatus,fldTransactionNum FROM $tblTransaction WHERE pkTransactionID = ?";
			$resultQuery = safeQuery($_SESSION['db'], $checkQuery, array($_POST["transID"][$i]), 'i');
			@list($tradeStatus,$transactionNum) = mysqli_fetch_row($resultQuery);
		
			if($tradeStatus == $_SESSION["CONST_LIVE"]) { //void a live trade
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'fldTransactionNum = ? AND fldStatus = ?', array($transactionNum, $_SESSION["CONST_LIVE"]));
			}
			else if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //void a confirmed trade
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'fldTransactionNum = ? AND fldStatus = ?', array($transactionNum, $_SESSION["CONST_CONFIRMED"]));
			}
			else { //void a pending trade
				//echo("Trans Num: ".$transactionNum);
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
				$result2 = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_VOID"]), 
					'fldTransactionNum = ? AND (fldStatus = ? OR fldStatus = ?)', array($transactionNum, $_SESSION["CONST_PENDING"], $_SESSION["CONST_CONFIRMED"]));
			}
		}
		
		$sendCount = generateResend($_POST["clientID"], $_POST["transID"], "void"); //send voided trade email
		
		if($sendCount > 0)
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully voided $total trades</p></div>";
		else
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to void trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to void trades</p></div>";
	}
}
else if($_POST["action"] == "deleteTrade") {
	//REMOVE-DELETE TRADE IN DASHBOARD
	$total = count($_POST["transID"]);
	if($total > 0){
		for($i=0; $i<$total; $i++){
		
			$checkQuery = "SELECT fldStatus FROM $tblTransaction WHERE pkTransactionID = ?";
			$resultQuery = safeQuery($_SESSION['db'], $checkQuery, array($_POST["transID"][$i]), 'i');
			@list($tradeStatus) = mysqli_fetch_row($resultQuery);
			
			if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //trade is confirmed, cancel it
				$result = safeUpdate($_SESSION['db'], $tblTransaction, 
					array('fldStatus' => $_SESSION["CONST_CONFIRM_CANCELLED"]), 
					'pkTransactionID = ?', array($_POST["transID"][$i]));
			}
			else {
				$result = safeDelete($_SESSION['db'], $tblTransaction, 'pkTransactionID = ?', array($_POST["transID"][$i]));
			}
		}
		
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully deleted $total trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to delete trades</p></div>";
	}
}
else if($_POST["action"] == "settleTrade") {
	//SETTLE TRADE IN DASHBOARD
	$total = count($_POST["transID"]);
	if($total > 0){
		for($i=0; $i<$total; $i++){		
			settleTrade($_POST["transID"][$i]);
		}
		
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully settled $total trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to settle trades</p></div>";
	}
}
else if($_POST["action"] == "confirmTrade"){
	//CONFIRM TRADE
	$total = count($_POST["transID"]);
	//echo("<br>transID: ".$_POST["transID"][0]);

	if($total > 0){
		for($i=0; $i<$total; $i++){
			
			$checkQuery = "SELECT fldStatus FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus) = mysqli_fetch_row($resultQuery);

			if($tradeStatus == $_SESSION["CONST_PENDING"]){
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION['CONST_CONFIRMED']." WHERE pkTransactionID=".$_POST["transID"][$i];
				$result = mysqli_query($_SESSION['db'],$query);
			}
		}
		
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully confirmed $total trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to confirm trades</p></div>";
	}
}
else if($_POST["action"] == "resendConfirm"){
	$total = count($_POST["transID"]);
	if($total > 0){
		require("util/confirm.php"); //get file containing resend function
		
		$sendCount = generateResend($_POST["clientID"], $_POST["transID"], ""); //generate list and send
			
		if($sendCount == 0)
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
	}
}
else if($_POST["action"] == "resendConfirmToMe"){
	$total = count($_POST["transID"]);
	if($total > 0){
		require("util/confirm.php"); //get file containing resend function

		// For resend to me, we need to process each trade individually
		$sendCount = 0;
		for($i=0; $i<$total; $i++){
			$tradeID = $_POST["transID"][$i];
			
			// Check trade status first
			$checkQuery = "SELECT fldStatus FROM $tblTransaction WHERE pkTransactionID=$tradeID";
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus) = mysqli_fetch_row($resultQuery);
			
			// Allow resend for CONFIRMED, LIVE, or VOID trades
			if($tradeStatus == $_SESSION["CONST_LIVE"] || $tradeStatus == $_SESSION["CONST_VOID"] || $tradeStatus == $_SESSION["CONST_CONFIRMED"]){
				resendTradeConfirm($tradeID, 1);
				$sendCount++;
			}
		}
		
		if($sendCount == 0)
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be CONFIRMED, LIVE, or VOID to resend a trade confirm.</p></div>";
		else
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully processed $sendCount trades for resend.</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">No trades selected for resend.</p></div>";
	}
}

//update sort order, field
if($sortOrder != "")
	$_SESSION["sortOrder2"] = $sortOrder;

if(!isset($_SESSION["s_status"]))
	$_SESSION["s_status"] = 0;
else if($_POST["statusID"] != "")
	$_SESSION["s_status"] = $_POST["statusID"];

if(isset($_POST['tradeID']))
	$_SESSION['s_tradeID'] = $_POST['tradeID'];
if(isset($_POST['startDate']))
	$_SESSION['s_startDate'] = $_POST['startDate'];
if(isset($_POST['endDate']))
	$_SESSION['s_endDate'] = $_POST['endDate'];
if(isset($_POST['companyName']))
	$_SESSION['s_companyName'] = $_POST['companyName'];
if(isset($_POST['productID']))
	$_SESSION['s_productID'] = $_POST['productID'];
if(isset($_POST['productGroup']))
	$_SESSION['s_productGroup'] = $_POST['productGroup'];
if(isset($_POST['brokerListID']))
	$_SESSION['s_brokerListID'] = $_POST['brokerListID'];
if(isset($_POST['non-cleared-trades']))
	$_SESSION['nonCleared'] = $_POST['non-cleared-trades'];

if($sortField != ""){
	if($sortField == "tradeID")
		$_SESSION["sortBy"] = "fldTransactionNum";
	else if($sortField == "date")
		$_SESSION["sortBy"] = "fldConfirmDate";
	else if($sortField == "prodGroup")
		$_SESSION["sortBy"] = "fldProductGroup";
	else if($sortField == "prod")
		$_SESSION["sortBy"] = "fldProductName";
	else if($sortField == "status")
		$_SESSION["sortBy"] = "fldStatus";
	else if($sortField == "price")
		$_SESSION["sortBy"] = "fldBuyerPrice";
	else if($sortField == "block")
		$_SESSION["sortBy"] = "fldBlock";
	else if($sortField == "qty")
		$_SESSION["sortBy"] = "fldUnitQty";
	else if($sortField == "option")
		$_SESSION["sortBy"] = "fkOptionTypeID";
	else if($sortField == "startDate")
		$_SESSION["sortBy"] = "fldStartDate";
	else if($sortField == "endDate")
		$_SESSION["sortBy"] = "fldEndDate";
	else if($sortField == "strike")
		$_SESSION["sortBy"] = "fldStrike";
}
else{
	$_SESSION["sortBy"] = "fldConfirmDate DESC,fldTransactionNum";
	$_SESSION["sortOrder2"] = "DESC";
	$last = "date";
}

?>

	<?php echo($message); ?>
	
	<div class="clear" id="ajaxResponse"></div>

	<div class="createNewTradeContainer" style="height: 30px; margin-top: -10px;">
		<div class="floatRight">
			<form name="selectTradeType" action="" method="post">
			<span class="fieldLabel">Create New Trade:</span>
			<select name="tradeType">
				<?php
					//BUILD NEW TRADE TEMPLATE LIST
					$query = "SELECT * FROM ".$tblTradeType." ORDER BY fldTradeName ASC";
					$result = mysqli_query($_SESSION['db'],$query);
					
					if($result){
						
						while($resultSet = mysqli_fetch_row($result)){
							/*
							0 = typeID
							1 = tradeName
							2 = short name
							*/
							
							echo("<option value=\"$resultSet[0]\">$resultSet[1]</option>\n");
						}
						
					}
					
				?>
			</select> &nbsp;
			<input type="button" name="selectType" value="Create Trade" onclick="launchTrade(document.selectTradeType.tradeType.value);" class="buttonBlue" style="margin-right: 0;" />
			</form>
		</div>
		
		<!-- <h2>My Trades</h2> -->
	</div>
	
	
	
	
	
	<link rel="stylesheet" type="text/css" href="css/fonts-min.css" />
	<link rel="stylesheet" type="text/css" href="css/calendar.css" />
	<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
	<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>
	<script type="text/javascript" src="scripts/ajax.js"></script>
	<script type="text/javascript" src="scripts/scripts.js"></script>
	
	<script language="javascript" type="text/javascript">
		filtersArray = new Array("filters","filtersClose","filtersOpen");
	</script>
	
	<?php
		if(!isset($_SESSION["filters"]))
			$_SESSION["filters"] = "none";
		

	?>

	
	<form name="tradeFilters" id="tradeFilters" action="" method="post">
	<div class="tradeFilters">
		<div id="filtersClose" style="display: <?php if($_SESSION["filters"] == "") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Trade Filters</a></h3>
		</div>
		<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Trade Filters</a></h3>
		</div>
		
		<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
		<table border="0" cellspacing="3" cellpadding="0">
		  <tr>
			<td class="fieldLabel">Trade ID: </td>
			<td><input name="tradeID" type="text" id="tradeID" size="15" value="<?php echo($_SESSION['s_tradeID']); ?>" /></td>
			<td class="spacer">&nbsp;</td>
			<td class="fieldLabel">Trade Date From: </td>
			<td class="yui-skin-sam" style="position: relative;"><input name="startDate" id="startDate" type="text" size="10" value="<?php echo($_SESSION['s_startDate']); ?>" />
                <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" />
                <div id="cal1Container"></div>
                
                <script language="javascript" type="text/javascript">
		
					function mySelectHandler(type,args,obj) {
						var dates = args[0]; 
						var date = dates[0]; 
						var year = date[0], month = date[1], day = date[2];
						
						if(month.toString().length == 1)
							month = "0" + month;
						if(day.toString().length == 1)
							day = "0" + day;

						var dateStr = month + "/" + day + "/" + year;								
						displayDate("startDate",dateStr);
						this.hide();
					};

					
					YAHOO.namespace("example.calendar"); 
					 
					YAHOO.example.calendar.init = function() { 
						YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","cal1Container", { 
							title:"Choose a date:", 
							close:true
						});
						YAHOO.example.calendar.cal1.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal1, true);
						YAHOO.example.calendar.cal1.render();
						
						// Position calendar container relative to the calendar icon
						var icon = document.getElementById('show1up');
						var container = document.getElementById('cal1Container');
						if (icon && container) {
							var rect = icon.getBoundingClientRect();
							container.style.position = 'fixed';
							container.style.top = (rect.bottom + 5) + 'px';
							container.style.left = rect.left + 'px';
						}
						 
						// Listener to show the single page Calendar when the button is clicked 
						YAHOO.util.Event.addListener("show1up", "click", YAHOO.example.calendar.cal1.show, YAHOO.example.calendar.cal1, true); 
					} 
					
					YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
				
				</script>            </td>
			<td class="spacer">&nbsp;</td>
		    <td class="fieldLabel">Status:</td>
		    <td><select name="statusID" id="statusID">
                <?php
					getStatusList($_SESSION["s_status"]);						
				  ?>
            </select></td>
		  </tr>
		  <tr>
		    <td class="fieldLabel">Product:</td>
		    <td><select name="productID" id="productID">
                <?php
					populateProductList("",$_SESSION['s_productID']);
				  ?>
              </select>            </td>
			<td>&nbsp;</td>
			<td class="fieldLabel">Trade Date Through: </td>
			<td class="yui-skin-sam" style="position: relative;"><input name="endDate" id="endDate" type="text" size="10" value="<?php echo($_SESSION['s_endDate']); ?>" />
                <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
                				<div id="cal2Container"></div>
			  <script language="JavaScript" type="text/javascript">
		
					function mySelectHandler2(type,args,obj) {
						var dates = args[0]; 
						var date = dates[0]; 
						var year = date[0], month = date[1], day = date[2];
						
						if(month.toString().length == 1)
							month = "0" + month;
						if(day.toString().length == 1)
							day = "0" + day;

						var dateStr = month + "/" + day + "/" + year;
						displayDate("endDate",dateStr);
						this.hide();
					};

					
					YAHOO.namespace("example.calendar2"); 
					 
					YAHOO.example.calendar2.init = function() { 
						YAHOO.example.calendar2.cal2 = new YAHOO.widget.Calendar("cal2","cal2Container", { 
							title:"Choose a date:", 
							close:true
						});
						YAHOO.example.calendar2.cal2.selectEvent.subscribe(mySelectHandler2, YAHOO.example.calendar2.cal2, true);
						YAHOO.example.calendar2.cal2.render();
						
						// Position calendar container relative to the calendar icon
						var icon = document.getElementById('show2up');
						var container = document.getElementById('cal2Container');
						if (icon && container) {
							var rect = icon.getBoundingClientRect();
							container.style.position = 'fixed';
							container.style.top = (rect.bottom + 5) + 'px';
							container.style.left = rect.left + 'px';
						}
						 
						// Listener to show the single page Calendar when the button is clicked 
						YAHOO.util.Event.addListener("show2up", "click", YAHOO.example.calendar2.cal2.show, YAHOO.example.calendar2.cal2, true); 
					}
					
					YAHOO.util.Event.onDOMReady(YAHOO.example.calendar2.init);
				
				</script>            </td>
			<td>&nbsp;</td>
		    <td class="fieldLabel">Company:</td>
		    <td><input type="text" name="companyName" id="companyName" value="<?php echo($_SESSION['s_companyName']); ?>" size="20" /></td>
		  </tr>
		  <tr>
		    <td class="fieldLabel">Product Group:</td>
		    <td><select name="productGroup" id="productGroup">
                <?php
					populateProductGroup($_SESSION['s_productGroup']);
				  ?>
              </select>
            </td>
		    <td>&nbsp;</td>
		    <td class="fieldLabel">Broker:</td>
		    <td><select name="brokerListID" id="brokerListID">
                <?php
					populateBrokerList($_SESSION['s_brokerListID']);
				  ?>
              </select>
            </td>
		    <td>&nbsp;</td>
		    <td class="fieldLabel" align="right"><input type="checkbox" name="non-cleared-trades" id="non-cleared-trades" <?php if($_SESSION["nonCleared"]) echo("checked=\"checked\""); ?> />
	        <label for="non-cleared-trades"></label></td>
		    <td>non-cleared trades only</td>
	      </tr>
	  </table>

	   	<div class="floatRight">
	      <input name="apply" type="submit" id="apply" value="Search" class="buttonSmall" />
		  <input name="remove" type="button" id="remove" value="Reset" class="buttonSmall" onclick="clearFilters();" />
		  
		  <input type="hidden" name="action" id="action" value="filter" />
		  <input type="hidden" name="sortField" id="sortField" value="<?php echo($_SESSION["sortBy"]); ?>" />
		  <input type="hidden" name="sortOrder" id="sortOrder" value="<?php echo($_SESSION["sortOrder2"]); ?>" />
		  <input type="hidden" name="last" id="last" value="<?php echo($last); ?>" />
   	  	</div>
		
		<div class="clear"></div>
		
		</div>
	</div>
	</form>
	
	
	<?php
		//GET ALL TRADES FOR CURRENT USER OR SUPER USER	
		//if($_POST["action"] == "filter"){
			// Build optimized query with all necessary JOINs to avoid N+1 queries
			$query = "SELECT DISTINCT t.pkTransactionID,t.fldTransactionNum,t.fldLegID,t.fldConfirmDate,b.pkTypeID,b.fldProductGroup,p.fldProductName,t.fldStatus,t.fkBuyerID,t.fkSellerID,t.fldBuyerPrice,t.fldBlock,t.fldStartHour,t.fldEndHour,t.fldUnitQty,t.fldIndexPrice,t.fldStartDate,t.fldEndDate,t.fldLocation,t.fkOptionTypeID,t.fldStrike,
				buyer.fldCompanyName as buyerCompany, buyer.fldTraderName as buyerTrader,
				seller.fldCompanyName as sellerCompany, seller.fldTraderName as sellerTrader,
				l.fldLocationName as locationName,
				ot.fldName as optionTypeName
				FROM $tblTransaction t 
				LEFT JOIN $tblTradeType b ON t.fkProductGroup=b.pkTypeID 
				LEFT JOIN $tblProduct p ON t.fkProductID=p.pkProductID
				LEFT JOIN $tblClient buyer ON t.fkBuyerID=buyer.pkClientID
				LEFT JOIN $tblClient seller ON t.fkSellerID=seller.pkClientID
				LEFT JOIN $tblLocation l ON t.fldLocation=l.pkLocationID
				LEFT JOIN tblB2TOptionType ot ON t.fkOptionTypeID=ot.pkOptionTypeID";
			
			// Build WHERE clause and collect parameters
			$whereConditions = array();
			$params = array();
			$types = '';
			
			// Company name filter
			if($_SESSION['s_companyName'] != "") {
				$whereConditions[] = "(buyer.fldCompanyName LIKE ? OR seller.fldCompanyName LIKE ?)";
				$params[] = "%" . $_SESSION['s_companyName'] . "%";
				$params[] = "%" . $_SESSION['s_companyName'] . "%";
				$types .= 'ss';
			}
			
			// Trade ID filter
			if($_SESSION['s_tradeID'] != "") {
				$whereConditions[] = "t.fldTransactionNum LIKE ?";
				$params[] = "%" . $_SESSION['s_tradeID'] . "%";
				$types .= 's';
			}
			
			// Start date filter
			if($_SESSION['s_startDate'] != "") {
				$whereConditions[] = "t.fldConfirmDate >= ?";
				$params[] = reverseDate($_SESSION['s_startDate']);
				$types .= 's';
			}
			
			// End date filter
			if($_SESSION['s_endDate'] != "") {
				$whereConditions[] = "t.fldConfirmDate <= ?";
				$params[] = reverseDate($_SESSION['s_endDate']);
				$types .= 's';
			}
			
			// Product ID filter
			if($_SESSION['s_productID'] != "") {
				$whereConditions[] = "t.fkProductID = ?";
				$params[] = $_SESSION['s_productID'];
				$types .= 'i';
			}
			
			// Status filter
			if(isset($_SESSION["s_status"]) && $_SESSION["s_status"] >= 0) {
				$whereConditions[] = "t.fldStatus = ?";
				$params[] = $_SESSION["s_status"];
				$types .= 'i';
			}
			
			// Product group filter
			if($_SESSION['s_productGroup'] != ""){
				if($_SESSION['s_productGroup'] == "Natural Gas") {
					$whereConditions[] = "(t.fkProductGroup = 4 OR t.fkProductGroup = 5 OR t.fkProductGroup = 6)";
				} else if($_SESSION['s_productGroup'] == "US Electricity") {
					$whereConditions[] = "(t.fkProductGroup = 1 OR t.fkProductGroup = 2 OR t.fkProductGroup = 3)";
				} else if($_SESSION['s_productGroup'] == "Crude Oil") {
					$whereConditions[] = "t.fkProductGroup = 8";
				} else if($_SESSION['s_productGroup'] == "RECs") {
					$whereConditions[] = "t.fkProductGroup = 9";
						} else if($_SESSION['s_productGroup'] == "RECs") {
			$whereConditions[] = "t.fkProductGroup = 9";
				}
			}
			
			// Broker filter
			if($_SESSION['s_brokerListID'] != ""){
				$whereConditions[] = "(t.fkBuyingBroker = ? OR t.fkSellingBroker = ?)";
				$params[] = $_SESSION['s_brokerListID'];
				$params[] = $_SESSION['s_brokerListID'];
				$types .= 'ii';
			}
			
			// Non-cleared filter
			if($_SESSION["nonCleared"] != "") {
				$whereConditions[] = "t.fldClearID = ''";
			}
			
			// Admin restriction
			if(!isAdmin()) {
				$whereConditions[] = "(t.fkBuyingBroker = ? OR t.fkSellingBroker = ?)";
				$params[] = $_SESSION["brokerID42"];
				$params[] = $_SESSION["brokerID42"];
				$types .= 'ii';
			}
			
			// Build final query
			if (!empty($whereConditions)) {
				$query .= " WHERE " . implode(" AND ", $whereConditions);
			}
							$query .= " ORDER BY " . $_SESSION["sortBy"] . " " . $_SESSION["sortOrder2"];
			

			
			// Execute secure query
			$result = safeQuery($_SESSION['db'], $query, $params, $types);
		
		// Check for SQL errors
		if (!$result) {
			echo "<p class='errorMessage'>Database Error: Unable to retrieve trades. Please contact support.</p>";
			$count = 0;
		} else {
			@$count = mysqli_num_rows($result);
		}
		
		// If no results with the complex query, try a simpler fallback query
		if ($count == 0 && empty($whereConditions)) {
			$fallbackQuery = "SELECT DISTINCT t.pkTransactionID,t.fldTransactionNum,t.fldLegID,t.fldConfirmDate,t.fkProductGroup as pkTypeID,t.fkProductGroup as fldProductGroup,'N/A' as fldProductName,t.fldStatus,t.fkBuyerID,t.fkSellerID,t.fldBuyerPrice,t.fldBlock,t.fldStartHour,t.fldEndHour,t.fldUnitQty,t.fldIndexPrice,t.fldStartDate,t.fldEndDate,t.fldLocation,t.fkOptionTypeID,t.fldStrike,
				'Unknown' as buyerCompany, 'Unknown' as buyerTrader,
				'Unknown' as sellerCompany, 'Unknown' as sellerTrader,
				'Unknown' as locationName,
				'Unknown' as optionTypeName
				FROM $tblTransaction t 
				WHERE t.fldStatus >= 0";
			
			// Add admin restriction if needed
			if(!isAdmin()) {
				$fallbackQuery .= " AND (t.fkBuyingBroker = " . $_SESSION["brokerID42"] . " OR t.fkSellingBroker = " . $_SESSION["brokerID42"] . ")";
			}
			
			$fallbackQuery .= " ORDER BY " . $_SESSION["sortBy"] . " " . $_SESSION["sortOrder2"];
			
			$fallbackResult = mysqli_query($_SESSION['db'], $fallbackQuery);
			if ($fallbackResult) {
				$result = $fallbackResult;
				$count = mysqli_num_rows($result);
				error_log("Using fallback query for dashboard - found $count trades");
			}
		}
		
		// Collect all data in a single pass to avoid mysqli_data_seek issues
		$allTradeData = array();
		$nymexTradeIDs = array();
		$usingFallback = false;
		
		if($count > 0 && $result) {
			
			// Check if we're using the fallback query (different column structure)
			$firstRow = mysqli_fetch_row($result);
			
			if ($firstRow && count($firstRow) == 21) {
				// This is the fallback query (21 columns)
				$usingFallback = true;
				$allTradeData[] = $firstRow;
				
				// Continue collecting data
				$rowCount = 1;
				while($row = mysqli_fetch_row($result)) {
					$allTradeData[] = $row;
					$rowCount++;
				}
				
				// Reset result pointer for the fallback case
				mysqli_data_seek($result, 0);
			} else {
				// This is the optimized query (27 columns)
				if ($firstRow) {
					$allTradeData[] = $firstRow;
				}
				
				$rowCount = 1;
				while($row = mysqli_fetch_row($result)) {
					$allTradeData[] = $row;
					$rowCount++;
					
					$buyerID = $row[8];
					$sellerID = $row[9];
					
					// Check if this trade involves NYMEX/ICE/NGX/NODAL/NASDAQ
					if($buyerID == $_SESSION["NYMEX_ID"] || $buyerID == $_SESSION["ICE_ID"] || $buyerID == $_SESSION["NGX_ID"] || $buyerID == $_SESSION["NODAL_ID"] || $buyerID == $_SESSION["NASDAQ_ID"] ||
					   $sellerID == $_SESSION["NYMEX_ID"] || $sellerID == $_SESSION["ICE_ID"] || $sellerID == $_SESSION["NGX_ID"] || $sellerID == $_SESSION["NODAL_ID"] || $sellerID == $_SESSION["NASDAQ_ID"]) {
						$nymexTradeIDs[] = $row[0]; // pkTransactionID
					}
					
				}
				
				// Pre-fetch all NYMEX trade party data in a single query
				$nymexTradeData = array();
				if(!empty($nymexTradeIDs)) {
					$nymexTradeIDsStr = implode(',', $nymexTradeIDs);
					$nymexQuery = "SELECT t1.pkTransactionID, t1.fldTransactionNum, t1.fldLegID, t1.fkBuyerID, t1.fkSellerID,
						buyLeg.fkBuyerID as actualBuyerID, sellLeg.fkSellerID as actualSellerID
						FROM $tblTransaction t1
						LEFT JOIN $tblTransaction buyLeg ON t1.fldTransactionNum = buyLeg.fldTransactionNum 
							AND buyLeg.fldLegID = CONCAT(FLOOR(t1.fldLegID/2)*2+1, '')
						LEFT JOIN $tblTransaction sellLeg ON t1.fldTransactionNum = sellLeg.fldTransactionNum 
							AND sellLeg.fldLegID = CONCAT(FLOOR(t1.fldLegID/2)*2+2, '')
						WHERE t1.pkTransactionID IN ($nymexTradeIDsStr)";
					$nymexResult = mysqli_query($_SESSION['db'], $nymexQuery);
					
					if (!$nymexResult) {
						echo "<p class='errorMessage'>NYMEX Query Error: " . mysqli_error($_SESSION['db']) . "</p>";
					} else {
						while($nymexRow = mysqli_fetch_row($nymexResult)) {
							$tradeID = $nymexRow[0];
							$nymexTradeData[$tradeID] = array(
								'actualBuyerID' => $nymexRow[5],
								'actualSellerID' => $nymexRow[6]
							);
						}
					}
				}
			}
		}
		
		if($statusID == $_SESSION["CONST_CONFIRMED"]){
			echo("<p class=\"warningMessage\">NOTE: These trades will automatically go live once both legs have been confirmed.</p>\n");
		}
	?>	
	
	<div class="controlBar">
		<div class="floatLeft">
			<span class="fieldLabel">SELECTED:</span>
			<input name="confirm" type="button" id="confirm" value="Confirm" class="buttonGreen" onclick="confirmConfirm();" />
			<input name="cancel" type="button" id="cancel" value="Cancel" class="buttonRed" onclick="confirmCancelTrade();" />
			<?php
			if(isAdmin()){
				echo("<input name=\"settle\" type=\"button\" id=\"settle\" value=\"Settle\" class=\"buttonGreen\" onclick=\"settleTrade();\" />");
				echo("<input name=\"void\" type=\"button\" id=\"void\" value=\"Void\" class=\"buttonRed\" onclick=\"voidTrade();\" />");
				echo("<input name=\"previewConfirm\" type=\"button\" id=\"previewConfirm\" value=\"Preview Confirm\" class=\"buttonBlue\" onclick=\"previewConfirm();\" />");
				echo("<input name=\"resendConfirm\" type=\"button\" id=\"resendConfirm\" value=\"Resend Confirm\" class=\"buttonBlue\" style=\"width: 120px;\" onclick=\"resendTradeConfirm(0);\" />\n");
				echo("<input name=\"resendConfirmToMe\" type=\"button\" id=\"resendConfirmToMe\" value=\"Resend Confirm To Me\" class=\"buttonBlue\" style=\"width: 155px;\" onclick=\"resendTradeConfirm(1);\"  />\n");
			}
			if(isSuperAdmin()){
				echo("<input name=\"delete\" type=\"button\" id=\"delete\" value=\"Delete\" class=\"buttonRed\" onclick=\"deleteTrade();\" style=\"background-color: #dc3545; font-weight: bold;\" />");
			}
			else {
				echo("<input name=\"previewConfirm\" type=\"button\" id=\"previewConfirm\" value=\"Preview Confirm\" class=\"buttonBlue\" onclick=\"previewConfirm();\" />");
			}
			?>
            
            <script language="javascript" type="text/javascript">
                $(document).ready(function() {
                    //$("#resendConfirm").colorbox( {inline:true, href:"#resendDialog"} );
                });
            </script>
            
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">TRADES: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('dashboard');" style="margin-right: 0; width: 95px;" />
		</div>
        
        <div class="clear"></div>
	</div>
	
	<div class="clear"></div>
	
	
	<form name="tradeList" id="tradeList" action="" method="post">
	<div class="scrollable">
	<style>
	.scrollable {
		position: relative;
		overflow: auto;
		max-height: 70vh;
	}
	.dataTable {
		border-collapse: collapse;
		width: 100%;
	}
	.dataTable thead {
		position: sticky;
		top: 0;
		z-index: 1000;
	}
	.dataTable th {
		background: white !important;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		border-bottom: 2px solid #007cba;
		padding: 10px 5px;
		text-align: left;
		font-weight: bold;
	}
	.dataTable tbody tr:nth-child(even) {
		background-color: #f9f9f9;
	}
	.dataTable tbody tr:hover {
		background-color: #f0f0f0;
	}
	/* Ensure sticky headers work properly */
	.dataTable thead tr {
		position: sticky;
		top: 0;
		background: white;
		z-index: 1000;
	}
	</style>
	<table border="0" cellspacing="0" cellpadding="0" class="dataTable">
	  <thead>
	  <tr>
      	<!-- Select All, Trade ID,Buyer,Seller,Unit Quantity,Start Date, End Date,Location,Price,Option type, Strike-->
		<th><a href="javascript:void(0);" onclick="selectAll('tradeList');">Select All</a></th>
		<th <?php if($last == "tradeID") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('tradeID');">Trade ID</a> <?php addSortArrow("tradeID",$last); ?></th>
		<th <?php if($last == "date" || $last == "fldTradeDate") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('date');">Trade Date</a> <?php addSortArrow("date",$last); ?></th>
		<th <?php if($last == "buyer") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('buyer');">Buyer</a> <?php addSortArrow("buyer",$last); ?></th>
		<th <?php if($last == "seller") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('seller');">Seller</a> <?php addSortArrow("seller",$last); ?></th>
		<th <?php if($last == "qty") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('qty');">Qty</a> <?php addSortArrow("qty",$last); ?></th>
        <th>Start Date -<br />
			End Date
        </th>
        <th <?php if($last == "location") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('location');">Location</a> <?php addSortArrow("location",$last); ?></th>
        <!--<th <?php if($last == "prodGroup") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prodGroup');">Product Group</a> <?php addSortArrow("prodGroup",$last); ?></th>-->
		<!--<th <?php if($last == "prod") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prod');">Product</a> <?php addSortArrow("prod",$last); ?></th>-->
		<th <?php if($last == "price") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('price');">Price</a> <?php addSortArrow("price",$last); ?></th>
		<!--<th <?php if($last == "block") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('block');">Block</a> <?php addSortArrow("block",$last); ?></th>-->
		<th <?php if($last == "option") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('option');">Option Type</a> <?php addSortArrow("option",$last); ?></th>
        <th <?php if($last == "strike") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('strike');">Strike</a> <?php addSortArrow("strike",$last); ?></th>
		<th <?php if($last == "status") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('status');">Status</a> <?php addSortArrow("status",$last); ?></th>
	  </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			// Pagination settings with page size selector
			$availablePageSizes = array(100, 250, 500, 1000, 2500);
			$rowsPerPage = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 500;
			
			// Validate page size is in allowed list
			if (!in_array($rowsPerPage, $availablePageSizes)) {
				$rowsPerPage = 500; // Default to 500 if invalid
			}
			
			$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
			$totalPages = ceil(count($allTradeData) / $rowsPerPage);
			$startRow = ($currentPage - 1) * $rowsPerPage;
			$endRow = min($startRow + $rowsPerPage, count($allTradeData));
			
			// Safety check - ensure we don't exceed array bounds
			if ($startRow >= count($allTradeData)) {
				echo "<p class='errorMessage'>Error: Start row $startRow exceeds available data (" . count($allTradeData) . " rows)</p>";
				$startRow = 0;
				$endRow = min($rowsPerPage, count($allTradeData));
			}
			
			// Page size selector
			echo "<div class='page-controls' style='margin: 20px 0; text-align: center;'>";
			echo "<div style='margin-bottom: 10px;'>";
			echo "<label for='pageSizeSelect' style='margin-right: 10px; font-weight: bold;'>Trades per page:</label>";
			echo "<select id='pageSizeSelect' onchange='changePageSize(this.value)' style='padding: 5px; border: 1px solid #ccc; border-radius: 3px; margin-right: 20px;'>";
			foreach ($availablePageSizes as $size) {
				$selected = ($size == $rowsPerPage) ? 'selected' : '';
				echo "<option value='$size' $selected>$size</option>";
			}
			echo "</select>";
			
			// Pagination controls
			if ($totalPages > 1) {
				// Build query string preserving all search parameters
				$queryParams = $_GET;
				unset($queryParams['page']); // Remove page from params
				$queryString = http_build_query($queryParams);
				$queryString = $queryString ? "&$queryString" : "";
				
				echo "<span style='margin-left: 20px;'>Page $currentPage of $totalPages | ";
				echo "Showing " . ($endRow - $startRow) . " of " . count($allTradeData) . " trades</span>";
				
				echo "<div class='pagination' style='margin: 10px 0;'>";
				
				// Previous page
				if ($currentPage > 1) {
					echo "<a href='?page=" . ($currentPage - 1) . $queryString . "' style='margin: 0 10px; padding: 5px 10px; background: #007cba; color: white; text-decoration: none; border-radius: 3px;'>Previous</a>";
				}
				
				// Page numbers
				$startPage = max(1, $currentPage - 2);
				$endPage = min($totalPages, $currentPage + 2);
				
				for ($p = $startPage; $p <= $endPage; $p++) {
					if ($p == $currentPage) {
						echo "<span style='margin: 0 5px; padding: 5px 10px; background: #007cba; color: white; border-radius: 3px;'>$p</span>";
					} else {
						echo "<a href='?page=$p$queryString' style='margin: 0 5px; padding: 5px 10px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 3px;'>$p</a>";
					}
				}
				
				// Next page
				if ($currentPage < $totalPages) {
					echo "<a href='?page=" . ($currentPage + 1) . $queryString . "' style='margin: 0 10px; padding: 5px 10px; background: #007cba; color: white; text-decoration: none; border-radius: 3px;'>Next</a>";
				}
				echo "</div>";
			}
			echo "</div>";
			echo "</div>";
			
			$displayedRows = 0;
			for ($rowIndex = $startRow; $rowIndex < $endRow; $rowIndex++) {
				// Safety check
				if (!isset($allTradeData[$rowIndex])) {
					echo "<p class='errorMessage'>Error: Row index $rowIndex not found in data array</p>";
					break;
				}
				
				$resultSet = $allTradeData[$rowIndex];
				
				$i++;
				$displayedRows++;
				
				/*
				Optimized query columns (27):
				0: pkTransactionID, 1: fldTransactionNum, 2: fldLegID, 3: fldConfirmDate, 4: pkTypeID, 5: fldProductGroup, 6: fldProductName, 7: fldStatus, 8: fkBuyerID, 9: fkSellerID, 10: fldBuyerPrice, 11: fldBlock, 12: fldStartHour, 13: fldEndHour, 14: fldUnitQty, 15: fldIndexPrice, 16: fldStartDate, 17: fldEndDate, 18: fldLocation, 19: fkOptionTypeID, 20: fldStrike, 21: buyerCompany, 22: buyerTrader, 23: sellerCompany, 24: sellerTrader, 25: locationName, 26: optionTypeName
				
				Fallback query columns (21):
				0: pkTransactionID, 1: fldTransactionNum, 2: fldLegID, 3: fldConfirmDate, 4: pkTypeID, 5: fldProductGroup, 6: fldProductName, 7: fldStatus, 8: fkBuyerID, 9: fkSellerID, 10: fldBuyerPrice, 11: fldBlock, 12: fldStartHour, 13: fldEndHour, 14: fldUnitQty, 15: fldIndexPrice, 16: fldStartDate, 17: fldEndDate, 18: fldLocation, 19: fkOptionTypeID, 20: fldStrike
				*/
				
				if($usingFallback) {
					// For fallback query, we need to fetch additional data individually
					$transactionID = $resultSet[0];
					$buyerID = $resultSet[8];
					$sellerID = $resultSet[9];
					$locationID = $resultSet[18];
					$optionTypeID = $resultSet[19];
					
					// Get buyer info
					$buyerCompany = "Unknown";
					$buyerTrader = "Unknown";
					if($buyerID) {
						$buyerQuery = "SELECT fldCompanyName, fldTraderName FROM $tblClient WHERE pkClientID = $buyerID";
						$buyerResult = mysqli_query($_SESSION['db'], $buyerQuery);
						if($buyerResult && $buyerRow = mysqli_fetch_row($buyerResult)) {
							$buyerCompany = $buyerRow[0] ?: "Unknown";
							$buyerTrader = $buyerRow[1] ?: "Unknown";
						}
					}
					
					// Get seller info
					$sellerCompany = "Unknown";
					$sellerTrader = "Unknown";
					if($sellerID) {
						$sellerQuery = "SELECT fldCompanyName, fldTraderName FROM $tblClient WHERE pkClientID = $sellerID";
						$sellerResult = mysqli_query($_SESSION['db'], $sellerQuery);
						if($sellerResult && $sellerRow = mysqli_fetch_row($sellerResult)) {
							$sellerCompany = $sellerRow[0] ?: "Unknown";
							$sellerTrader = $sellerRow[1] ?: "Unknown";
						}
					}
					
					// Get location name
					$locationName = "Unknown";
					if($locationID) {
						$locationQuery = "SELECT fldLocationName FROM $tblLocation WHERE pkLocationID = $locationID";
						$locationResult = mysqli_query($_SESSION['db'], $locationQuery);
						if($locationResult && $locationRow = mysqli_fetch_row($locationResult)) {
							$locationName = $locationRow[0] ?: "Unknown";
						}
					}
					
					// Get option type name
					$optionTypeName = "Unknown";
					if($optionTypeID) {
						$optionQuery = "SELECT fldName FROM tblB2TOptionType WHERE pkOptionTypeID = $optionTypeID";
						$optionResult = mysqli_query($_SESSION['db'], $optionQuery);
						if($optionResult && $optionRow = mysqli_fetch_row($optionResult)) {
							$optionTypeName = $optionRow[0] ?: "Unknown";
						}
					}
				} else {
					// Using optimized query - data is already available
					$transactionID = $resultSet[0];
					$buyerCompany = $resultSet[21] ?: "Unknown";
					$buyerTrader = $resultSet[22] ?: "Unknown";
					$sellerCompany = $resultSet[23] ?: "Unknown";
					$sellerTrader = $resultSet[24] ?: "Unknown";
					$locationName = $resultSet[25] ?: "Unknown";
					$optionTypeName = $resultSet[26] ?: "Unknown";
				}
				
				// Ensure all variables have default values
				$buyerCompany = $buyerCompany ?: 'Unknown';
				$buyerTrader = $buyerTrader ?: 'Unknown';
				$sellerCompany = $sellerCompany ?: 'Unknown';
				$sellerTrader = $sellerTrader ?: 'Unknown';
				$locationName = $locationName ?: 'Unknown';
				$optionTypeName = $optionTypeName ?: 'Unknown';
			  
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td align=\"center\" width=\"40\">\n");
				//if(($resultSet[7] == $_SESSION["CONST_PENDING"]) || ($resultSet[7] == $_SESSION["CONST_CONFIRMED"] && isAdmin()) || ($resultSet[7] == $_SESSION["CONST_LIVE"] && isAdmin()))
					echo("<input type=\"checkbox\" name=\"transID[]\" value=\"$resultSet[0]\" onclick=\"highlightRowCheck(this);\" />\n");
				//else
				//	echo("&nbsp;\n");
				echo("</td>\n");
				echo("<td onclick=\"highlightRow(this);\"><a href=\"javascript:void(0);\" onclick=\"launchDetail($resultSet[4],$resultSet[0]);\" id=\"trade_$i\">...".substr($resultSet[1]."-".$resultSet[2],5)."</a></td>\n"); 
				echo("<td onclick=\"highlightRow(this);\">".formatDate($resultSet[3])."</td>\n");
				echo("<td onclick=\"highlightRow(this);\">$buyerCompany<br />$buyerTrader</td>\n");
				echo("<td onclick=\"highlightRow(this);\">$sellerCompany<br />$sellerTrader</td>\n");
				echo("<td onclick=\"highlightRow(this);\">$resultSet[14]</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".formatDate($resultSet[16])." - \n");
					echo(formatDate($resultSet[17])."</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".($locationName ?: 'Unknown')."</td>\n");
				//echo("<td onclick=\"highlightRow(this);\">$resultSet[5]</td>\n");
				//echo("<td onclick=\"highlightRow(this);\">$resultSet[6]</td>\n");
				echo("<td onclick=\"highlightRow(this);\">");
					if($resultSet[4] == 7)
						echo( formatCurrency($resultSet[15],4,0) );
					else
						echo( formatCurrency($resultSet[10],-1,1) ); //was formatCurrency($resultSet[10],4,0)
						
						
						
				echo("</td>\n");
				/*echo("<td onclick=\"highlightRow(this);\">");
					if($resultSet[11] == 6)
						echo($resultSet[12]."-".$resultSet[13]);
					else
						getBlock($resultSet[11]);
				echo("</td>\n");*/
				echo("<td onclick=\"highlightRow(this);\">".($optionTypeName ?: 'Unknown')."</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".formatCurrency($resultSet[20],2,1)."</td>\n");
				//echo("<td onclick=\"highlightRow(this);\">$resultSet[14]</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".showStatus($resultSet[7])."</td>\n");
			 	echo("</tr>\n");
				
				$i++;
			}
			
			echo "</table>";
		}
		else {
			echo("<p class=\"warningMessage\">No trades found matching your criteria.</p>\n");
		}
	?>
	</tbody>
	</table>
	</div>
	<input type="hidden" name="action" id="action" value="" />
	</form>
	
	<?php if(isSuperAdmin()): // Super Admin only ?>
	<?php include('util/simpleCronDisplay.php'); ?>
	<?php endif; ?>
     
     
     <div style="display: none;">

         <div id="resendDialog" style="width: 400px; height: 300px;">
             <h2>Resend Confirm</h2>
             <p class="fieldLabel">Select recipients of confirm:</p>
             
             <form name="confirmRecip" id="confirmRecip" action="" method="post">
             <table cellspacing="0" cellpadding="5" border="0" id="recipTable">
             
             </table>
             <br />
             <p align="right"><input type="button" name="submit" value="Send" class="buttonGreen" onclick="submitResend();" />
             <input type="button" name="cancel" value="Cancel" class="buttonSmall" onclick="closeModal();" /></p>
             </form>
             
         </div>
         
     </div>
     
     <script>
     function changePageSize(newSize) {
         // Get current URL parameters
         const urlParams = new URLSearchParams(window.location.search);
         
         // Update page size
         urlParams.set('pageSize', newSize);
         
         // Reset to page 1 when changing page size
         urlParams.set('page', '1');
         
         // Redirect to new URL
         window.location.href = window.location.pathname + '?' + urlParams.toString();
     }
     </script>
     
     <script language="javascript" type="text/javascript">
		// Resizable Table Columns
		document.addEventListener('DOMContentLoaded', function() {
			const table = document.querySelector('.dataTable');
			if (!table) return;
			
			const headers = table.querySelectorAll('th');
			let isResizing = false;
			let currentHeader = null;
			let startX = 0;
			let startWidth = 0;
			
			headers.forEach(header => {
				header.addEventListener('mousedown', function(e) {
					const rect = header.getBoundingClientRect();
					const isResizeHandle = e.clientX > rect.right - 6; // Match CSS width
					
					if (isResizeHandle) {
						isResizing = true;
						currentHeader = header;
						startX = e.clientX;
						startWidth = header.offsetWidth;
						
						header.classList.add('resizing');
						e.preventDefault();
						e.stopPropagation();
					}
				});
			});
			
			document.addEventListener('mousemove', function(e) {
				if (!isResizing || !currentHeader) return;
				
				const deltaX = e.clientX - startX;
				const newWidth = Math.max(50, startWidth + deltaX); // Minimum 50px width
				
				currentHeader.style.width = newWidth + 'px';
				
				// Also update any cells in this column
				const columnIndex = Array.from(currentHeader.parentElement.children).indexOf(currentHeader);
				const rows = table.querySelectorAll('tbody tr');
				rows.forEach(row => {
					const cell = row.children[columnIndex];
					if (cell) {
						cell.style.width = newWidth + 'px';
					}
				});
			});
			
			document.addEventListener('mouseup', function() {
				if (isResizing && currentHeader) {
					currentHeader.classList.remove('resizing');
					isResizing = false;
					currentHeader = null;
				}
			});
		});
	</script>