/*******************************
* Scripts
*
*******************************/

var last;
var activeTab = "";
var blockBaseState;

/*********************************
* function showHide
* IN: object ID (or an array of IDs)
*
* this function is used to toggle
* the visibility of a page element
**********************************/
function showHide(objIds,dispType) {
	if(isArray(objIds)){
		for(var i=0; i<objIds.length; i++){
			var objRef = $("#"+objIds[i]).toggle();
		}
	}
	else {	
		$("#"+objIds).toggle();
	}
	
}

function hide(fieldRef) {
	//setTimeout("hideIt('"+fieldRef+"');",500);
	$("#"+fieldRef).fadeOut(400);
}
/*function hideIt(fieldRef){
	$("#"+fieldRef).hide();
}*/

/****************************
* function isArray
* IN: any variable
* OUT: bool
*
* This function returns true if the variable
* that was passed in is an array 
*******************************/
function isArray(myVar) {	
	var tempType = typeof myVar;
	//alert(tempType);

	if (tempType == "string")
		return false;
	else
		return true;
}


function toggleTab(tabRef) {
	currTabRef = $(activeTab);
	newTabRef = $(tabRef);
	
	currTabRef.removeClass("selected");
	newTabRef.addClass("selected");
	
	$("#"+currTabRef.attr("id")+"_content").hide();
	$("#"+newTabRef.attr("id")+"_content").show();
	
	activeTab = newTabRef;
}


function displayDate(strFieldName,strDate) {
	$("#"+strFieldName).val(strDate);
	
	try {
		checkDates();
		updateQuantity();
	}
	catch(e){
		//do nothing
	}
}


function launchTrade(templateID) {
	newWindow("trade/tradeInput.php?tradeType="+templateID,900,1200,"no");
}

function launchClient(clientID) {
	height = 700;
	if(isIE())
		height += 40;
	newWindow("trade/clientDetail.php?clientID="+clientID,height,950,"no");
}

function launchClientNested(clientID) {
	height = 700;
	if(isIE())
		height += 40;
	newWindow("../trade/clientDetail.php?clientID="+clientID,height,840,"no");
}

function launchCompany(companyID) {
	height = 610;
	if(isIE())
		height += 40;
	newWindow("trade/companyDetail.php?companyID="+companyID,height,860,"no");
}

function launchLocation(locationID) {
	height = 500;
	if(isIE())
		height += 40;
	if(locationID) {
		newWindow("trade/locationDetail.php?locationID="+locationID,height,800,"no");
	} else {
		newWindow("trade/locationDetail.php",height,800,"no");
	}
}

function launchBroker(brokerID) {
	newWindow("trade/brokerDetail.php?brokerID="+brokerID+"&superAdmin=1",335,490,"no");
}

function launchDetail(templateID,tradeID) {
	//newWindow("trade/tradeDetail.php?tradeType="+templateID+"&tradeID="+tradeID+"&height=570",600,850,"no");
	newWindow("trade/tradeInput.php?tradeType="+templateID+"&tradeID="+tradeID,900,1200,"no");
}

function launchDetail2(templateID,tradeID) {
	//newWindow("trade/tradeDetail.php?tradeType="+templateID+"&tradeID="+tradeID+"&height=570",600,850,"no");
	newWindow("tradeInput.php?tradeType="+templateID+"&tradeID="+tradeID+"&height=570",615,900,"no");
}

function invoiceDetail(clientID,startDate,endDate) {
	if(startDate == undefined)
		startDate="";
	if(endDate == undefined)
		endDate="";
	if(clientID != "")
		newWindow("trade/invoiceDetail.php?companyID="+clientID+"&startDate="+startDate+"&endDate="+endDate,900,1200,"no");	
	
}

function isIE(){
	if(navigator.appName == "Microsoft Internet Explorer")
		return true;
	else
		return false;
}

/***************************************
* function newWindow
* IN: page URL, window height, window width, scrollbars (yes/no)
*
* Calls a new popup window through a page
* URL passed into function
****************************************/
function newWindow(myURL,h,w,scrollBars) {
	var winl = (screen.width - w) / 2;
	var wint = ((screen.height - h) / 2) - 20;
	var now = new Date();
	//var windowID = now.getTime();
	var windowCount = $.cookie("windowCt");
	//alert(windowCount);
	
	if(windowCount == null || windowCount == undefined)
		$.cookie("windowCt", 0, {path: '/'} ); //set cookie
	
	//alert(windowCount);
	windowCount = $.cookie("windowCt");	
	var windowID = "bosworthPopup"+windowCount;
	//alert(windowID);
	winprops = 'height=' + h + ',width=' + w +',top=' + wint + ',left= ' + winl + ', scrollbars=' + scrollBars + ', resizable=no';
	
	testWindow = window.open(myURL,windowID,winprops);
	testWindow.focus();
	windowCount++;
	$.cookie("windowCt",windowCount, {path: '/'} ); //update cookie
}



function highlightRow(cellRef){
	var rowRef = cellRef.parentNode;
	try {
		currState = rowRef.getElementsByTagName("INPUT")[0].checked;
		
		if(currState) {
			if(rowRef.className.split(" ")[0] == "highlight")
				rowRef.className = "";
			else
				rowRef.className = rowRef.className.split(" ")[0];
			rowRef.getElementsByTagName("INPUT")[0].checked = false;
		}
		else{
			rowRef.className = rowRef.className + " highlight";
			rowRef.getElementsByTagName("INPUT")[0].checked = true;	
		}
	}
	catch(e){
		//nothing	
	}
	
}

function highlightRowCheck(checkRef){
	var rowRef = checkRef.parentNode.parentNode;
	currState = checkRef.checked;
	
	if(!currState) {
		if(rowRef.className.split(" ")[0] == "highlight")
			rowRef.className = "";
		else
			rowRef.className = rowRef.className.split(" ")[0];
	}
	else{
		rowRef.className = rowRef.className + " highlight";
	}
}

function confirmSubmit() {
	if(confirm("Are you sure you want to submit this trade?")){
		if(validateTrade())
			return true;
		else
			return false;
	}

	return false;
}
function confirmSubmit2() {
	return confirm("Are you sure you want to cancel this trade?");	
}

function confirmCancel(strMessage) {
	if(confirm("Are you sure you want to "+strMessage+"?")){
		window.close();	
	}
}

function confirmClientDelete(clientID){
	if(confirm("Are you sure you want to delete this client?")){
		document.deleteForm.deleteID.value = clientID;
		document.deleteForm.submit();
	}	
}

function confirmBrokerDelete(clientID){
	if(confirm("Are you sure you want to delete this broker?")){
		document.deleteForm.deleteID.value = clientID;
		document.deleteForm.submit();
	}	
}

function clearFilters() {
	$("#tradeID").val("");
	$("#startDate").val("");
	$("#endDate").val("");
	$("#productID").attr('selectedIndex', 0);
	$("#statusID").attr('selectedIndex', 1);
	$("#productGroup").attr('selectedIndex', 0);
	$("#brokerListID").attr('selectedIndex', 0);
	$("#companyName").val("");
	$("#tradeFilters").submit();
}
function clearClientFilters() {
	$("#traderName").val("");
	$("#companyName").val("");
	$("#emptyEmail").attr('checked', false);
	document.tradeFilters.submit();
}
function clearInvoiceFilters() {
	$("#tradeID").val("");
	$("#startDate").val("");
	$("#endDate").val("");
	$("#productID").attr('selectedIndex', 0);
	$("#productGroup").attr('selectedIndex', 0);
	document.tradeFilters.submit();
}
function clearLocationFilters() {
	$("#locationName").val("");
	$("#locationStatus").val("1");
	document.tradeFilters.submit();
}

function sorter(field){
	if($("#last").val() == field)
		toggleOrder();
	
	$("#last").val(field);
	$("#sortField").val(field);
	$("#tradeFilters").submit();
}

function toggleOrder() {
	if($("#sortOrder").val() == "ASC"){
		$("#sortOrder").val("DESC");
	}
	else
		$("#sortOrder").val("ASC");
}


function confirmCancelTrade() {
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to cancel these trades?")){
			document.tradeList["action"].value = "cancelTrade";
			document.tradeList.submit();			
		}
	}
	else
		alert("You must select a trade before canceling!");
}

function resendTradeConfirm(sendType) {
	//alert("working");
	
	var empty = validateCheckboxes();
	
	if(!empty){
		/*if(confirm("Are you sure you want to resend confirms for these trades?")){*/
			if(sendType == 1){
				//alert("send to me");
				if(confirm("Are you sure you want to resend confirms for these trades?")) {
					document.tradeList["action"].value = "resendConfirmToMe";
					$("#tradeList").submit();
				}
			}
			else{
				//alert("send to list/individual");
				document.tradeList["action"].value = "resendConfirm";
				
				//invoke resend modal window
				$.post("util/ajax.php", $("#tradeList").serialize(), function(output) {
					//alert(output);
					if(output != "") {
						$("#recipTable").html(output);
					}
				} );
				
				$.colorbox( {inline:true, href:"#resendDialog"} );
			}
			
		/*}*/
	}
	else
		alert("You must select a trade before resending!");
}


function submitResend() {
	
	var empty = validateCheckboxes2();
	
	if(!empty){
		if(confirm("Are you sure you want to resend confirms for these trades?")){
			
			$("#confirmRecip input:checked").each(function() {
				temp = "<input type=\"hidden\" name=\"clientID[]\" value=\""+$(this).val()+"\" />";
				$("#tradeList").append( $(temp) );
			});
			
			$("#tradeList").submit();
			$.colorbox.close();		
		}
	}
	else
		alert("You must select a company before resending!");
}

function closeModal() {
	$.colorbox.close();
}


function confirmDeleteTrade() {
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to delete these trades?")){
			document.tradeList["action"].value = "deleteTrade";
			document.tradeList.submit();			
		}
	}
	else
		alert("You must select a trade before deleting!");
}


function settleTrade() {
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to settle these trades?")){
			document.tradeList["action"].value = "settleTrade";
			document.tradeList.submit();
		}
	}
	else
		alert("You must select a trade before settling!");
}


function voidTrade() {
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to void these trades?")){
			document.tradeList["action"].value = "voidTrade";
			document.tradeList.submit();
		}
	}
	else
		alert("You must select a trade before voiding!");
}

function deleteTrade() {
	var empty = validateCheckboxes();
	if(!empty){
		if(confirm("WARNING: This will permanently delete the selected trades from the database. This action cannot be undone. Are you absolutely sure?")){
			if(confirm("Final confirmation: You are about to permanently delete these trades. This will remove all records and cannot be reversed. Continue?")){
				document.tradeList["action"].value = "deleteTrade";
				document.tradeList.submit();
			}
		}
	}
	else
		alert("You must select a trade before deleting!");
}


function confirmConfirm() {
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to confirm these trades?")){
			document.tradeList["action"].value = "confirmTrade";
			document.tradeList.submit();
		}
	}
	else
		alert("You must select a trade before confirming!");
}

function previewConfirm() {
	var empty = validateCheckboxes();
	
	if(!empty){
		$("#tradeList input:checked").each( function() {
			newWindow("trade/previewConfirm.php?tradeID="+$(this).val(),500,700,"no");
		});
	}
	else
		alert("You must select a trade before previewing!");
}


function invoiceSend() {
	
	var empty = validateCheckboxes();
	
	if(!empty){
		if(confirm("Are you sure you want to send invoices now?")){
			document.tradeList["action"].value = "invoiceSend";
			document.tradeList.submit();
		}
	}
	else
		alert("You must select a company before sending!");
}


function resendInvoice() {
	
	if(confirm("Are you sure you want to resend this invoice now?")){
		document.tradeList["action"].value = "invoiceResend";
		document.tradeList.submit();
	}
}


function validateCheckboxes() {
	var fieldRef = document.tradeList["transID[]"];
	var empty = true;

	if(fieldRef.length == undefined){
		if(fieldRef.checked == true)
			empty = false;
	}
	else{
		count = fieldRef.length;
		
		for(i=0; i<count; i++){
			if(fieldRef[i].checked == true){
				empty = false;
				break;
			}
		}
	}
	
	return empty;
}

function validateCheckboxes2() {
	var fieldRef = document.confirmRecip["clientID[]"];
	var empty = true;

	if(fieldRef.length == undefined){
		if(fieldRef.checked == true)
			empty = false;
	}
	else{
		count = fieldRef.length;
		
		for(i=0; i<count; i++){
			if(fieldRef[i].checked == true){
				empty = false;
				break;
			}
		}
	}
	
	return empty;
}


function refreshView(pageName) {
	//window.location.href = "index.php?p="+pageName;	
	if(pageName == "brokers" || pageName == "invoice")
		window.location = "index.php?p="+pageName;
	else
		document.tradeFilters.submit();
}

function showDetails(listRef) {
		
	var paramRef = $(listRef).val();
	var traderRef = $(listRef).attr("id");
	
	$.post("../util/ajax.php", { param:paramRef }, function(returnValue) {

		if(returnValue != "") {			
			if((returnValue.indexOf("ICE CLEARED") >= 0) || (returnValue.indexOf("NYMEX CLEARED") >= 0)){
				updateFields("",traderRef);
			}
			else {
				updateFields(returnValue,traderRef);
			}
		}
		else {
			/*alert("Request failed to respond");*/
		}

	} );
}

//show/hide the clear ID field on trade entry
function showClearField(fieldRef) {
	
	if(fieldRef.value == "ICE" || fieldRef.value == "NYMEX" || fieldRef.value == "NGX" || fieldRef.value == "NODAL" || fieldRef.value == "NASDAQ") {
		$("#clearIDField").show();
		if((tradeFields.indexOf("clearID") < 0)) {
			tradeFields.push("clearID");
		}
	}
	else {
		$("#clearIDField").hide();
		if(tradeFields.indexOf("clearID") > 0)
			tradeFields.splice(tradeFields.indexOf("clearID"));
	}
}

function updateFields(responseText,type) {
	var dataSet = responseText;
	var pieces = dataSet.split("\t\n");
	//alert(type);
	if(responseText == "") {
		$("#"+type+"Company").val("");
		$("#"+type+"Address").val("");
		$("#"+type+"Phone").value = "";
		$("#"+type+"Fax").val("");
	}
	else {
		$("#"+type+"Company").val(pieces[0]);
		$("#"+type+"Address").val(pieces[1]);
		$("#"+type+"Phone").val(pieces[2]);
		$("#"+type+"Fax").val(pieces[3]);
	}
}


function toggleWidth(fieldRef){
	fieldRef.style.position = "absolute";
	fieldRef.style.width = "";
	fieldRef.style.marginTop = "-10px";
}

function resetWidth(fieldRef){
	fieldRef.style.position = "";
	fieldRef.style.width = "170px";
	fieldRef.style.marginTop = "";
}

function updateRegion() {
	
	region = $("#location").val();
	//ajaxRegion("../util/ajax.php?region="+fieldRef.value);
	$.post("../util/ajax.php", { region:region }, function(returnValue) {

		if(returnValue != "") {
			var parts = returnValue.split("\t");
			var region = parts[0];
			var timezone = parts[1];
			updateRegionText(region);
			updateTimezoneText(timezone);
		}
		else {
			/*alert("Request failed to respond");*/
		}

	} );
}
function updateRegionText(strText){
	$("#region").val(strText);
	blockID = $("#block").val();
	strLocation = $("#location").val();
		
	$.post("../util/ajax.php", { blocks:1, blockID:blockID, blockRegion:strText, locationID:strLocation }, function(returnValue) {

		if(returnValue != "") {
			$("#block").html(returnValue);
		}
		else {
			/*alert("Request failed to respond");*/
		}

	} );

}
function updateTimezoneText(strText){
	if($("#timezone"))
		$("#timezone").val(strText);
}

function updateHours(type,dayType/*startDay,endDay*/,startHour,endHour){
	//alert("../util/ajax.php?customHours=1&type="+type+"&startDay="+startDay+"&endDay="+endDay+"&startHour="+startHour+"&endHour="+endHour);
	//ajaxHours("../util/ajax.php?customHours=1&type="+type+"&startDay="+startDay+"&endDay="+endDay+"&startHour="+startHour+"&endHour="+endHour);
	//ajaxHours("../util/ajax.php?customHours=1&type="+type+"&dayType="+dayType+"&startHour="+startHour+"&endHour="+endHour);
	
	$.post("../util/ajax.php", { customHours:1, type:type, dayType:dayType, startHour:startHour, endHour:endHour }, function(returnValue) {
	
		if(returnValue != "") {
			$("#hoursBlock").html(returnValue);
		}
		else {
			alert("Request failed to respond");
		}

	} );
}
function updateExistingClient(clientList){
	var clientID = clientList.value;
	//alert(clientID);
	//ajaxClientList("../util/ajax.php?existing=1&clientID="+clientID);
	
	$.post("../util/ajax.php", { existing:1, clientID:clientID }, function(returnValue) {

		if(returnValue != "") {
			var parts = returnValue.split("\t\n");
	
			$("#companyName").val(parts[4]);
			$("#companyShortName").val(parts[24]);
			$("#invoiceName").val(parts[2]);
			$("#email").val(parts[3]);
			$("#street1").val(parts[5]);
			$("#street2").val(parts[6]);
			$("#city").val(parts[7]);
			$("#state").val(parts[8]);
			$("#zip").val(parts[9]);
			$("#country").val(parts[10]);
			$("#phone").val(parts[11]);
			$("#fax").val(parts[12]);
			$("#confirmClientName").val(parts[14]);
			$("#confirmEmail").val(parts[13]);
			$("#confirmStreet1").val(parts[15]);
			$("#confirmStreet2").val(parts[16]);
			$("#confirmCity").val(parts[17]);
			$("#confirmState").val(parts[18]);
			$("#confirmZip").val(parts[19]);
			$("#confirmCountry").val(parts[20]);
			$("#confirmPhone").val(parts[21]);
			$("#confirmFax").val(parts[22]);
		}
		else {
			alert("Request failed to respond");
		}

	} );
	
}

/*function updateClientFields(strText){
	
}
*/
function updateQuantity() {
	
	var qty = $("#qty").val();
	if($("#unitFreq"))
		var freq = $("#unitFreq").val();
	var startDate = $("#startDate").val();
	var endDate = $("#endDate").val();
	if(block == undefined) {
		var block = $("#block").val();
	}
	
	var dayType = "";
	var startHour = "";
	var endHour = "";
		
	try{
		/*var startDay = document.getElementById("startDay")[document.getElementById("startDay").selectedIndex].value;
		var endDay = document.getElementById("endDay")[document.getElementById("endDay").selectedIndex].value;*/
		/*var dayType = document.getElementById("dayType")[document.getElementById("dayType").selectedIndex].value;
		var startHour = document.getElementById("startHour")[document.getElementById("startHour").selectedIndex].value;
		var endHour = document.getElementById("endHour")[document.getElementById("endHour").selectedIndex].value;*/
		dayType = $("#dayType").val();
		startHour = $("#startHour").val();
		endHour = $("#endHour").val();
		timezone = $("#timezone").val();
	
	} catch(e) { /*alert("an error occurred");*/ }
	
	if(block == 6) {
		//custom block
		//if(startHour != undefined && endHour != undefined)
		updateHours(1,dayType,startHour,endHour);
	}
	else if(block == 7) {
		//west peak - Mon-Sat 07-22 non-nerc
		dayType = 5;
		startHour = 700;
		endHour = 2200;
		updateHours(0,dayType,startHour,endHour);
		//updateHours(0,'','','','');
	}
	else if(block == 8) {
		//west off-peak - Mon-Sat 01-06, 23-24, Sun 01-24 nerc
		dayType = 4;
		startHour = new Array(100,2300,100);
		endHour = new Array(600,2400,2400);
		updateHours(0,dayType,startHour,endHour);
		//updateHours(0,'','','','');
	}
	else {
		//if($("#hours").val() == "")
			updateHours(0,'','','','');
	}
	
	//alert("../util/ajax.php?calculate=1&block="+block+"&startDate="+startDate+"&endDate="+endDate+"&qty="+qty+"&freq="+freq+"&startDay="+startDay+"&endDay="+endDay+"&startHour="+startHour+"&endHour="+endHour);
	//ajaxQuantity("../util/ajax.php?calculate=1&block="+block+"&startDate="+startDate+"&endDate="+endDate+"&qty="+qty+"&freq="+freq+"&startDay="+startDay+"&endDay="+endDay+"&startHour="+startHour+"&endHour="+endHour);
	//alert("../util/ajax.php?calculate=1&block="+block+"&startDate="+startDate+"&endDate="+endDate+"&qty="+qty+"&freq="+freq+"&dayType="+dayType+"&startHour="+startHour+"&endHour="+endHour);
	//var queryStr = "../util/ajax.php?calculate=1&block="+block+"&startDate="+startDate+"&endDate="+endDate+"&qty="+qty+"&freq="+freq+"&dayType="+dayType+"&startHour="+startHour+"&endHour="+endHour;
	//alert(queryStr);
	//ajaxQuantity(queryStr);
	
	var locationID = $("#location").val();
	
	$.post("../util/ajax.php", { calculate:1, block:block, startDate:startDate, endDate:endDate, qty:qty, freq:freq, dayType:dayType, startHour:startHour, endHour:endHour, locationID:locationID, timezone:timezone }, function(returnValue) {
		
		//alert(returnValue);
		
		if(returnValue != "") {
			var parts = returnValue.split('\n');
			
			setTimeout( function() {
				$("#totalQty").val(parts[0]);
				$("#hours").val(parts[1]);
				
				updateCommission("all"); //moved here to ensure values are present before recalc
			}, 100 );

		}
		else {
			//alert("Request failed to respond");
		}

	} );

}

//function updateQuantityText(strText) {
	/*var total = $("#totalQty"); removed*/
	/*if($("#hours"))
		var hours = $("#hours") removed;*/

	/*var parts = strText.split("\n");  //temporary remove
	
	$("#totalQty").val(parts[0]);
	if(parts[1] != "") {
		$("#hours").val(parts[1]);
	}*/
	
	//updateCommission("buy");
	//updateCommission("sell");
	//updateCommission("all",'');  //temporary remove
//}

function toggleFilter(newState){ 
	//ajaxFilter(newState);
	$.post("util/ajax.php", { filter:newState }, function(returnValue) {	} );
}

/****************
* updateCommission
*
* update commission for one broker
*****************/
function updateCommission(party,brokerCount){
	
	if(party == "all") {
		updateCommission("buy",'');
		updateCommission("sell",'');
		
		for(var i=2; i<=buyBrokerCount; i++)
			updateCommission("buy",i);
		for(var i=2; i<=sellBrokerCount; i++)
			updateCommission("sell",i);
	}
	else {
		try {
			var rateRef = $("#"+party+"BrokerRate"+brokerCount).val();
			var totalRef = $("#"+party+"BrokerTotal"+brokerCount);
			var total = $("#totalQty").val();
			
			var roughTotal = total * rateRef;
			$(totalRef).val( CurrencyFormatted(roughTotal) );
			verifyManualCommission2(""); //update display buy/sell broker totals
		} catch(e) { alert("error!"); }
	}

}

function CurrencyFormatted(amount)
{
	var i = parseFloat(amount);
	if(isNaN(i)) { i = 0.00; }
	var minus = '';
	if(i < 0) { minus = '-'; }
	i = Math.abs(i);
	i = parseInt((i + .005) * 100);
	i = i / 100;
	s = new String(i);
	if(s.indexOf('.') < 0) { s += '.00'; }
	if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
	s = minus + s;
	return s;
}

function setSame(checkRef) {
	//set client fields the same as invoice information (from visible fields)
	if(checkRef.checked == true){
		// Copy from the visible Invoice Information fields to Trade Confirm fields
		// Use .value for disabled/readonly fields instead of .val()
		$("#confirmClientName").val( document.getElementById('invoiceName').value );
		$("#confirmEmail").val( document.getElementById('email').value );
		$("#confirmStreet1").val( document.getElementById('street1').value );
		$("#confirmStreet2").val( document.getElementById('street2').value );
		$("#confirmCity").val( document.getElementById('city').value );
		$("#confirmState").val( document.getElementById('state').value );
		$("#confirmZip").val( document.getElementById('zip').value );
		$("#confirmPhone").val( document.getElementById('phone').value );
		$("#confirmFax").val( document.getElementById('fax').value );
		$("#confirmCountry").val( document.getElementById('country').value );
	}
	else {
		$("#confirmClientName").val("");
		$("#confirmEmail").val("");
		$("#confirmStreet1").val("");
		$("#confirmStreet2").val("");
		$("#confirmCity").val("");
		$("#confirmState").val("");
		$("#confirmZip").val("");
		$("#confirmPhone").val("");
		$("#confirmFax").val("");
		$("#confirmCountry").val("");
	}
}

function fetchParentCompanyInvoiceInfo(companyName) {
	// Use AJAX to fetch parent company's invoice information
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '../util/ajax.php', true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			try {
				var response = JSON.parse(xhr.responseText);
				if (response.success) {
					// Populate trade confirm fields with parent company's invoice data
					$("#confirmClientName").val(response.invoiceContactName || "");
					$("#confirmEmail").val(response.invoiceContactEmail || "");
					$("#confirmStreet1").val(response.street1 || "");
					$("#confirmStreet2").val(response.street2 || "");
					$("#confirmCity").val(response.city || "");
					$("#confirmState").val(response.state || "");
					$("#confirmZip").val(response.zip || "");
					$("#confirmPhone").val(response.phone || "");
					$("#confirmFax").val(response.fax || "");
					$("#confirmCountry").val(response.country || "");
				} else {
					alert('Error fetching company information: ' + (response.error || 'Unknown error'));
				}
			} catch (e) {
				alert('Error parsing company information response.');
			}
		}
	};
	
	xhr.send('action=fetchCompanyInvoiceInfo&companyName=' + encodeURIComponent(companyName));
}


function selectAll(formRef) {
	inputArr = document.getElementById(formRef).getElementsByTagName("input");
	
	for(i=0; i<inputArr.length; i++) {
		if(inputArr[i].type == "checkbox"){
			inputArr[i].checked = true;	
		}
	}
}

function sendInvoice(invoiceType) {
	
	if(invoiceType == "single"){
		var empty = validateCheckboxes();
		
		if(!empty){
			if(confirm("Are you sure you want to send an invoice for the selected trades?")){
				document.tradeList["action"].value = "sendInvoice";
				document.tradeList.submit();
			}
		}
		else
			alert("You must select a trade before sending an invoice!");
	}
	else if(invoiceType == "all"){
		
	}
}


function getTradeDataRequest(formRef) {
		
	if( $("#period2").val() == "custom") {
		if( $("#startDate2").val() == "" ) {
			alert("You must enter a start date.");
			return false;
		}
		else if( $("#endDate2").val() == "" ) {
			alert("You must enter an end date.");
			return false;
		}	
	}
	else {
		if(formRef.month.value == "") {
			alert("You must select a month.");
			return false;
		}
		else if (formRef.year.value == "") {
			alert("You must select a year.");
			return false;
		}
	}
	
	return true;
}

function getCommissionDataRequest(formRef) {
	brokerID = "";
	
	//broker id required for admins
	try {
		if( $("#brokerID").val() < 0 || $("#brokerID").val() == "") {
			alert("You must select a broker");
			return false;
		}
	} catch(e) {}		
	
	//period required
	if( $("#period").val() == "") {
		alert("You must select a period.");
		return false;
	}
	
	//custom - date fields required
	if( $("#period").val() == "custom") {
		if( $("#startDate").val() == "" || $("#endDate").val() == "") {
			alert("You must enter a start date and end date");
			return false;
		}
	}
	
	try {
		brokerID = $("#brokerID").val();	
	} catch(e) {}
	
	requestStr = "trade/reportDetail.php?period="+$("#period").val()+"&brokerID="+brokerID+"&startDate="+$("#startDate").val()+"&endDate="+$("#endDate").val()+"&extended="+$("#extended_format").attr("checked");
	//alert(requestStr);
	
	newWindow(requestStr,400,550,"yes");
	
	return true;
}

function createCopy(templateID,tradeID) {
	newWindow("tradeInput.php?tradeType="+templateID+"&tradeID="+tradeID+"&height=570&copyTrade=1",610,860,"no");	
}

function submitAdminUpdate() {
	var newField = document.createElement("input");
	newField.setAttribute("type","hidden");
	newField.setAttribute("name","adminAction");
	newField.setAttribute("value","overrideUpdate");
	document.tradeInfo.appendChild(newField);
	document.tradeInfo.submit();
}

function showResults(fieldRef,target){
	searchString = fieldRef.value;
	target = document.getElementById(target);
	if(searchString != "")
		ajaxSearchCompany("../util/ajax.php?searchCompany=1&searchString="+searchString,target);	
}

function handleCompany(companyID,fieldRef) {
	origRef = fieldRef;
	fieldRef = fieldRef.parentNode.parentNode.previousSibling;
	while(fieldRef.nodeType != 1){
		fieldRef = fieldRef.previousSibling;
	}
	fieldRef.value = origRef.innerHTML;
	if(fieldRef.id == "filterBuyer")
		target = document.getElementById("buyer");
	else if(fieldRef.id == "filterSeller")
		target = document.getElementById("seller");
	//alert(target.id);
	ajaxSearchTraders("../util/ajax.php?getClientList=1&companyID="+companyID,target);
}


function checkCustom(fieldRef) {
	
	if(fieldRef.id == "period") {
		if(fieldRef.value == "custom")
			$("#customFields").show();
		else
			$("#customFields").hide();
	}
	else if(fieldRef.id == "period2") {
		if(fieldRef.value == "custom") {
			$("#monthlyFields").hide();
			$("#customFields2").show();
		}
		else {
			$("#customFields2").hide();
			$("#monthlyFields").show();
		}
	}
		
}

//generate extract output/file
function getIndex() {
	
	if( $("#reportName").val() == "" )
		alert("You must select a report.");
	else if( $("#hubName").val() == "" )
		alert("You must select a hub.");
	else if( $("#startDate").val() == "" )
		alert("You must select a start date.");
	else if( $("#endDate").val() == "" )
		alert("You must select an end date.")
	else
		newWindow("util/ajax.php?"+$("#indexForm").serialize(),100,100,"no");
}

//dynamic call for hub list extract
function getHubList(indexVal) {
	var indexName = $(indexVal).val();
	
	$.post("util/ajax.php", { indexType:indexName }, function(returnValue) {		
		if(returnValue != "")
			$("#hubName").html(returnValue);
		else
			alert("Request failed.");
	} );
}

function toggleMode(buttonRef) {
	/*if(buttonRef.checked == true) {
		//set dark mode*/
		$.post("util/ajax.php", { viewState:buttonRef.checked }, function(returnValue) {		
		
		if(returnValue != "")
			$("#style-link").attr("href",returnValue);
		else
			alert("Request failed.");
	} );
	
	/*
	}
	else {
		//set light mode	
	}	*/
}



