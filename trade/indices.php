<?php

if(strpos($PHP_SELF,"indices.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}

require_once("util/indicesObj.php");
$index = new Indices();

?>

<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>
<script language="javascript" type="text/javascript" src="scripts/scripts.js"></script>

<link rel="stylesheet" type="text/css" href="css/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="css/calendar.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>


	<div style="height: 32px;">
	</div>

	<div class="scrollable">
	
	<div class="fieldGroup groupHalf">
		<div class="groupHeader">
			Index Raw Data Export
		</div>
		<div class="groupContent">
			
			<form name="indexForm" id="indexForm" action="" method="post">
			<table cellspacing="0" cellpadding="3" border="0">
			<tr>
			  <td class="fieldLabel">Report:</td>
			  <td>
              	<select name="reportName" id="reportName" onchange="getHubList(this);">
                <option value="">Select</option>
                <?php  $index->getReportList(); ?>
                </select>
              </td>
			  </tr>
			<tr>
			  <td class="fieldLabel">Hub:</td>
			  <td>
              	<select name="hubName" id="hubName">
                <?php  //$index->getHubList(); ?>
                </select>
              </td>
			  </tr>
			<tr>
				<td class="fieldLabel">Period:</td>
				<td class="yui-skin-sam">                  
                  
                  	<span>From:</span>
                    	<input type="text" size="10" name="startDate" id="startDate" />
                        <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" />
						<div id="cal1Container"></div>
                    <span>Through:</span>
                    	<input type="text" size="10" name="endDate" id="endDate" />
                        <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
						<div id="cal2Container"></div>
						
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

							
							YAHOO.namespace("example.calendar"); 
							 
							YAHOO.example.calendar.init = function() { 
								YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","cal1Container", { title:"Choose a date:", close:true } );
								YAHOO.example.calendar.cal1.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal1, true);
								YAHOO.example.calendar.cal1.render();
								 
								// Listener to show the single page Calendar when the button is clicked 
								YAHOO.util.Event.addListener("show1up", "click", YAHOO.example.calendar.cal1.show, YAHOO.example.calendar.cal1, true); 
							}
							
							YAHOO.namespace("example.calendar2"); 
							 
							YAHOO.example.calendar2.init = function() { 
								YAHOO.example.calendar2.cal2 = new YAHOO.widget.Calendar("cal2","cal2Container", { title:"Choose a date:", close:true } );
								YAHOO.example.calendar2.cal2.selectEvent.subscribe(mySelectHandler2, YAHOO.example.calendar2.cal2, true);
								YAHOO.example.calendar2.cal2.render();
								 
								// Listener to show the single page Calendar when the button is clicked 
								YAHOO.util.Event.addListener("show2up", "click", YAHOO.example.calendar2.cal2.show, YAHOO.example.calendar2.cal2, true); 
							} 
							
							YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
							YAHOO.util.Event.onDOMReady(YAHOO.example.calendar2.init);
						
						</script>
       
                </td>
			</tr>
			<tr>
			  <td class="fieldLabel">&nbsp;</td>
			  <td><input name="getData" type="button" id="getData" value="Get Data" onclick="getIndex();" class="buttonGreen" />
              	<input type="hidden" name="indices" value="1" />
              </td>
			  </tr>
			</table>
			</form>
			
		</div>
	</div>
	</div>
	

	<div class="clear"></div>