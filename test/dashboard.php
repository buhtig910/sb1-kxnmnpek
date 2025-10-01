<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>
<noscript>
	<p class="errorMessage"><strong>WARNING:</strong> This site relies HEAVILY on JavaScript and it appears your browser does not support it.<br />
	Please contact your support desk for assistance to use this application.</p>
</noscript>
	
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

//echo("action: ".$_POST["action"]);

if($_POST["action"] == "cancelTrade") {
	//REMOVE-CANCEL TRADE IN DASHBOARD
	$total = count($_POST["transID"]);
	if($total > 0){
		for($i=0; $i<$total; $i++){
		
			$checkQuery = "SELECT fldStatus,fldTransactionNum FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus,$transactionNum) = mysqli_fetch_row($resultQuery);
		
			if($tradeStatus == $_SESSION["CONST_LIVE"]) { //cancel a live trade
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_LIVE_CANCELLED"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_LIVE_CANCELLED"]." WHERE fldTransactionNum=$transactionNum AND fldStatus=".$_SESSION["CONST_LIVE"];
			}
			else if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //cancel a confirmed trade
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_CONFIRM_CANCELLED"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_CONFIRM_CANCELLED"]." WHERE fldTransactionNum=$transactionNum AND fldStatus=".$_SESSION["CONST_CONFIRMED"];
			}
			else { //cancel a pending trade
				//echo("Trans Num: ".$transactionNum);
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_CANCELLED"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_CANCELLED"]." WHERE fldTransactionNum=$transactionNum AND (fldStatus=".$_SESSION["CONST_PENDING"]." OR fldStatus=".$_SESSION["CONST_CONFIRMED"].")";
			}
			//echo("query: ".$query."<br />");
			$result = mysqli_query($_SESSION['db'],$query);
			$result = mysqli_query($_SESSION['db'],$query2);
		}
				
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully cancelled $total trades</p></div>";
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to cancel trades</p></div>";
	}
}
else if($_POST["action"] == "voidTrade") {
	//VOID TRADE IN DASHBOARD
	$sendCount = 0;
	$total = count($_POST["transID"]);
	
	if($total > 0){
		require("util/confirm.php"); //get file containing resend function
		
		for($i=0; $i<$total; $i++){
		
			$checkQuery = "SELECT fldStatus,fldTransactionNum FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus,$transactionNum) = mysqli_fetch_row($resultQuery);
		
			if($tradeStatus == $_SESSION["CONST_LIVE"]) { //cancel a live trade
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE fldTransactionNum=$transactionNum AND fldStatus=".$_SESSION["CONST_LIVE"];
			}
			else if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //cancel a confirmed trade
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE fldTransactionNum=$transactionNum AND fldStatus=".$_SESSION["CONST_CONFIRMED"];
			}
			else { //cancel a pending trade
				//echo("Trans Num: ".$transactionNum);
				$query = "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE pkTransactionID=".$_POST["transID"][$i];
				$query2= "UPDATE $tblTransaction SET fldStatus=".$_SESSION["CONST_VOID"]." WHERE fldTransactionNum=$transactionNum AND (fldStatus=".$_SESSION["CONST_PENDING"]." OR fldStatus=".$_SESSION["CONST_CONFIRMED"].")";
			}
			//echo("query: ".$query."<br />");
			//echo("query2: ".$query2."<br />");
			$result = mysqli_query($_SESSION['db'],$query);
			$result = mysqli_query($_SESSION['db'],$query2);
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
		
			$checkQuery = "SELECT fldStatus,fldTransactionNum FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus) = mysqli_fetch_row($resultQuery);
			
			if($tradeStatus == $_SESSION["CONST_CONFIRMED"]) { //trade is confirmed, cancel it
				$query = "UPDATE $tblTransaction SET fldStatus=\"".$_SESSION["CONST_CONFIRM_CANCELLED"]."\" WHERE pkTransactionID=".$_POST["transID"][$i];
			}
			else {
				$query = "DELETE FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			}
			$result = mysqli_query($_SESSION['db'],$query);
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
	//echo("resend to me clicked....");
	//echo("<br>transID: ".$transID);
	
	$total = count($_POST["transID"]);	
	if($total > 0){
		require("util/confirm.php"); //get file containing resend function

		//added for new company prompt
		$companyArr = array();
		/*$sendCount = 0;
		$coTotal = count($clientID);
		
		$query_extra = "(";
		for($j=0; $j<$coTotal; $j++) {
			$query_extra .= "co.pkCompanyID=$clientID[$j] ";
			
			if($i < ($coTotal -1))
				$query_extra .= " OR ";
		}
		$query_extra .= ")";*/

		/*for($i=0; $i<$total; $i++){
			//$checkQuery = "SELECT fldStatus, FROM $tblTransaction WHERE pkTransactionID=".$_POST["transID"][$i];
			$checkQuery = "SELECT t.fldStatus, co.pkCompanyID FROM tblB2TTransaction t, tblB2TClient c, tblB2TCompany co WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID) OR
				(t.fkSellerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID)) AND t.pkTransactionID=".$_POST["transID"][$i];
			if($query_extra != "")
				$checkQuery.= " AND ".$query_extra;
			
			//echo($checkQuery);
			$resultQuery = mysqli_query($_SESSION['db'],$checkQuery);
			@list($tradeStatus) = mysqli_fetch_row($resultQuery); //get trade status

			if($tradeStatus == $_SESSION["CONST_LIVE"]){
				resendTradeConfirm($_POST["transID"][$i],1);
			}
			else
				$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
		}*/
		
		$sendCount = generateResend($_POST["clientID"], $_POST["transID"], "sendToMe"); //generate list and send
		
		if($sendCount == 0)
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">222 Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
		
	}
	else {
		$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
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

	<h1>TradeConnect Dashboard</h1>
	
	<?php echo($message); ?>
	
	<div class="clear" id="ajaxResponse"></div>

	<div style="height: 30px; margin-top: -10px;">
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
			<td class="yui-skin-sam"><input name="startDate" id="startDate" type="text" size="10" value="<?php echo($_SESSION['s_startDate']); ?>" />
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
						YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","cal1Container", { title:"Choose a date:", close:true } );
						YAHOO.example.calendar.cal1.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal1, true);
						YAHOO.example.calendar.cal1.render();
						 
						// Listener to show the single page Calendar when the button is clicked 
						YAHOO.util.Event.addListener("show1up", "click", YAHOO.example.calendar.cal1.show, YAHOO.example.calendar.cal1, true); 
					} 
					
					YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
				
				</script>			</td>
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
			<td class="yui-skin-sam"><input name="endDate" id="endDate" type="text" size="10" value="<?php echo($_SESSION['s_endDate']); ?>" />
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
						YAHOO.example.calendar2.cal2 = new YAHOO.widget.Calendar("cal2","cal2Container", { title:"Choose a date:", close:true } );
						YAHOO.example.calendar2.cal2.selectEvent.subscribe(mySelectHandler2, YAHOO.example.calendar2.cal2, true);
						YAHOO.example.calendar2.cal2.render();
						 
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
		    <td class="fieldLabel">
				<?php
				if(isAdmin()){
					echo("Broker:");
				}
				?>
			</td>
		    <td>
				<?php
				if(isAdmin()){
					echo("<select name=\"brokerListID\" id=\"brokerListID\">\n");
						populateBrokerList($_SESSION['s_brokerListID']);
					echo("</select>\n");
				}
				?>
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
			$query = "SELECT DISTINCT pkTransactionID,fldTransactionNum,fldLegID,fldConfirmDate,b.pkTypeID,b.fldProductGroup,p.fldProductName,fldStatus,t.fkBuyerID,t.fkSellerID,fldBuyerPrice,fldBlock,fldStartHour,fldEndHour,fldUnitQty,fldIndexPrice,fldStartDate,fldEndDate,fldLocation,fkOptionTypeID,fldStrike FROM $tblTransaction t, $tblTradeType b, $tblProduct p, $tblClient c WHERE t.fkProductGroup=b.pkTypeID AND t.fkProductID=p.pkProductID";
			
			if($_SESSION['s_tradeID'] != "")
				$query .= " AND fldTransactionNum LIKE \"%".$_SESSION['s_tradeID']."%\"";
			if($_SESSION['s_startDate'] != "")
				$query .= " AND fldConfirmDate >= '".reverseDate($_SESSION['s_startDate'])."'";
			if($_SESSION['s_endDate'] != "")
				$query .= " AND fldConfirmDate <= '".reverseDate($_SESSION['s_endDate'])."'";
			if($_SESSION['s_productID'] != "")
				$query .= " AND fkProductID=".$_SESSION['s_productID'];
			if(isset($_SESSION["s_status"])) {
				if($_SESSION["s_status"] >= 0)
					$query .= " AND fldStatus=".$_SESSION["s_status"];
			}
			if($_SESSION['s_productGroup'] != ""){
				if($_SESSION['s_productGroup'] == "Natural Gas")
					$query .= " AND (fkProductGroup=4 OR fkProductGroup=5 OR fkProductGroup=6)";
				else if($_SESSION['s_productGroup'] == "US Electricity")
					$query .= " AND (fkProductGroup=1 OR fkProductGroup=2 OR fkProductGroup=3)";
				else if($_SESSION['s_productGroup'] == "Crude Oil")
					$query .= " AND (fkProductGroup=8)";
			}
			if($_SESSION['s_brokerListID'] != ""){
				$query .= " AND (fkBuyingBroker=".$_SESSION['s_brokerListID']." OR fkSellingBroker=".$_SESSION['s_brokerListID'].")";
			}
			if($_SESSION['s_companyName'] != ""){
				$query .= " AND ((t.fkBuyerID=c.pkClientID AND c.fldCompanyName LIKE \"%".$_SESSION['s_companyName']."%\") OR (t.fkSellerID=c.pkClientID AND c.fldCompanyName LIKE \"%".$_SESSION['s_companyName']."%\"))";
			}
			if($_SESSION["nonCleared"] != "") {
				$query .= " AND t.fldClearID=\"\"";
			}
			
			if(!isAdmin())
				$query .= " AND (fkBuyingBroker=".$_SESSION["brokerID42"]." OR fkSellingBroker=".$_SESSION["brokerID42"].")";
			$query .= " ORDER BY ".$_SESSION["sortBy"]." ".$_SESSION["sortOrder2"]; //GROUP BY fldTransactionNum
		/*}
		else{
			$query = "SELECT pkTransactionID,fldTransactionNum,fldLegID,fldTradeDate,b.pkTypeID,b.fldProductGroup,p.fldProductName,fldStatus FROM $tblTransaction t, $tblTradeType b, $tblProduct p WHERE t.fkProductGroup=b.pkTypeID AND t.fkProductID=p.pkProductID AND (fkBuyingBroker=".$_SESSION["brokerID42"]." OR fkSellingBroker=".$_SESSION["brokerID42"].") ORDER BY ".$_SESSION["sortBy"]." ".$_SESSION["sortOrder2"];
		}*/
		
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
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
				//echo("<input name=\"delete\" type=\"button\" id=\"delete\" value=\"Delete\" class=\"buttonRed\" onclick=\"confirmDeleteTrade();\" />");
				echo("<input name=\"settle\" type=\"button\" id=\"settle\" value=\"Settle\" class=\"buttonGreen\" onclick=\"settleTrade();\" />");
				echo("<input name=\"void\" type=\"button\" id=\"void\" value=\"Void\" class=\"buttonRed\" onclick=\"voidTrade();\" />");
				echo("<br /><div class=\"controlBarSpacer\"></div>\n");
				echo("<input name=\"previewConfirm\" type=\"button\" id=\"previewConfirm\" value=\"Preview Confirm\" class=\"buttonBlue\" onclick=\"previewConfirm();\" />");
				echo("<input name=\"resendConfirm\" type=\"button\" id=\"resendConfirm\" value=\"Resend Confirm\" class=\"buttonBlue\" style=\"width: 120px;\" onclick=\"resendTradeConfirm(0);\" />\n");
				echo("<input name=\"resendConfirmToMe\" type=\"button\" id=\"resendConfirmToMe\" value=\"Resend Confirm To Me\" class=\"buttonBlue\" style=\"width: 155px;\" onclick=\"resendTradeConfirm(1);\"  />\n");
			}
			else {
				echo("<br /><div class=\"controlBarSpacer\"></div>\n");
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
	<div class="scrollable" style="height: 290px;">
	<table border="0" cellspacing="0" cellpadding="0" class="dataTable">
	  <tr>
      	<!-- Select All, Trade ID,Buyer,Seller,Unit Quantity,Start Date, End Date,Location,Price,Option type, Strike-->
		<th width="25"><a href="javascript:void(0);" onclick="selectAll('tradeList');">Select All</a></th>
		<th width="50" <?php if($last == "tradeID") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('tradeID');">Trade ID</a> <?php addSortArrow("tradeID",$last); ?></th>
		<th width="50" <?php if($last == "date" || $last == "fldTradeDate") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('date');">Trade Date</a> <?php addSortArrow("date",$last); ?></th>
		<th width="220" <?php if($last == "buyer") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('buyer');">Buyer</a> <?php addSortArrow("buyer",$last); ?></th>
		<th width="220" <?php if($last == "seller") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('seller');">Seller</a> <?php addSortArrow("seller",$last); ?></th>
		<th width="20" <?php if($last == "qty") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('qty');">Qty</a> <?php addSortArrow("qty",$last); ?></th>
        <th width="180">Start Date -<br />
			End Date
        </th>
        <th width="120" <?php if($last == "location") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('location');">Location</a> <?php addSortArrow("location",$last); ?></th>
        <!--<th <?php if($last == "prodGroup") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prodGroup');">Product Group</a> <?php addSortArrow("prodGroup",$last); ?></th>-->
		<!--<th <?php if($last == "prod") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prod');">Product</a> <?php addSortArrow("prod",$last); ?></th>-->
		<th width="40" <?php if($last == "price") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('price');">Price</a> <?php addSortArrow("price",$last); ?></th>
		<!--<th <?php if($last == "block") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('block');">Block</a> <?php addSortArrow("block",$last); ?></th>-->
		<th width="40" <?php if($last == "option") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('option');">Option Type</a> <?php addSortArrow("option",$last); ?></th>
        <th width="40" <?php if($last == "strike") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('strike');">Strike</a> <?php addSortArrow("strike",$last); ?></th>
		<th <?php if($last == "status") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('status');">Status</a> <?php addSortArrow("status",$last); ?></th>
	  </tr>
	  
	  <?php
	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = pkTransID
				1 = fldTransNum
				2 = legID
				3 = trade date
				4 = template ID
				5 = prod group
				6 = product
				7 = status
				8 = buyer ID
				9 = seller ID
				10 = price
				11 = block
				12 = start hour
				13 = end hour
				14 = unit qty
				15 = index price
				16 = start date
				17 = end date
				18 = location
				19 = option type
				20 = strike
				*/
				
				//get buyer info
				if($resultSet[8] == $_SESSION["NYMEX_ID"] || $resultSet[8] == $_SESSION["ICE_ID"] || $resultSet[8] == $_SESSION["NGX_ID"] || $resultSet[8] == $_SESSION["NODAL_ID"] || $resultSet[8] == $_SESSION["NASDAQ_ID"])
					$resultSet[8] = verifyParty($resultSet[0],"buyer");
				$buyerQuery = "SELECT fldCompanyName,fldTraderName FROM tblB2TClient WHERE pkClientID=$resultSet[8]";
				$buyerResult = mysqli_query($_SESSION['db'],$buyerQuery);
				@list($buyerCo,$buyerTrader) = mysqli_fetch_row($buyerResult);
				
				//get seller info
				if($resultSet[9] == $_SESSION["NYMEX_ID"] || $resultSet[9] == $_SESSION["ICE_ID"] || $resultSet[9] == $_SESSION["NGX_ID"] || $resultSet[9] == $_SESSION["NODAL_ID"] || $resultSet[9] == $_SESSION["NASDAQ_ID"])
					$resultSet[9] = verifyParty($resultSet[0],"seller");
				$sellerQuery = "SELECT fldCompanyName,fldTraderName FROM tblB2TClient WHERE pkClientID=$resultSet[9]";
				$sellerResult = mysqli_query($_SESSION['db'],$sellerQuery);
				@list($sellerCo,$sellerTrader) = mysqli_fetch_row($sellerResult);
			  
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
				echo("<td onclick=\"highlightRow(this);\">$buyerCo<br />$buyerTrader</td>\n");
				echo("<td onclick=\"highlightRow(this);\">$sellerCo<br />$sellerTrader</td>\n");
				echo("<td onclick=\"highlightRow(this);\">$resultSet[14]</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".formatDate($resultSet[16])." - \n");
					echo(formatDate($resultSet[17])."</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".getLocationList($resultSet[18])."</td>\n");
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
				echo("<td onclick=\"highlightRow(this);\">".getOptionType($resultSet[19])."</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".formatCurrency($resultSet[20],2,1)."</td>\n");
				//echo("<td onclick=\"highlightRow(this);\">$resultSet[14]</td>\n");
				echo("<td onclick=\"highlightRow(this);\">".showStatus($resultSet[7])."</td>\n");
			 	echo("</tr>\n");
				
				$i++;
			}
		}
		else {
		?>
			<tr>
				<td colspan="12">No records found</td>
			</tr>
		<?php		
		}
		?>
	</table>
	</div>
	<input type="hidden" name="action" id="action" value="" />
	</form>
    
    
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