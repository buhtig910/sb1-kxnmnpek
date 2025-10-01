	<?php
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../scripts/ajax.js"></script>

	<!-- <h1>B2T Technologies Trade Manager Platform</h1> -->
	
	<?php
	
	if($action == "cancelTrade"){
	
		$transID = time(); //$_SESSION['userID']."-".
		$legID = 1;
		//echo($transID);
		/*for($i=$legID; $i<=$legID+1 ; $i++){
			$query = "INSERT INTO tblB2TTransaction VALUES('',\"$transID\",\"$i\",\"".reverseDate($confirmFor)."\",\"$today\"".
				",\"$buyerID\",\"$sellerID\",\"$tradeType\",\"$productID\",\"$productTypeID\"".
				",\"$region\",\"$location\",\"$offsetIndex\",\"$delivery\"".
				",\"$buyerPrice\",\"$buyerCurr\",\"$sellerPrice\",\"$sellerCurr\",\"$optionStyle\",\"$optionType\",\"$exerciseType\",\"$strikePrice\",\"$strikeCurr\"".
				",\"".reverseDate($startDate)."\",\"".reverseDate($endDate)."\",\"$block\",\"$qty\",\"$unitFreq\",\"$totalQty\",\"$hours\"".
				",\"$timeZone\",\"$buyBroker\",\"$buyBrokerRate\",\"$buyBrokerTotal\",\"$sellBroker\",\"$sellBrokerRate\",\"$sellBrokerTotal\"".
				",0". //status default to 0
				")";
			//echo($query."<br>");
			$result = mysql_query($query);
		}*/
		
		if($result){
			$step = "confirm";
		}
		else{
			//$tradeType = "";
		}
	}
	
	if($step == "confirm") {
		echo("<p class=\"confirmMessage\">Your transaction has been submitted successfully.<br>Your transaction ID is: <strong>$transID</strong>.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");
		
		echo("<script language=\"javascript\" type=\"text/javascript\">opener.location.reload(true);</script>\n");
	
	}
	else if($HTTP_GET_VARS["tradeType"] != ""){
		//$_SESSION["myTradeType"] = $HTTP_GET_VARS["tradeType"];
		//$tradeType = $HTTP_GET_VARS["tradeType"];
			
			$query = "SELECT fldProductGroup FROM ".$tblTradeType." WHERE pkTypeID=$tradeType";
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			@list($tradeName) = mysqli_fetch_row($result);
			
			echo("<h2>$tradeName</h2>\n");
			
			$query2 = "SELECT * FROM tblB2TTemplates WHERE fkTypeID=$tradeType";
			//echo($query2);
			$result2 = mysqli_query($_SESSION['db'],$query2);
			$templateOptions = mysqli_fetch_row($result2);
			/*
			0 = pkTemplateID
			1 = fldTemplateName
			2 = fldOffsetIndex
			3 = fldDeliveryType
			4 = fldBuyerPrice
			5 = fldBuyerCurrency
			6 = fldSellerPrice
			7 = fldSellerCurrency
			8 = fldOptionStyle
			9 = fldOptionType
			10 = fldExerciseType
			11 = fldStrike
			12 = fldStrikeCurrency
			13 = fldBlock
			14 = fldHours
			15 = fldTimeZone
			16= fkTypeID
			*/
			
			//echo("trade ID: ".$tradeID);
			
			if($tradeID != ""){
				$queryTrade = "SELECT * FROM $tblTransaction WHERE pkTransactionID=$tradeID";
				//echo($queryTrade);
				$resultTrade = mysqli_query($_SESSION['db'],$queryTrade);
				@$count = mysqli_num_rows($resultTrade);
				
				if($count == 1){
					list($tradeID,$transactionID,$leg,$confirmDate,$tradeDate,$buyerID,$sellerID,$productGroup,$productID,$productType,$region,$location,$offsetIndex,$delivery,$buyerPrice,$buyerCurrency,$sellerPrice,$sellerCurrency,$optionStyle,$optionType,$exerciseType,$strike,$strikeCurrency,$startDate,$endDate,$block,$qty,$freq,$totalQty,$hours,$timeZone,$buyBroker,$buyBrokerRate,$buyBrokerComm,$sellBroker,$sellBrokerRate,$sellBrokerComm,$status) = mysqli_fetch_row($resultTrade);
				}
			}
		?>
		
		<link rel="stylesheet" type="text/css" href="../css/fonts-min.css" />
		<link rel="stylesheet" type="text/css" href="../css/calendar.css" />
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>
		
		<p>Enter the trade details below.</p>
	
		<form name="tradeInfo" action="" method="post" onsubmit="return confirmSubmit2();">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trade Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Transaction Number: </td>
					<td><?php echo($transactionID); ?></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Leg ID: </td>
					<td><?php echo($leg); ?></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Trade Date:</td>
					<td class="yui-skin-sam"><input name="confirmFor" type="text" id="confirmFor" size="15" readonly="readonly" class="readonly" value="<?php echo(formatDate($confirmDate)); ?>" />
						<!--<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" />
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
								displayDate("confirmFor",dateStr);
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
						
						</script>-->
						
					</td>
				  </tr>
				</table>
			</div>
		</div>
		
		<div class="fieldGroup floatLeft" style="width: 382px; margin-right: 15px;">
			<div class="groupHeader">
				Buyer Information
			</div>
		  <div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Buying Trader: </td>
					<td><select name="buyerID" id="buyer" onchange="showDetails(this);" disabled="disabled">
                      <?php
					  	populateTraderList($buyerID);						
					  ?>
                    </select>
					
					<script language="javascript" type="text/javascript">
						showDetails(document.getElementById("buyer"));
					  </script>
					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Company: </td>
					<td><input name="buyerCompany" type="text" id="buyerCompany" size="30" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Address: </td>
					<td><input name="buyerAddress" type="text" id="buyerAddress" size="35" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Telephone:</td>
				    <td><input name="buyerPhone" type="text" id="buyerPhone" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="buyerFax" type="text" id="buyerFax" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				</table>
		  </div>
		</div>
		
		<div class="fieldGroup floatLeft" style="width: 382px;">
			<div class="groupHeader">
				Seller Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Selling Trader: </td>
					<td><select name="sellerID" id="seller" onchange="showDetails(this);" disabled="disabled">
					  <?php
					  	populateTraderList($sellerID);						
					  ?>
					  </select>
					  
					  <script language="javascript" type="text/javascript">
						showDetails(document.getElementById("seller"));
					  </script>
					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Company: </td>
					<td><input name="sellerCompany" type="text" id="sellerCompany" size="30" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Address: </td>
					<td><input name="sellerAddress" type="text" id="sellerAddress" size="35" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Telephone:</td>
				    <td><input name="sellerPhone" type="text" id="sellerPhone" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="sellerFax" type="text" id="sellerFax" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				</table>
			</div>
		</div>
		
		<div class="clear"></div>
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trade Details
			</div>
		  <div class="groupContent">
		    <table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td colspan="2" valign="top"><table border="0" cellspacing="3" cellpadding="0">
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Product Group: </td>
					    <td><input name="productGroup" type="text" class="readonly" value="<?php echo($productGroup); ?>" size="20" readonly="readonly" /></td>
				      </tr>
					  <tr>
					    <td nowrap="nowrap" class="fieldLabel">Product:</td>
					    <td><select name="productID" disabled="disabled">
					      <?php
							populateProductList($productID);						
						  ?>
						  </select>
						</td>
				      </tr>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Product Type: </td>
					    <td><select name="productTypeID" id="productTypeID" disabled="disabled">
                          <?php
							populateProductType($productType);						
						  ?>
                          </select></td>
				      </tr>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Location: </td>
					    <td style="height: 20px; width: 170px;">
							<select name="location" id="location" style="width: 170px;" disabled="disabled" onfocus="toggleWidth(this);" onblur="resetWidth(this);" onchange="updateRegion(this);">
                              <?php
								populateLocationList($location);						
							  ?>
                          </select>
						</td>
				      </tr>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Region: </td>
					    <td>
							<input type="text" name="region" id="region" size="25" value="<?php echo($region); ?>" readonly="readonly" class="readonly" />
						</td>
				      </tr>
					  
					  
					  <?php
					  if($templateOptions[2] == "1"){
					  ?>
					  <tr>
					    <td nowrap="nowrap" class="fieldLabel">Offset Index: </td>
					    <td><select name="offsetIndex" id="offsetIndex" disabled="disabled">
					     	<?php
								//$offsetIndex
							?>
						 	</select>
						</td>
				      </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[3] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Delivery:</td>
					    <td><select name="delivery" id="delivery" disabled="disabled">
                            <option value="0">Select</option>
                          </select></td>
				      </tr>
					  <?php
					  }
					  ?>
					  
                    </table></td>
					<td class="spacer">&nbsp;</td>
				    <td colspan="2" valign="top"><table border="0" cellspacing="3" cellpadding="0">
				      
					  <?php
					  if($templateOptions[4] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Buyer Price: </td>
				        <td>
                          <input name="buyerPrice" type="text" id="buyerPrice" size="12" readonly="readonly" class="readonly" value="<?php echo($buyerPrice); ?>" />
						 </td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[5] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Buyer Currency: </td>
				        <td>
							<select name="buyerCurr" disabled="disabled">
								 <?php
									populateCurrencyList($buyerCurrency);						
								 ?>
							</select>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[6] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Seller Price: </td>
				        <td>
                          <input name="sellerPrice" type="text" id="sellerPrice" size="12" readonly="readonly" class="readonly" value="<?php echo($sellerPrice); ?>" />
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[7] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Seller Currency: </td>
				        <td><select name="sellerCurr" id="sellerCurr" disabled="disabled">
								 <?php
									populateCurrencyList($sellerCurrency);						
								  ?>
							</select>
						</td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[8] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Option Style: </td>
				        <td>
							<select name="optionStyle" id="optionStyle" disabled="disabled">
                            <?php
								populateOptionStyle($optionStyle);						
							?>
                            </select>
                        </td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[9] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Option Type: </td>
				        <td><select name="optionType" id="optionType" disabled="disabled">
                            <?php
								populateOptionType($optionType);						
							  ?>
                            </select>
                        </td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[10] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Exercise Type: </td>
				        <td>
							<select name="exerciseType" id="exerciseType" disabled="disabled">
                              <?php
								populateExercise($exerciseType);						
							  ?>
                          </select>
                        </td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[11] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Strike:</td>
				        <td>
                          <input name="strikePrice" type="text" id="strikePrice" size="12" readonly="readonly" class="readonly" value="<?php echo($strike); ?>" />
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[12] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Strike Currency: </td>
				        <td>
							<select name="strikeCurr" id="strikeCurr" disabled="disabled">
								 <?php
									populateCurrencyList($strikeCurrency);						
								  ?>
							</select>
						</td>
			          </tr>
					  <?php
					  }
					  ?>

                    </table></td>
				    <td valign="top"  class="spacer">&nbsp;</td>
				    <td valign="top"><table border="0" cellspacing="3" cellpadding="0">
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Start Date: </td>
				        <td class="yui-skin-sam"><input name="startDate" type="text" id="startDate" size="8" readonly="readonly" class="readonly" value="<?php echo($startDate); ?>" />
							<!--<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
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
									displayDate("startDate",dateStr);
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
							
							</script>-->
						</td>
			          </tr>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">End Date: </td>
				        <td class="yui-skin-sam"><input name="endDate" type="text" id="endDate" size="8" readonly="readonly" class="readonly" value="<?php echo($endDate); ?>" />
							<!--<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show3up" class="center" />
							<div id="cal3Container"></div>
						
							<script language="javascript" type="text/javascript">
					
								function mySelectHandler3(type,args,obj) {
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
	
								
								YAHOO.namespace("example.calendar3"); 
								 
								YAHOO.example.calendar3.init = function() { 
									YAHOO.example.calendar3.cal3 = new YAHOO.widget.Calendar("cal3","cal3Container", { title:"Choose a date:", close:true } );
									YAHOO.example.calendar3.cal3.selectEvent.subscribe(mySelectHandler3, YAHOO.example.calendar3.cal3, true);
									YAHOO.example.calendar3.cal3.render();
									 
									// Listener to show the single page Calendar when the button is clicked 
									YAHOO.util.Event.addListener("show3up", "click", YAHOO.example.calendar3.cal3.show, YAHOO.example.calendar3.cal3, true); 
								}
								
								YAHOO.util.Event.onDOMReady(YAHOO.example.calendar3.init);
							
							</script>-->
						</td>
			          </tr>
				      
					  <?php
					  if($templateOptions[13] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Block:</td>
				        <td><select name="block" id="block" disabled="disabled">
							<?php
								//$block
							?>
				          </select>
				        </td>
					  </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[14] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Hours:</td>
				        <td><input name="hours" type="text" id="hours" size="12" readonly="readonly" class="readonly" value="<?php echo($hours); ?>" /></td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[15] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Time Zone: </td>
				        <td><select name="timeZone" id="timeZone" disabled="disabled">
                            <option value="0">Select</option>
							<?php
								//$timeZone
							?>
                          </select>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Unit Quantity: </td>
				        <td><input name="qty" type="text" id="qty" size="10" readonly="readonly" class="readonly" value="<?php echo($qty); ?>" onchange="updateQuantity();" /></td>
			          </tr>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Unit Frequency: </td>
				        <td><select name="unitFreq" id="unitFreq" onchange="updateQuantity();" disabled="disabled">
                            <?php
								populateFrequency($freq);						
							  ?>
                          </select>
						</td>
			          </tr>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Total Quantity: </td>
				        <td><input name="totalQty" type="text" id="totalQty" value="<?php echo($totalQty); ?>" size="12" readonly="readonly" class="readonly" /></td>
			          </tr>

                    </table></td>
			      </tr>
				</table>
			</div>
		</div>
		
		
		<div class="fieldGroup floatLeft" style="width: 382px; margin-right: 15px;">
			<div class="groupHeader">
				Buying Broker Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Buying Broker: </td>
					<td><select name="buyBroker" id="buyBroker" disabled="disabled">
					   <?php
					  	populateBrokerList($buyBroker);						
					  ?>
					  </select>
					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Commission Rate: </td>
					<td nowrap="nowrap"><input name="buyBrokerRate" type="text" id="buyBrokerRate" readonly="readonly" class="readonly" size="10" value="<?php echo($buyBrokerRate); ?>" onchange="updateCommission('buy');" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Total Commission: </td>
					<td><input name="buyBrokerTotal" type="text" id="buyBrokerTotal" size="12" readonly="readonly" class="readonly" value="<?php echo($buyBrokerComm); ?>" /></td>
				  </tr>
				</table>
			</div>
		</div>
		
		<div class="fieldGroup floatLeft" style="width: 382px;">
			<div class="groupHeader">
				Selling Broker Information
			</div>
		  <div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
                    <td class="fieldLabel">Selling Broker: </td>
				    <td><select name="sellBroker" id="sellBroker" disabled="disabled">
                         <?php
							populateBrokerList($sellBroker);						
						  ?>
                      </select>
                    </td>
			      </tr>
				  <tr>
                    <td class="fieldLabel">Commission Rate: </td>
				    <td nowrap="nowrap"><input name="sellBrokerRate" type="text" id="sellBrokerRate" readonly="readonly" class="readonly" size="10" value="<?php echo($sellBrokerRate); ?>" onchange="updateCommission('sell');" /></td>
			      </tr>
				  <tr>
                    <td class="fieldLabel">Total Commission: </td>
				    <td><input name="sellBrokerTotal" type="text" id="sellBrokerTotal" size="12" readonly="readonly" class="readonly" value="<?php echo($sellBrokerComm); ?>" /></td>
			      </tr>
				</table>
			</div>
		</div>
		
		<div class="clear"></div>
		
		
		<div>
			<div class="floatLeft">
				<input type="button" name="cancel" value="Close" onclick="confirmCancel('close this window');" class="buttonSmall" />
			</div>
			<div class="floatRight">
				<input type="submit" name="selectType" value="Cancel Trade" class="buttonSmall" />
			</div>
		</div>
		
		<input type="hidden" name="action" value="cancelTrade" />
		<input type="hidden" name="tradeType" value="<?php echo($tradeType); ?>" />
		
		</form>

	<?php
	}
	else {
		
	?>

		<p class="errorMessage">No trade type found.</p>
		
	<?php
	}
	?>
	
	
	<?php
	require_once("../util/popupBottom.php");
	?>