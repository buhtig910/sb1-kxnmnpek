/********************************
* function parsePage
*
*********************************/
function initAjax() {
	
	/*var objXml;*/
	
	var xmlhttp = false;
   	/* running locally on IE5.5, IE6, IE7 */
     if(location.protocol=="file:"){
      if(!xmlhttp)try{ xmlhttp=new ActiveXObject("MSXML2.XMLHTTP"); }catch(e){xmlhttp=false;}
      if(!xmlhttp)try{ xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); }catch(e){xmlhttp=false;}
     }
   	/* IE7, Firefox, Safari, Opera...  */
     if(!xmlhttp)try{ xmlhttp=new XMLHttpRequest(); }catch(e){xmlhttp=false;}
   	/* IE6 */
     if(typeof ActiveXObject != "undefined"){
      if(!xmlhttp)try{ xmlhttp=new ActiveXObject("MSXML2.XMLHTTP"); }catch(e){xmlhttp=false;}
      if(!xmlhttp)try{ xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); }catch(e){xmlhttp=false;}
     }
	 
	 return xmlhttp;
}


/*function doAjaxRequest(requestString,type){
        var http_request = new initAjax();
		
		if(http_request!=null){
			//alert("fetching");
			http_request.onreadystatechange = function() { alertContents(http_request,type); };
			var url = requestString;
			http_request.open("POST", url, true);
			http_request.send(null);
		}
}

function alertContents(http_request,type) {

        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
				var responseText = http_request.responseText;
				
				if((responseText.indexOf("ICE CLEARED") >= 0) || (responseText.indexOf("NYMEX CLEARED") >= 0)){
					//document.getElementById(type+"ClearField").style.display = "block"; //removed when clear IDs changed
					updateFields("",type);
				}
				else {
					//document.getElementById(type+"ClearField").style.display = "none"; //removed
					//document.getElementById(type+"ClearID").value = ""; //removed
					updateFields(http_request.responseText,type);
				}
            } else {
                alert("Request failed.");
            }
        }
}*/


/*function ajaxRegion(requestString){
        http_request = new initAjax();
		
		if(http_request!=null){
			//alert("fetching");
			http_request.onreadystatechange = function() { regionResponse(http_request); };
			var url = requestString;
			http_request.open("POST", url, true);
			http_request.send(null);
		}
}

function regionResponse(http_request) {

        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
				var parts = http_request.responseText.split("\t");
				var region = parts[0];
				var timezone = parts[1];
				updateRegionText(region);
				updateTimezoneText(timezone);
            } else {
                alert("Request failed.");
            }
        }
}*/

/* calculate trade total */
/*function ajaxQuantity(requestString){
        http_request = new initAjax();
		
		if(http_request!=null){
			//alert("fetching");
			http_request.onreadystatechange = function() { quantityResponse(http_request); };
			var url = requestString;
			http_request.open("POST", url, true);
			http_request.send(null);
		}
}

function quantityResponse(http_request) {

        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
				updateQuantityText(http_request.responseText);
            } else {
                alert("Request failed.");
            }
        }
}
*/

/*function ajaxFilter(newState){
	http_request = new initAjax();
		
	if(http_request!=null){
		//alert("fetching");
		//http_request.onreadystatechange = function() {  };
		var url = "util/ajax.php?filter="+newState;
		//alert(url);
		http_request.open("POST", url, true);
		http_request.send(null);
	}	
}*/


/*function ajaxHours(requestHoursString) {
	
	http_requestHours = new initAjax();
		
	if(http_requestHours!=null){
		//alert("fetching");
		http_requestHours.onreadystatechange = function() { hoursResponse(http_requestHours); };
		var url = requestHoursString;
		http_requestHours.open("POST", url, true);
		http_requestHours.send(null);
	}
}

function hoursResponse(http_requestHours) {
	
	 if (http_requestHours.readyState == 4) {
            if (http_requestHours.status == 200) {
				try{
					var blockRef = document.getElementById("hoursBlock");
					blockRef.innerHTML = http_requestHours.responseText;
				} catch(e) {}
				
            } else {
                alert("Request failed.");
            }
        }
}*/


/*function ajaxClientList(requestClientString) {
	http_requestClients = new initAjax();
		
	if(http_requestClients!=null){
		//alert("fetching");
		http_requestClients.onreadystatechange = function() { clientResponse(http_requestClients); };
		var url = requestClientString;
		http_requestClients.open("POST", url, true);
		http_requestClients.send(null);
	}
}

function clientResponse(http_requestClients) {
	 
	 if (http_requestClients.readyState == 4) {
		if (http_requestClients.status == 200) {
			try{
				//do something with returned values
				updateClientFields(http_requestClients.responseText);
				
			} catch(e) {}
			
		} else {
			alert("Request failed.");
		}
	}
}*/


function ajaxResendConfirm(requestTradeString) {
	http_requestClients = new initAjax();
	
	if(http_requestClients!=null){
		//alert("fetching");
		http_requestClients.onreadystatechange = function() { tradeResponse(http_requestClients); };
		var url = requestTradeString;
		http_requestClients.open("POST", url, true);
		http_requestClients.send(null);
	}
}

function tradeResponse(http_requestTrades) {
	 
	 responseRef = document.getElementById("ajaxResponse");
	 
	 if (http_requestTrades.readyState == 4) {
		if (http_requestTrades.status == 200) {
			try{
				responseRef.innerHTML = "<p class='confirmMessage'>Your trade confirm(s) has been resent successfully.</p>";
				responseRef.style.height = "40px";
			} catch(e) {}
			
		} else {
			responseRef.innerHTML = "<p class='errorMessage'>An error occured trying to resend your trade confirm(s).</p>";
			responseRef.style.height = "40px";
		}
	}
	else {
		responseRef.innerHTML = "<p class='warningMessage'>Resend in progress...</p>";
		responseRef.style.height = "30px";
	}
}


function ajaxSendInvoice(requestClientString) {
	http_requestInvoice = new initAjax();
		
	if(http_requestInvoice!=null){
		//alert(requestClientString);
		http_requestInvoice.onreadystatechange = function() { invoiceResponse(http_requestInvoice); };
		var url = requestClientString;
		http_requestInvoice.open("POST", url, true);
		http_requestInvoice.send(null);
	}
}

function invoiceResponse(http_requestInvoice) {
	 
	 responseRef = document.getElementById("ajaxResponse");
	 
	 if (http_requestInvoice.readyState == 4) {
		if (http_requestInvoice.status == 200) {
			try{
				responseRef.innerHTML = "<p class='confirmMessage'>Your invoice has been sent successfully.<br />Your trades will still appear in the list below until they have been settled.</p>";
				responseRef.style.height = "40px";
			} catch(e) {}
			
		} else {
			responseRef.innerHTML = "<p class='errorMessage'>An error occured trying to send your invoice.</p>";
			responseRef.style.height = "40px";
		}
	}
	else {
		responseRef.innerHTML = "<p class='warningMessage'>Invoicing in progress...</p>";
		responseRef.style.height = "30px";
	}
}

function ajaxSearchCompany(requestString,target) {
	http_requestCompany = new initAjax();
		
	if(http_requestCompany!=null){
		//alert(requestClientString);
		http_requestCompany.onreadystatechange = function() { companyResponse(http_requestCompany,target); };
		var url = requestString;
		http_requestCompany.open("POST", url, true);
		http_requestCompany.send(null);
	}
}

function companyResponse(http_requestCompany,target) {
	 
	 //responseRef = document.getElementById("ajaxResponse");
	 
	 if (http_requestCompany.readyState == 4) {
		if (http_requestCompany.status == 200) {
			try{
				//alert(http_requestCompany.responseText);
				//alert(target.id);
				target.innerHTML = http_requestCompany.responseText;
				target.style.display = "";
				//responseRef.innerHTML = "<p class='confirmMessage'>Your invoice has been sent successfully.<br />Your trades will still appear in the list below until they have been settled.</p>";
				//responseRef.style.height = "40px";
			} catch(e) {}
			
		} else {
			//responseRef.innerHTML = "<p class='errorMessage'>An error occured trying to send your invoice.</p>";
			//responseRef.style.height = "40px";
		}
	}
	else {
		//responseRef.innerHTML = "<p class='warningMessage'>Invoicing in progress...</p>";
		//responseRef.style.height = "30px";
	}
}

function ajaxSearchTraders(requestString,target) {

	http_requestTraders = new initAjax();
		
	if(http_requestTraders!=null){
		http_requestTraders.onreadystatechange = function() { traderResponse(http_requestTraders,target); };
		var url = requestString;
		http_requestTraders.open("POST", url, true);
		http_requestTraders.send(null);
	}
}

function traderResponse(http_requestTraders,target) {

	 if (http_requestTraders.readyState == 4) {
		if (http_requestTraders.status == 200) {
			try{
				var responseText = eval('('+http_requestTraders.responseText+')');
				var myNewOption = null;
				
				//alert(responseText["companies"].length);
				target.length = 0;
				for (var i=0; i<responseText["companies"].length; i++) {
					//alert(responseText["companies"][i].id);
					var myNewOption = new Option(responseText["companies"][i].value, responseText["companies"][i].id);

					try {
						target.add(myNewOption,null);
					}
					catch(e) {
						target.add(myNewOption);	
					}
				}
				
			} catch(e) {/* alert("An error occurred.");*/ }
			
		} else {
			//responseRef.innerHTML = "<p class='errorMessage'>An error occured trying to send your invoice.</p>";
		}
	}
	else {
		//responseRef.innerHTML = "<p class='warningMessage'>Invoicing in progress...</p>";
	}
}

// add multiple brokers to the trade entry screen
/*function ajaxAddBroker(requestString,target) {
	http_requestTraders = new initAjax();
		
	if(http_requestTraders!=null){
		http_requestTraders.onreadystatechange = function() { addBrokerResponse(http_requestTraders,target); };
		var url = requestString;
		http_requestTraders.open("POST", url, true);
		http_requestTraders.send(null);
	}
}
function addBrokerResponse(http_requestTraders,target) {

	 if (http_requestTraders.readyState == 4) {
		if (http_requestTraders.status == 200) {
			try{
				//var responseText = eval('('+http_requestTraders.responseText+')');
				var responseText = http_requestTraders.responseText;
				//target.innerHTML = target.innerHTML + responseText;
				$(target).append( responseText );
				//need to add JS calls to append required fields to list

				//update split commission - removed on 1.4.11
				if(target.id.indexOf("Buy") >= 0){
					//splitCommission(document.tradeInfo.buyCommTotal,"buy");
				}
				else if(target.id.indexOf("Sell") >= 0){
					//splitCommission(document.tradeInfo.sellCommTotal,"sell");
				}
				
			} catch(e) { alert("An error occurred adding a broker."); }
			
		} else {
			//responseRef.innerHTML = "<p class='errorMessage'>An error occured trying to send your invoice.</p>";
		}
	}
	else {
		//responseRef.innerHTML = "<p class='warningMessage'>Invoicing in progress...</p>";
	}
}*/
