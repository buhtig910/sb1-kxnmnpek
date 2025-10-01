<?php

require("../vars.inc.php");

$query = "SELECT DISTINCT fkCompanyID,fldCompanyName,fldEmail,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldCountry,fldPhone,fldFax,fldInvoiceName FROM tblB2TClient WHERE fldActive=1 ORDER BY fldCompanyName ASC";
//echo($query);
$result = mysqli_query($_SESSION['db'],$query);
@$count = mysqli_num_rows($result);

$totalUpdate = 0;
if($count > 0) {
	
	while($resultSet = mysqli_fetch_row($result)){
		
		if($resultSet[1] != "") { //ignore blank company names
			/*
			0 = fkCompanyID
			1 = company name
			2 = email
			3 = street1
			4 = street2
			5 = city
			6 = state
			7 = zip
			8 = country
			9 = phone
			10= fax
			11=invoice name
			*/
			
			$queryUpdate = "UPDATE tblB2TCompany SET fldInvoiceEmail=\"".$resultSet[2]."\",fldStreet1=\"".$resultSet[3]."\",fldStreet2=\"".$resultSet[4]."\",
				fldCity=\"".$resultSet[5]."\",fldState=\"".$resultSet[6]."\",fldZip=\"".$resultSet[7]."\",fldCountry=\"".$resultSet[8]."\",
				fldPhone=\"".$resultSet[9]."\",fldFax=\"".$resultSet[9]."\",fldInvoiceName=\"".$resultSet[11]."\" WHERE pkCompanyID=".$resultSet[0];
			//echo("<p>".$queryUpdate."</p>");
			$resultUpdate = mysqli_query($_SESSION['db'],$queryUpdate);
			@$countUpdate = mysqli_affected_rows();
			
			if($countUpdate == 1)
				$totalUpdate++;
		}
		
	}
	
	echo("<p>Found <strong>$count</strong> unique company names in Client table, updated <strong>$totalUpdate</strong> Company records in the database");

}

?>
