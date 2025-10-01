	<?php
	@session_start();
	
	/*require_once("../functions.php");
	
	if(!validateSecurity()){
		echo("<p>You do not have permission to view this page.</p>");
		echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
		exit;
	}*/
	
	require_once("../util/popupHeader.php");
	
	if(!isset($_POST['invoiceNum']))
		$invoiceNum = 0;
	
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../scripts/ajax.js"></script>
	<script type="text/javascript" src="../scripts/trade.js"></script>
	<noscript>
		<p><strong>WARNING:</strong> This site relies HEAVILY on JavaScript and it appears your browser does not support it.</p>
		<p>Please contact your support desk for assistance to use this application.</p>
	</noscript>
	
	<?php
	
	/*******************************
	* PAGE ACTIONS
	********************************/
	if($_POST['action'] == "sendInvoice"){
	
		$total = count($_POST['transID']);
		if($total > 0){
			require("../util/invoice.php"); //get file containing send function
			
			$invoiceNum = time(); //generate unique invoice ID
			$totalValue = 0;
			
			for($i=0; $i<$total; $i++){
				//log invoice details
				$insertQuery = "INSERT INTO $tblInvoiceTrans VALUES ('',$invoiceNum,".$_POST['transID'][$i].",".$_SESSION["CONST_NOT_PAID"].")";
				//echo($insertQuery."<br>");
				$resultQuery = mysqli_query($_SESSION['db'],$insertQuery);
				
				//get trade commission value
				$queryValue = "SELECT sum(fldBuyBrokerComm + fldSellBrokerComm) FROM $tblTransaction WHERE pkTransactionID=".$_POST['transID'][$i];
				$resultValue = mysqli_query($_SESSION['db'],$queryValue);
				@list($tradeValue) = mysqli_fetch_row($resultValue);
				
				$additionalBrokerFees = getAdditionalBrokerFees($_POST['transID'][$i]);
				
				$totalValue += ($tradeValue + $additionalBrokerFees);
	
			}
			
			//log invoice master
			$invoiceQuery = "INSERT INTO $tblInvoice VALUES ('',\"$today\",$invoiceNum,$totalValue,".$_POST['companyID'].")";
			//echo($invoiceQuery."<br>");
			$invoiceResult = mysqli_query($_SESSION['db'],$invoiceQuery);
			
			sendInvoice($invoiceNum);
			
		}
		else {
			$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to resend trade confirms. Trades must be LIVE to resend a trade confrim.</p></div>";
		}
	
	}
	else if($_POST['action'] == "settleTrade") {
		//SETTLE TRADE IN INVOICE
		$total = count($_POST['transID']);
		if($total > 0){
			$updateCount = 0;
			for($i=0; $i<$total; $i++){		
				$updateResult = settleTrade($_POST['transID'][$i]);
				
				if($updateResult)
					$updateCount++;
			}
			
			if($updateCount > 0)
				echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully settled $updateCount trades</p></div>");
			else
				echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to settle trades</p></div>");
		}
		else {
			echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Unable to settle trades</p></div>");
		}
	}
	else if($_POST['action'] == "invoiceResend") {
		//echo("resending invoice...");
		require("../util/invoice.php"); //get file containing send function
		
		sendInvoice($invoiceNum);
	}
	
	
	/*******************************
	* END PAGE ACTIONS
	********************************/
	?>
	
	
	
	<div class="clear" id="ajaxResponse"></div>		
		
	<?php
	$companyID = $_GET['companyID'];
	
	if(isset($_GET['startDate']))
		$startDate = $_GET['startDate'];
	if(isset($_GET['endDate']))
		$endDate = $_GET['endDate'];
	if(isset($_POST['startDate']))
		$startDate = $_POST['startDate'];
	if(isset($_POST['endDate']))
		$endDate = $_POST['endDate'];
	
	if($companyID != ""){
		
		$queryName = "SELECT fldCompanyName FROM $tblCompany WHERE pkCompanyID=$companyID";
		$resultName = mysqli_query($_SESSION['db'],$queryName);
		@list($companyName) = mysqli_fetch_row($resultName);
		
		echo("<h2>Invoicing for: $companyName</h2>\n");
		
		?>
		
		<div class="floatRight">
			<div style="height: 10px; width: 10px; border: 1px solid #666666; float: left; margin-right: 5px;" class="unsettled"></div><span class="note"> = Unsettled trade</span>
		</div>
		
		<?php
		
		echo("<form name=\"viewChange\" action=\"\" method=\"post\">\n");
		echo("<p>View: ");
			getInvoiceHistory($companyID,$_POST['invoiceNum']);
		echo("<input type=\"submit\" value=\"Go\" class=\"buttonSmall\" />\n");
		echo("</form>\n");
		
		if($invoiceNum == 0){
		?>
		
		<link rel="stylesheet" type="text/css" href="../css/fonts-min.css" />
		<link rel="stylesheet" type="text/css" href="../css/calendar.css" />
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>
		
		<script language="javascript" type="text/javascript">
			filtersArray = new Array("filters","filtersClose","filtersOpen");
		</script>
		
		<form name="tradeFilters" id="tradeFilters" action="" method="post">		
			<div class="tradeFilters">
				<div id="filtersClose" style="display: <?php if($_SESSION["filters"] == "") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="../images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Trade Filters</a></h3>
				</div>
				<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="../images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Trade Filters</a></h3>
				</div>
				
				<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Trade ID: </td>
					<td><input name="tradeID" type="text" id="tradeID" size="15" value="<?php echo($_POST['tradeID']); ?>" /></td>
					<td class="spacer">&nbsp;</td>
					<td class="fieldLabel">Trade Date From: </td>
					<td class="yui-skin-sam"><input name="startDate" id="startDate" type="text" size="10" value="<?php echo($startDate); ?>" />
						<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" />
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
					<td class="fieldLabel">Product Group:</td>
					<td>
					  <select name="productGroup" id="productGroup">
						<?php
							populateProductGroup($productGroup);
						  ?>
					  </select>					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Product:</td>
					<td><select name="productID" id="productID">
						<?php
							populateProductList($productID,"");						
						  ?>
					  </select>            </td>
					<td>&nbsp;</td>
					<td class="fieldLabel">Trade Date Through: </td>
					<td class="yui-skin-sam"><input name="endDate" id="endDate" type="text" size="10" value="<?php echo($endDate); ?>" />
						<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
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
					<td class="fieldLabel">&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
			  </table>
		
				<div class="floatRight">
				  <input name="apply" type="submit" id="apply" value="Search" class="buttonSmall" />
				  <input name="remove" type="button" id="remove" value="Reset" class="buttonSmall" onclick="clearInvoiceFilters();" />
				  
				  <input type="hidden" name="action" value="filter" />
				  <input type="hidden" name="sortField" id="sortField" value="<?php echo($_SESSION["sortBy4"]); ?>" />
				  <input type="hidden" name="sortOrder" id="sortOrder" value="<?php echo($_SESSION["sortOrder4"]); ?>" />
				  <input type="hidden" name="last" id="last" value="<?php echo($last); ?>" />
				  <input type="hidden" name="invoiceNum" id="invoiceNum" value="<?php echo($invoiceNum); ?>" />
				</div>
				
				<div class="clear"></div>
				
				</div>
			</div>		
		</form>
		
		<?php
		} //end trade filters
		
		if($_GET['invoiceNum'] > 0){
						  //SELECT DISTINCT pkTransactionID,fldTransactionNum,fldLegID,fldConfirmDate,fkBuyerID,fkSellerID,b.fldProductGroup,p.fldProductName,fldBuyerPrice,fldUnitQty,fldBuyBrokerComm,fldSellBrokerComm,b.pkTypeID FROM tblB2TTransaction t, tblB2TTradeType b, tblB2TClient c, tblB2TProduct p, tblB2TInvoice i, tblB2TInvoiceTrans it WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=1 AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=1 AND fldSellBrokerComm > 0)) AND t.pkTransactionID=it.fkTransactionID AND i.pkInvoiceNum= GROUP BY pkTransactionID
			$queryTrades = "SELECT DISTINCT pkTransactionID,fldTransactionNum,fldLegID,fldConfirmDate,fkBuyerID,fkSellerID,b.fldProductGroup,p.fldProductName,fldBuyerPrice,fldUnitQty,fldBuyBrokerComm,fldSellBrokerComm,b.pkTypeID,t.fldStatus FROM $tblTransaction t, $tblTradeType b, $tblClient c, $tblProduct p, $tblInvoice i, $tblInvoiceTrans it WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=$companyID AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=$companyID AND fldSellBrokerComm > 0)) AND t.pkTransactionID=it.fkTransactionID AND it.fkInvoiceID=i.fldInvoiceNum AND i.fldInvoiceNum=$invoiceNum";
		}		
		else {
			//show outstanding "live" trades
			$queryTrades = "SELECT DISTINCT pkTransactionID,fldTransactionNum,fldLegID,fldConfirmDate,fkBuyerID,fkSellerID,b.fldProductGroup,p.fldProductName,fldBuyerPrice,fldUnitQty,fldBuyBrokerComm,fldSellBrokerComm,b.pkTypeID,t.fldStatus FROM $tblTransaction t, $tblTradeType b, $tblClient c, $tblProduct p WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=$companyID AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=$companyID AND fldSellBrokerComm > 0)) AND fldStatus=".$_SESSION["CONST_LIVE"];
		}
		//echo($queryTrades);
		
		if($_POST['tradeID'] != "")
			$queryTrades .= " AND fldTransactionNum LIKE \"%".$_POST['tradeID']."%\"";
		if($startDate != "")
			$queryTrades .= " AND fldConfirmDate >= '".reverseDate($startDate)."'";
		if($endDate != "")
			$queryTrades .= " AND fldConfirmDate <= '".reverseDate($endDate)."'";
		if($_POST['productID'] != "")
			$queryTrades .= " AND fkProductID=$productID";
		if($_POST['productGroup'] != ""){
			if($_POST['productGroup'] == "Natural Gas")
				$queryTrades .= " AND (fkProductGroup=4 OR fkProductGroup=5 OR fkProductGroup=6)";
			else if($_POST['productGroup'] == "US Electricity")
				$queryTrades .= " AND (fkProductGroup=1 OR fkProductGroup=2 OR fkProductGroup=3)";
		}
		
		$queryTrades.= " GROUP BY pkTransactionID";
		
		//echo($queryTrades);
		$resultTrades = mysqli_query($_SESSION['db'],$queryTrades);
		@$count = mysqli_num_rows($resultTrades);
		
		if($count > 0){	
			?>
			
			<div class="controlBar">
				<div class="floatLeft">
					<input name="sendInv" type="button" id="sendInv" value="Send Invoice" class="buttonGreen" onclick="sendInvoice('single');" />
                    <input name="settleInv" type="button" id="settleInv" value="Settle Trades" class="buttonGreen" onclick="settleTrade();" />
					<?php
					if($invoiceNum != 0){
						//echo(" | &nbsp; <input name=\"settleInv\" type=\"button\" id=\"settleInv\" value=\"Settle Trades\" class=\"buttonGreen\" onclick=\"settleTrade();\" />");
						echo("<input name=\"resendInv\" type=\"button\" id=\"resendInv\" value=\"Resend Invoice\" class=\"buttonSmall\" onclick=\"resendInvoice();\" />");
					}
					?>
				</div>
				
				<div class="floatRight">
					<span class="fieldLabel">Trades showing: <?php echo($count); ?></span>
				</div>
			</div>
			
			<div class="clear"></div>
		
			<form name="tradeList" id="tradeList" action="" method="post">
			<div class="scrollable" style="height: 290px;">
			<table border="0" cellspacing="0" cellpadding="0" class="dataTable" style="width: 830px;">
			  <tr>
				<th width="30"><a href="javascript:void(0);" onclick="selectAll('tradeList');">Select All</a></th>
				<th width="70" <?php if($last == "tradeID") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('tradeID');">Trade ID</a> <?php addSortArrow("tradeID",$last); ?></th>
				<th width="60" <?php if($last == "date" || $last == "fldTradeDate,fldTransactionNum") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('date');">Trade Date</a> <?php addSortArrow("date",$last); ?></th>
				<th width="150" <?php if($last == "buyer") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('buyer');">Buyer</a> <?php addSortArrow("buyer",$last); ?></th>
				<th width="150" <?php if($last == "seller") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('seller');">Seller</a> <?php addSortArrow("seller",$last); ?></th>
				<th <?php if($last == "prodGroup") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prodGroup');">Product Group</a> <?php addSortArrow("prodGroup",$last); ?></th>
				<th <?php if($last == "prod") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('prod');">Product</a> <?php addSortArrow("prod",$last); ?></th>
				<th <?php if($last == "price") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('price');">Price</a> <?php addSortArrow("price",$last); ?></th>
				<th <?php if($last == "qty") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('qty');">Qty</a> <?php addSortArrow("qty",$last); ?></th>
			  </tr>
			  
			  <?php
			  	
				$subTotal = 0;
				while($resultSet = mysqli_fetch_row($resultTrades)) {
					/*
					0 = pkTransID
					1 = trans num
					2 = leg num
					3 = confirm date
					4 = buyer ID
					5 = seller ID
					6 = product group
					7 = product name
					8 = price
					9 = qty				
					10= buy broker comm
					11= sell broker comm
					12= template ID
					13= trade status
					*/
					
					//get buyer info
					$buyerQuery = "SELECT fldCompanyName,fldTraderName FROM tblB2TClient WHERE pkClientID=$resultSet[4]";
					$buyerResult = mysqli_query($_SESSION['db'],$buyerQuery);
					@list($buyerCo,$buyerTrader) = mysqli_fetch_row($buyerResult);
					
					//get seller info
					$sellerQuery = "SELECT fldCompanyName,fldTraderName FROM tblB2TClient WHERE pkClientID=$resultSet[5]";
					$sellerResult = mysqli_query($_SESSION['db'],$sellerQuery);
					@list($sellerCo,$sellerTrader) = mysqli_fetch_row($sellerResult);
					
					$subTotal += $resultSet[10] + $resultSet[11];
					
					$rowClass = "";
					if($resultSet[13] == $_SESSION["CONST_LIVE"])
						$rowClass = "unsettled";
						
					echo("<tr class=\"$rowClass\">\n");
					echo("<td align=\"center\" width=\"40\">\n");
						echo("<input type=\"checkbox\" name=\"transID[]\" value=\"$resultSet[0]\" onclick=\"highlightRowCheck(this);\" />\n");
					echo("</td>\n");
					echo("<td onclick=\"highlightRow(this);\"><a href=\"javascript:void(0);\" onclick=\"launchDetail2($resultSet[12],$resultSet[0]);\">$resultSet[1]-$resultSet[2]</a></td>\n"); 
					echo("<td onclick=\"highlightRow(this);\" align=\"center\">".formatDate($resultSet[3])."</td>\n");
					echo("<td onclick=\"highlightRow(this);\">$buyerCo<br />$buyerTrader</td>\n");
					echo("<td onclick=\"highlightRow(this);\">$sellerCo<br />$sellerTrader</td>\n");
					echo("<td onclick=\"highlightRow(this);\">$resultSet[6]</td>\n");
					echo("<td onclick=\"highlightRow(this);\">$resultSet[7]</td>\n");
					echo("<td onclick=\"highlightRow(this);\" align=\"right\">".formatCurrency($resultSet[8],2,1)."</td>\n");
					echo("<td onclick=\"highlightRow(this);\" align=\"right\">$resultSet[9]</td>\n");
					echo("</tr>\n");
					
					$i++;
				}
				
			  ?>
			  
			  </table>		
			</div>
			<p align="right" style="margin: 0;">
				<strong>Total Commission:</strong> <?php echo(formatCurrency($subTotal,2,1)); ?>
			</p>
			
			<input type="hidden" name="action" value="" />
			<input type="hidden" name="invoiceNum" value="<?php echo($invoiceNum); ?>" />
            <input type="hidden" name="companyID" value="<?php echo($companyID); ?>" />
			</form>
		
		<?php
		}
		else {
			?>
	
			<p class="errorMessage">No trades found.</p>
			
			<?php
		}
	}
	else {
		?>

		<p class="errorMessage">You have accessed this page incorrectly and no information can be displayed at this time.</p>
		
		<?php
	}
	?>
	
	
	<?php
	require_once("../util/popupBottom.php");
	?>