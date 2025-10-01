<?php

if(strpos($PHP_SELF,"reports.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}
?>

<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>
<script language="javascript" type="text/javascript" src="scripts/trade.js"></script>

<link rel="stylesheet" type="text/css" href="css/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="css/calendar.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>


	<div style="height: 32px;">
	</div>
	
	<div class="scrollable">
	
	<?php
	
	if($action == "delete"){
		
	}

	if(isAdmin()){
	?>
	
	<div class="fieldGroup groupHalf" style="margin-right: 15px;">
		<div class="groupHeader">
			Raw Data Extracts
		</div>
		<div class="groupContent">
			Get full database extract for:
			<ul>
				<li><a href="util/reports.php?list=client" target="_blank">Client List</a></li>
				<li><a href="util/reports.php?list=location">Location List</a></li>
				<li><a href="util/reports.php?list=broker">Broker List</a></li>
			    <li><a href="util/reports.php?list=prod">Product List</a></li>
			    <li><a href="util/reports.php?list=prodType">Product Type List</a></li>
			    <li><a href="util/reports.php?list=tradeType">Trade Type List</a></li>
			</ul>
		
		</div>
	</div>
    
    <?php
	}
	?>
	
	<div class="fieldGroup groupHalf">
		<div class="groupHeader">
			Trade Commission Report
		</div>
		<div class="groupContent">
			
			<form name="commissionData" action="" method="post">
			<table cellspacing="0" cellpadding="3" border="0">
            <?php
			
			if(isAdmin()){
				echo("<tr>\n");
				echo("<td class=\"fieldLabel\">Broker</td>\n");
				echo("<td><select name=\"brokerID\" id=\"brokerID\">\n");
				echo(populateBrokerList($_POST['brokerID']));
				echo("<option value=\"all\">ALL</option>\n");
				echo("</select>");
				?>
				
                <span id="all_extended" style="display: ;">
                <input type="checkbox" name="extended_format" id="extended_format" />
                Extended
                <span>
				
				<?php
				echo("</td>\n");
				echo("</td>\n");
			}
			
			?>
                        
			<tr>
				<td class="fieldLabel">Period:</td>
				<td><select name="period" id="period" onchange="checkCustom(this);">
					<option value="" selected="selected">Select</option>
					<option value="YTD">YTD</option>
                    <option value="YTM">YTM</option>
                    <option value="current">This month</option>
                    <option value="last">Last month</option>
                    <option value="custom">Custom</option>
				  </select>
                  
                  <div id="customFields" class="yui-skin-sam" style="display: none;">
                  	<span class="fieldLabel">From:</span>
                    	<input type="text" size="10" name="startDate" id="startDate" />
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
						
						</script>
                        
                    <span class="fieldLabel">Through:</span>
                    	<input type="text" size="10" name="endDate" id="endDate" />
                        <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
						<div id="cal2Container"></div>
                        
                        <script language="javascript" type="text/javascript">
					
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
							
							</script>
                  </div>                        
                        
                </td>
			</tr>
			
			<tr>
			  <td class="fieldLabel">&nbsp;</td>
			  <td><input name="getData" type="button" id="getData" value="Get Trade Data" class="buttonGreen" onclick="getCommissionDataRequest(this);" /></td>
			  </tr>
			</table>
			</form>
			
		</div>
	</div>
    
    	<div style="height: 150px; width: 200px; float: left;"></div>
	</div>