<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>
<noscript>
	<p><strong>WARNING:</strong> This site relies HEAVILY on JavaScript and it appears your browser does not support it.</p>
	<p>Please contact your support desk for assistance to use this application.</p>
</noscript>
	
<?php

if(strpos($PHP_SELF,"invoice.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}

if($p == ""){
	?>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<?php
}



//update sort order, field
if($sortOrder != "")
	$_SESSION["sortOrder4"] = $sortOrder;

		
if($sortField != ""){
	if($sortField == "companyName")
		$_SESSION["sortBy4"] = "fldCompanyName";
	else if($sortField == "date")
		$_SESSION["sortBy4"] = "fldTradeDate";
	
	$last = $sortField;
}
else{
	$_SESSION["sortBy4"] = "fldCompanyName";
	$_SESSION["sortOrder4"] = "ASC";
	$last = "fldCompanyName";
}

/*******************
* ACTIONS
*******************/

if($action == "invoiceSend") {

	echo("<p>invoice send</p>\n");

}

?>

	<h1>Invoicing</h1>
	
	<div class="floatRight" style="margin-top: -35px;">
		<form name="viewInvoice" action="" method="post" style="margin: 0;">
		<span class="fieldLabel">View invoicing for:</span> <?php buildCompanyList(); ?> <input type="button" value="Go" name="getInvoices" onclick="invoiceDetail(document.viewInvoice.companyID.value);" class="buttonSmall" />
		</form>
	</div>
	
	<?php echo($message); ?>
	
	<div class="clear" id="ajaxResponse"></div>
	
	
	
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
	

	<form name="tradeFilters" action="" method="post" style="display: ;">
	<div class="tradeFilters">
		<div id="filtersClose" style="display: <?php if($_SESSION["filters"] == "") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Invoice Filters</a></h3>
		</div>
		<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;"><h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Invoice Filters</a></h3>
		</div>
		
		<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
		<table border="0" cellspacing="3" cellpadding="0">
		  <tr>
			<!--<td class="fieldLabel">Trade ID: </td>
			<td><input name="tradeID" type="text" id="tradeID" size="15" value="<?php echo($tradeID); ?>" /></td>
			<td class="spacer">&nbsp;</td>-->
			<td class="fieldLabel">Trade Date From: </td>
			<td class="yui-skin-sam"><input name="startDate" id="startDate" type="text" size="10" value="<?php echo($_POST['startDate']); ?>" />
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
		    
            <!--<td class="fieldLabel">Status:</td>
		    <td><select name="statusID">
                <?php
					getStatusList($statusID);						
				  ?>
            </select></td>-->
		  </tr>
		  <tr>
		   <!-- <td class="fieldLabel">Product:</td>
		    <td><select name="productID">
                <?php
					//populateProductList($productID);						
				  ?>
              </select>            </td>
			<td>&nbsp;</td>-->
			<td class="fieldLabel">Trade Date Through: </td>
			<td class="yui-skin-sam"><input name="endDate" id="endDate" type="text" size="10" value="<?php echo($_POST['endDate']); ?>" />
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
		    <td class="fieldLabel">&nbsp;</td>
		    <td>&nbsp;</td>
		  </tr>
		  <tr>
		    <!--<td class="fieldLabel">Product Group:</td>
		    <td><select name="productGroup">
                <?php
					populateProductGroup($productGroup);
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
					echo("<select name=\"brokerListID\">\n");
						populateBrokerList($brokerListID);
					echo("</select>\n");
				}
				?>
			</td>-->
		    <td>&nbsp;</td>
		    <td class="fieldLabel">&nbsp;</td>
		    <td>&nbsp;</td>
	      </tr>
	  </table>
      

	   	<div class="floatRight">
	      <input name="apply" type="submit" id="apply" value="Search" class="buttonSmall" />
		  <input name="remove" type="button" id="remove" value="Reset" class="buttonSmall" onclick="clearFilters();" />
		  
		  <input type="hidden" name="action" value="filter" />
		  <input type="hidden" name="sortField" value="<?php echo($_SESSION["sortBy4"]); ?>" />
		  <input type="hidden" name="sortOrder" value="<?php echo($_SESSION["sortOrder4"]); ?>" />
		  <input type="hidden" name="last" value="<?php echo($last); ?>" />
   	  	</div>
		
		<div class="clear"></div>
		
		</div>
	</div>
	</form>
	
	
	<?php
		$query = "SELECT co.pkCompanyID, co.fldCompanyName, count(t.pkTransactionID) FROM $tblClient c, $tblTransaction t, $tblCompany co 
			WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID AND t.fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID 
			AND c.fkCompanyID=co.pkCompanyID AND t.fldSellBrokerComm > 0)) AND t.fldStatus=".$_SESSION["CONST_LIVE"];
		if(isset($_POST['startDate']))
			$query.= " AND fldConfirmDate>='".reverseDate($_POST['startDate'])."'";
		if(isset($_POST['endDate']))
			$query.= " AND fldConfirmDate<='".reverseDate($_POST['endDate'])."'";
		$query.=  " GROUP BY co.fldCompanyName ORDER BY ".$_SESSION["sortBy4"]." ".$_SESSION["sortOrder4"];
		//$query = "SELECT pkCompanyID, fldCompanyName FROM tblB2TCompany  ORDER BY ".$_SESSION["sortBy4"]." ".$_SESSION["sortOrder4"];
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
	?>
    
    <p style="margin: 0;">All unsettled trade activity is summarized below by client.</p>
	
	<div class="controlBar">
		<div class="floatLeft">
			<!--<span class="fieldLabel">Selected:</span>
			<input name="sendInv" type="button" id="sendInv" value="Send Invoice" class="buttonGreen" onclick="invoiceSend();" />-->
			<!--<input name="resendInvoice" type="button" id="resendInvoice" value="Resend Invoice" class="buttonBlue" onclick="resendInvoice();" />-->
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">Showing: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('invoice');" style="margin-right: 0;" />
		</div>
	</div>
	
	<div class="clear"></div>
	
	
	<form name="tradeList" id="tradeList" action="" method="post">
	<div class="scrollable" style="height: 290px;">
	<table border="0" cellspacing="0" cellpadding="0" class="dataTable">
	  <tr>
		<!--<th width="30">Select</th>-->
		<th width="200" <?php if($last == "companyName") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('companyName');">Company Name</a> <?php addSortArrow("companyName",$last); ?></th>
		<th width="100" <?php if($last == "tradeCount") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('tradeCount');">Total Live Trades</a> <?php addSortArrow("tradeCount",$last); ?></th>
		<th width="100" <?php if($last == "totalCommission") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('totalCommission');">Total Commission</a> <?php addSortArrow("totalCommission",$last); ?></th>
		<th width="100">Last Invoice Date</th>
		<td></td>
	  </tr>
	  
	  <?php
	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = client ID
				1 = company name
				2 = trade count
				*/
				
				//buy commission
				$queryTotal = "SELECT sum(fldBuyBrokerComm) FROM $tblTransaction t, $tblClient c, $tblCompany co WHERE t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID AND co.pkCompanyID=$resultSet[0] AND fldStatus=".$_SESSION["CONST_LIVE"]." AND fldBuyBrokerComm > 0";
				if(isset($_POST['startDate']))
					$queryTotal.= " AND fldConfirmDate>='".reverseDate($_POST['startDate'])."'";
				if(isset($_POST['endDate']))
					$queryTotal.= " AND fldConfirmDate<='".reverseDate($_POST['endDate'])."'";
				//echo("<p>".$queryTotal."</p>");
				$resultTotal = mysqli_query($_SESSION['db'],$queryTotal);
				@list($buyTotal) = mysqli_fetch_row($resultTotal);
				//additional buy brokers
				$additionalBrokerFees = getAdditionalBrokerFeesByCompany($resultSet[0],"buy",$_POST['startDate'],$_POST['endDate']);
				
				//sell commission
				$queryTotal2 = "SELECT sum(fldSellBrokerComm) FROM $tblTransaction t, $tblClient c, $tblCompany co WHERE t.fkSellerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID AND co.pkCompanyID=$resultSet[0] AND fldStatus=".$_SESSION["CONST_LIVE"]." AND fldSellBrokerComm > 0";
				if(isset($_POST['startDate']))
					$queryTotal2.= " AND fldConfirmDate>='".reverseDate($_POST['startDate'])."'";
				if(isset($_POST['endDate']))
					$queryTotal2.= " AND fldConfirmDate<='".reverseDate($_POST['endDate'])."'";
				//echo("<p>".$queryTotal2."</p>");
				$resultTotal2 = mysqli_query($_SESSION['db'],$queryTotal2);
				@list($sellTotal) = mysqli_fetch_row($resultTotal2);
				//additional sell brokers
				$additionalBrokerFees2 = getAdditionalBrokerFeesByCompany($resultSet[0],"sell",$_POST['startDate'],$_POST['endDate']);
				
				//$totalCount = $tradeCount + $tradeCount2;
				$totalCount = $resultSet[2];
				$balanceTotal = $buyTotal + $sellTotal + $additionalBrokerFees + $additionalBrokerFees2;
			  
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				//echo("<td align=\"center\" width=\"40\">\n");
				//	echo("<input type=\"checkbox\" name=\"transID[]\" value=\"$resultSet[0]\" onclick=\"highlightRowCheck(this);\" />\n");
				//echo("</td>\n");
				echo("<td onclick=\"highlightRow(this);\"><a href=\"javascript:void(0);\" onclick=\"invoiceDetail($resultSet[0],'".$_POST['startDate']."','".$_POST['endDate']."');\">$resultSet[1]</a></td>\n"); 
				echo("<td onclick=\"highlightRow(this);\"  align=\"center\">".$totalCount."</td>\n");
				echo("<td onclick=\"highlightRow(this);\" align=\"right\">".formatCurrency($balanceTotal,2,1)."</td>\n");
				echo("<td onclick=\"highlightRow(this);\" align=\"right\">".lastInvoiceDate($resultSet[0])."</td>\n"); 
			 	echo("</tr>\n");
				
				$i++;
			}
		}
		else {
		?>
			<tr>
				<td colspan="5">No records found</td>
			</tr>
		<?php		
		}
		?>
	</table>
	</div>
	<input type="hidden" name="action" id="action" value="" />
	</form>