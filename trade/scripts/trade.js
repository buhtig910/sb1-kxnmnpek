// Trade entry validation scripts
var tradeFields = new Array();
var buyBrokerCount = 0;
var sellBrokerCount = 0;

function checkDates(){
	var endRef = document.getElementById("endDate");
	var startRef = document.getElementById("startDate");
	var startDate = new Date(startRef.value);
	var endDate = new Date(endRef.value);

	var result = endDate - startDate;
	
	if(result < 0){
		alert("The end date must be later than the start date.");
		endRef.focus();
		endRef.className = "fieldError";
	}
	else
		endRef.className = "";
}

function validateTrade() {
	
	validTrade = true;
	
	for(i=0; i<tradeFields.length; i++){
		var fieldRef = document.getElementById(tradeFields[i]);
		if(fieldRef.value == "" || fieldRef.value == "undefined"){
			fieldRef.className = "fieldError";
			validTrade = false;
		}
		else{
			fieldRef.className = "";
		}
	}
	
	if(!validTrade){
		alert("One or more fields must be completed before entering this trade.");	
	}
	
	return validTrade;
}

function swapBuyerSeller() {
	
	buyerRef = $("#buyer");
	sellerRef = $("#seller");
	
	if($(buyerRef).val() == "") {
		alert("Select a buyer before trying to swap.");
	} 
	else if($(sellerRef).val() == "") {
		alert("	Select a seller before trying to swap.");
	}
	else {
		var selectedBuyerIndex = $(buyerRef).val();
		var selectedSellerIndex = $(sellerRef).val();
		var buyerOptions = new Array();
		var sellerOptions = new Array();
		
		//clear search fields
		temp1 = $("#filterBuyer").val();
		$("#filterBuyer").val( $("filterSeller").val() );
		$("#filterSeller").val(temp1);
		
		buyerList = $(buyerRef).html(); //do swap
		$(buyerRef).html( $(sellerRef).html() );
		$(sellerRef).html( buyerList );
		$(buyerRef).val(selectedSellerIndex); //updated selected items
		$(sellerRef).val(selectedBuyerIndex);
		
		showDetails(buyerRef); //update buyer preview info
		showDetails(sellerRef); //update seller preview info
		
		
		//also swap brokers
		buyBroker = $("#buyBroker").val();
		buyBrokerRate = $("#buyBrokerRate").val();
		buyBrokerComm = $("#buyBrokerTotal").val();
		sellBroker = $("#sellBroker").val();
		sellBrokerRate = $("#sellBrokerRate").val();
		sellBrokerComm = $("#sellBrokerTotal").val();
		
		$("#buyBroker").val(sellBroker); //swap first brokers
		$("#sellBroker").val(buyBroker);
		$("#buyBrokerRate").val(sellBrokerRate);
		$("#sellBrokerRate").val(buyBrokerRate);
		$("#buyBrokerTotal").val(sellBrokerComm);
		$("#sellBrokerTotal").val(buyBrokerComm);
		
		buyCommTotal = $("#buyCommTotal").val(); //swap total comms fields
		$("#buyCommTotal").val( $("#sellCommTotal").val() );
		$("#sellCommTotal").val( buyCommTotal );
		
		//swap additional brokers
		buyB = $("#moreBuyBrokers").find("select"); //get all dropdowns
		buyC = $("#moreBuyBrokers").find("input:text"); //get all text boxes
		sellB = $("#moreSellBrokers").find("select"); //get all dropdowns
		sellC = $("#moreSellBrokers").find("input:text"); //get all text boxes
		buyVals = new Array();
		sellVals = new Array();

		for(var i=0; i<buyB.length; i++) { //swap
			offset = (2*i);
			offset2 = offset + 1;
			buyVals[i] = new Array(buyB[i].value, buyC[offset].value, buyC[offset2].value);
		}
		for(var i=0; i<sellB.length; i++) {
			offset = (2*i);
			offset2 = offset + 1;
			sellVals[i] = new Array(sellB[i].value, sellC[offset].value, sellC[offset2].value);
		}
				
		$("#moreBuyBrokers").html("");
		$("#moreSellBrokers").html("");
		buyBrokerCount = 1;
		sellBrokerCount = 1;
		
		for(var i=0; i<buyVals.length; i++) {
			//alert("add sell: "+buyVals[i][0]+","+buyVals[i][1]);
			params = new Array(buyVals[i][0],buyVals[i][1],buyVals[i][2]);
			addBroker("moreSellBrokers",params);
		}
		for(var i=0; i<sellVals.length; i++) {
			//alert("add buy: "+buyVals[i][0]+","+buyVals[i][1]);
			params = new Array(sellVals[i][0],sellVals[i][1],sellVals[i][2]);
			addBroker("moreBuyBrokers",params);
		}
		
	}
	
}

//add additional broker block on trade entry screen
function addBroker(targetRef,params) {
	/* params
	0 = brokerID
	1 = rate
	2 = commission
	3 = buyer
	*/
	
	if(params == "") {
		params = new Array("","","");
	}
	
	if(targetRef == "moreBuyBrokers") {
		buyBrokerCount = $("#moreBuyBrokers").find("table").length + 1;
		tradeFields.push("buyBroker"+buyBrokerCount);
		tradeFields.push("buyBrokerRate"+buyBrokerCount);
		//alert("../util/ajax.php?addBroker=1&brokerType=buy&brokerCount="+ buyBrokerCount +"&selectedBroker="+params[0]+"&brokerRate="+params[1]+"&brokerComm="+params[2]);
		//ajaxAddBroker("../util/ajax.php?addBroker=1&brokerType=buy&brokerCount="+ buyBrokerCount +"&selectedBroker="+params[0]+"&brokerRate="+params[1]+"&brokerComm="+params[2],document.getElementById(targetRef));
		
		$.post("../util/ajax.php", { addBroker:1, brokerType:"buy", brokerCount:buyBrokerCount, selectedBroker:params[0], brokerRate:params[1], brokerComm:params[2] }, function(returnValue) {

			$("#"+targetRef).append( returnValue );
			//need to add JS calls to append required fields to list

			//update split commission - removed on 01.04.11
			//added again on 12.30.11
			splitCommission($("#buyCommTotal"),"buy");
	
		} );
		
		
	}
	else if(targetRef == "moreSellBrokers") {
		sellBrokerCount = $("#moreSellBrokers").find("table").length + 1;
		tradeFields.push("sellBroker"+sellBrokerCount);
		tradeFields.push("sellBrokerRate"+sellBrokerCount);
		//ajaxAddBroker("../util/ajax.php?addBroker=1&brokerType=sell&brokerCount="+ sellBrokerCount +"&selectedBroker="+params[0]+"&brokerRate="+params[1]+"&brokerComm="+params[2],document.getElementById(targetRef));
		
		$.post("../util/ajax.php", { addBroker:1, brokerType:"sell", brokerCount:sellBrokerCount, selectedBroker:params[0], brokerRate:params[1], brokerComm:params[2] }, function(returnValue) {

			$("#"+targetRef).append( returnValue );
			
			//update split commission - removed on 01.04.11
			//added again on 12.30.11
			splitCommission($("#sellCommTotal"),"sell");
	
		} );
	}	
}

//remove extra broker block on trade entry screen
function removeBrokerBlock(blockRef,fieldRef) {
	
	//add to delete queue for admin update
	removeID = $("#"+fieldRef).val();
	$("#addlDeleteIDs").val( $("#addlDeleteIDs").val()+","+removeID);
	
	
	var block = document.getElementById(blockRef).parentNode;
	var blockParent = block.parentNode;
	blockParent.removeChild(block);
	//block.innerHTML = "";
	tradeFields.splice(tradeFields.indexOf(fieldRef),1); //remove JS required field check
	var strLoc = fieldRef.indexOf("Broker");
	var rateRef = fieldRef.substr(0,strLoc) + "Rate" + fieldRef.substr(strLoc);
	tradeFields.splice(tradeFields.indexOf(fieldRef+"Rate"),1); //remove JS required field check
	
	if(blockRef.indexOf("buy") >= 0) {
		splitCommission($("#buyCommTotal"),"buy"); //removed on 1.4.11
		verifyManualCommission2("");
		$("#addlDeleteTypes").val( $("#addlDeleteTypes").val()+",buy"); //for admin delte
		//buyBrokerCount--;
	}
	else if(blockRef.indexOf("sell") >= 0) {
		splitCommission($("#sellCommTotal"),"sell"); //removed on 1.4.11
		verifyManualCommission2("");
		$("#addlDeleteTypes").val( $("#addlDeleteTypes").val()+",sell"); //for admin delete
		//sellBrokerCount --;
	}
}

// divide a total commission amount across X number of brokers on buy or sell side
function splitCommission(fieldRef,brokerType) {
    if($(fieldRef).val() == "") {
		alert("a");
		return;
	}
	
	if( brokerType == "buy" && !$("#evenSplitBuy").attr('checked') ) {
		return;
	}
	else if( brokerType == "sell" && !$("#evenSplitSell").attr('checked') ) {
		return;
	}
	
	var baseRef = brokerType+"BrokerRate"; //build var name
	var firstLetter = brokerType.charAt(0).toUpperCase(); //uppercase letter
    var addBrokerRef = "more"+firstLetter + brokerType.substr(1)+"Brokers"; //base field ref variable
	var additionalItems = $("#"+addBrokerRef).find("table").length; //get additional broker count
	var totalQty = $("#totalQty").val(); //total qty
	
	if(brokerType == "buy") {
		var buyTotal = $("#buyCommTotal").val();
		buyBrokerCount = $("#moreBuyBrokers").find("table").length;
		buyBrokerCount = buyBrokerCount + 1;
		splitCommVal = (buyTotal / totalQty) / buyBrokerCount; //calc new ate
	}
	else if(brokerType == "sell") {
		var sellTotal= $("#sellCommTotal").val();
		sellBrokerCount = $("#moreSellBrokers").find("table").length;
		sellBrokerCount = sellBrokerCount + 1;
		splitCommVal = (sellTotal / totalQty)  / sellBrokerCount; //calc new rate
	}
	
	//var totalComm = fieldRef.value; //total buy/sell commission
	//var totalComm = parseFloat(buyTotal) + parseFloat(sellTotal); //total commission across buyer & seller

	//if(additionalItems > 0 ){
		if(brokerType == "buy") {
			$("#buyBrokerRate").val( splitCommVal ); //set value of first buy broker
			updateCommission("buy",'');
			
			//buy broker loop
			for(var i=1; i<(1+buyBrokerCount); i++){
				try {
					$("#buyBrokerRate"+i).val( splitCommVal ); //set commisssion percent for each subsequent broker
					updateCommission("buy",i);
				} catch(e) { }
			}
		}
		else if(brokerType == "sell") {
			$("#sellBrokerRate").val( splitCommVal ); //set value of first sell broker
			updateCommission("sell",'');
			
			//sell broker loop
			for(var i=1; i<(1+sellBrokerCount); i++){
				try {
					$("#sellBrokerRate"+i).val( splitCommVal ); //set commisssion percent for each subsequent broker
					updateCommission("sell",i);
				} catch(e) { }
			}
		}		
	/*}
	else {
		$("#buyBrokerRate").val( splitCommVal ); //set comm rate for single buy broker
		$("#sellBrokerRate").val( splitCommVal ); //set comm rate for single sell broker
		//updateCommission(brokerType,'');
		updateCommission("buy",'');
		updateCommission("sell",'');
	}*/
}

function splitEven(traderSide) {

	if(traderSide == "buy") {
		splitCommission($("#buyTotalComm"),traderSide);
	}
	else if(traderSide == "sell") {
		splitCommission($("#sellTotalComm"),traderSide);
	}
		
}


//validate that all broker commissions do not exceed Total Commission for buy+sell
function verifyManualCommission(fieldRef) {

    var buyTotal = $("#buyCommTotal").val();
	var sellTotal= $("#sellCommTotal").val();
    var totalComm = parseFloat(buyTotal) + parseFloat(sellTotal); //total commission across buyer & seller
    buyBrokerCount = $("#moreBuyBrokers").find("table").length;
	sellBrokerCount = $("#moreSellBrokers").find("table").length;
  	var customTotal = 0;

    customTotal += parseFloat($("#buyBrokerTotal").val());
    customTotal += parseFloat($("#sellBrokerTotal").val());
	
	//buy broker loop
    for(var i=1; i<(1+buyBrokerCount); i++){
    	try {
    		customTotal += parseFloat($("#buyBrokerTotal"+i).val());
    	} catch(e) { }
    }
    //sell broker loop
    for(var i=1; i<(1+sellBrokerCount); i++){
    	try {
    		customTotal += parseFloat($("#sellBrokerTotal"+i).val());
    	} catch(e) { }
    }
	
	if(customTotal > totalComm) {
		$(fieldRef).addClass("fieldError");
		alert("This value exceeds the total commission for the trade.");
	}
	else {
    	$("#buyBrokerTotal").removeClass();
		$("#sellBrokerTotal").removeClass();
		for(var i=1; i<(1+buyBrokerCount); i++)
    		$("#buyBrokerTotal"+i).removeClass();
		for(var i=1; i<(1+sellBrokerCount); i++)
    		$("#sellBrokerTotal"+i).removeClass();
		//$(fieldRef).removeClass();
	}
	
}

//update buy/sell side commission total field automatically (two different totals)
function verifyManualCommission2(fieldRef) {
		
	//var buyTotal = $("#buyCommTotal").val();
	//var sellTotal= $("#sellCommTotal").val();
    //var totalComm = parseFloat(buyTotal) + parseFloat(sellTotal); //total commission across buyer & seller
    
  	var buyTotal = 0;
	var sellTotal = 0;
	buyBrokerArr = $("#moreBuyBrokers").find("table");
	sellBrokerArr = $("#moreSellBrokers").find("table");
	buyBrokerCount = buyBrokerArr.length;
	sellBrokerCount = sellBrokerArr.length;

    buyTotal += parseFloat($("#buyBrokerTotal").val());
    sellTotal += parseFloat($("#sellBrokerTotal").val());

	//buy broker loop
    for(var i=0; i<buyBrokerCount; i++){
    	try {
    		//buyTotal += parseFloat($("#buyBrokerTotal"+i).val());
			fieldRef = $(buyBrokerArr[i]).find("input");
			buyTotal += parseFloat($(fieldRef[2]).val());
    	} catch(e) { /*alert("error buy: "+i);*/ }
    }
    //sell broker loop
    for(var i=0; i<sellBrokerCount; i++){
    	try {
    		//sellTotal += parseFloat($("#sellBrokerTotal"+i).val());
			fieldRef2 = $(sellBrokerArr[i]).find("input");
			sellTotal += parseFloat($(fieldRef2[2]).val());
    	} catch(e) { /*alert("error sell: "+i);*/ }
    }
	
	$("#buyCommTotal").val(CurrencyFormatted(buyTotal));
	$("#sellCommTotal").val(CurrencyFormatted(sellTotal));
		
}