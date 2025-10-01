<?php

if($action == "uploadLocationList"){
	//parse static location lookup list
	if($_FILES['myFile']){	
		
		$file_name = $_FILES['myFile']['name'];
		$newLocation = "../temp/".$file_name;
		//echo($newLocation);
		$copied = copy($_FILES['myFile']['tmp_name'], $newLocation);
		if (!$copied){
			echo("<p class=\"error\">Unable to upload file.</p>\n");
			$errors=1;
		}
		else {
			$handle = fopen($newLocation, "rb");
			
			if($handle){	
				require("../vars.inc.php");
				
				//use this block to replace all values, comment out to append
				$deleteQuery = "TRUNCATE TABLE $tblLocation";
				//echo($deleteQuery);
				$result = mysqli_query($_SESSION['db'],$deleteQuery);
				
				$contents = "";
				while (!feof($handle)) {
					$contents .= fread($handle, 8192);
				}
				fclose($handle);
				
				$locationSet = explode("\n",$contents);
								
				$updated = 0;
				
				for($i = 0; $i<sizeOf($locationSet)-1; $i++){
					$locationDetail = explode("\t",$locationSet[$i]);
					/*
					0 = location name
					1 = unit
					2 = currency
					3 = prod group
					4 = global region
					5 = geo region
					6 = geo sub-region
					7 = region
					8 = timezone
					9 = hour template
					10 = status
					*/
					
					/*$active = 0;
					echo("<p>$locationDetail[10]</p>\n");
					if($locationDetail[10] == "Live"){
						$active = 1;
					}*/
					
					$locName = preg_replace("[\"]","",$locationDetail[0]);
					
					$updateQuery = "INSERT INTO $tblLocation VALUES('',\"$locName\",\"$locationDetail[1]\",\"$locationDetail[2]\"".
									",\"$locationDetail[3]\",\"$locationDetail[4]\",\"$locationDetail[5]\",\"$locationDetail[6]\",\"$locationDetail[7]\"".
									",\"$locationDetail[8]\",\"$locationDetail[9]\",\"1\")";
					//echo($updateQuery."\n");
					$updateResult = mysqli_query($_SESSION['db'],$updateQuery);
					if(mysqli_affected_rows() == 1) {
						$updated++;
					}
					else {
						//echo("Record $i failed to upload<br />");
						//echo($updateQuery."<br />");
					}
				}
				echo("<p>Upload $i rows read successful ($updated new records added to database)</p>\n");
			}
		}
	}
	else {
		echo("<p class=\"error\">File not found</p>\n");
	}
}
else if($action == "uploadHoursTable"){
	// parse static hours lookup table
	if($_FILES['myFile']){	
		
		$file_name = $_FILES['myFile']['name'];
		$newLocation = "../temp/".$file_name;
		//echo($newLocation);
		$copied = copy($_FILES['myFile']['tmp_name'], $newLocation);
		if (!$copied){
			echo("<p class=\"error\">Unable to upload file.</p>\n");
			$errors=1;
		}
		else {
			$handle = fopen($newLocation, "rb");
			
			if($handle){	
				require("../vars.inc.php");
				$contents = "";
				while (!feof($handle)) {
					$contents .= fread($handle, 8192);
				}
				fclose($handle);
				
				$locationSet = explode("\n",$contents);
				
				//$deleteQuery = "DELETE * FROM $tblLocation";
				//$result = mysqli_query($_SESSION['db'],$deleteQuery);

				for($i = 0; $i<sizeOf($locationSet)-1; $i++){
					$locationDetail = explode(",",$locationSet[$i]);
					/*
					each row has 70 data points, skip 24,61,62,63,64,65,66,67,68,69,70,71,72,73
					*/
					$partialQuery = "";
					$ignoreKeys = array(23,61,62,63,64,65,66,67,68,69,70,71,72,73);
					for($j = 0; $j<count($locationDetail); $j++){
						if(!(in_array($j,$ignoreKeys))){
							$partialQuery .= ",\"".$locationDetail[$j]."\"";
						}
					}
					
					$updateQuery = "INSERT INTO $tblHours VALUES(''".$partialQuery.")";
					//echo($updateQuery."<br>");
					$updateResult = mysqli_query($_SESSION['db'],$updateQuery);
				}
				echo("<p>Upload successful</p>\n");
			}
		}
	}
	else {
		echo("<p class=\"error\">File not found</p>\n");
	}
}else if($action == "uploadClientTable"){
	// parse client table
	if($_FILES['myFile']){	
		
		$file_name = $_FILES['myFile']['name'];
		$newLocation = "../temp/".$file_name;
		//echo($newLocation);
		$copied = copy($_FILES['myFile']['tmp_name'], $newLocation);
		if (!$copied){
			echo("<p class=\"error\">Unable to upload file.</p>\n");
			$errors=1;
		}
		else {
			$handle = fopen($newLocation, "rb");
			
			if($handle){	
				require("../vars.inc.php");
				$contents = "";
				while (!feof($handle)) {
					$contents .= fread($handle, 8192);
				}
				fclose($handle);
				
				$clientSet = explode("\n",$contents);
				
				//$deleteQuery = "DELETE * FROM $tblLocation";
				//$result = mysqli_query($_SESSION['db'],$deleteQuery);
				
				for($i = 0; $i<sizeOf($clientSet)-1; $i++){
					$clientDetail = explode("\t",$clientSet[$i]);
					/*
					0 = company name
					1 = short name
					2 = active
					3 = street address
					4 = street address 2
					5 = city
					6 = state
					7 = zip
					8 = invoice contact (first name)
					9 = invoice contact (last name)
					10 = invoice contact email
					11 = invoice contact tel
					12 = invoice contact fax
					13 = confirm first name
					14 = confirm last name
					15 = confirm email
					16 = confirm tel
					17 = confirm fax
					*/
					
					if($clientDetail[2] == "Y")
						$active = 1;
					else
						$active = 0;
					
					$updateQuery = "INSERT INTO tblB2TClient VALUES(\"\",\"$traderName\",\"$clientDetail[8] $clientDetail[9]\",\"$clientDetail[10]\"";
					$updateQuery.= ",\"$clientDetail[0]\",\"$clientDetail[3]\",\"$clientDetail[4]\",\"$clientDetail[5]\",\"$clientDetail[6]\",\"$clientDetail[7]\"";
					$updateQuery.= ",\"\",\"$clientDetail[11]\",\"$clientDetail[12]\",\"$clientDetail[15]\",\"$clientDetail[13] $clientDetail[14]\"";
					$updateQuery.= ",\"$confirmStreet\",\"$confirmStreet2\",\"$confirmCity\",\"$confirmState\",\"$confirmZip\",\"$confirmCountry\"";
					$updateQuery.= ",\"$clientDetail[16]\",\"$clientDetail[17]\",\"$active\",\"$clientDetail[1]\")";
					//$updateQuery = ereg_replace("\"\"","\"",$updateQuery);
					//echo($updateQuery."<br><br>");
					$updateResult = mysqli_query($_SESSION['db'],$updateQuery);
				}
				if($updateResult)
					echo("<p>Upload successful</p>\n");
				else
					echo("<p>Upload UNSUCCESSFUL</p>\n");
			}
		}
	}
	else {
		echo("<p class=\"error\">File not found</p>\n");
	}
}
?>


<form name="uploadData" action="" method="post" enctype="multipart/form-data">
<h2>Upload Location List</h2>
<p>Input a tab-delimited txt file for upload. Be sure to remove the column header row and first column (ID) before uploading.</p>
<table>
	<tr>
		<td>File:</td>
		<td><input type="file" name="myFile" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Upload Data" />
			<input type="hidden" name="action" value="uploadLocationList" />
		</td>
	</tr>
</table>
</form>

<form name="uploadData2" action="" method="post" enctype="multipart/form-data">
<h2>Upload Hours Table</h2>
<table>
	<tr>
		<td>File:</td>
		<td><input type="file" name="myFile" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Upload Data" />
			<input type="hidden" name="action" value="uploadHoursTable" />
		</td>
	</tr>
</table>
</form>

<form name="uploadData3" action="" method="post" enctype="multipart/form-data">
<h2>Upload Client Table</h2>
<strong>WARNING: This will lose all existing connections between client ID and trades!</strong>

<table>
	<tr>
		<td>File:</td>
		<td><input type="file" name="myFile" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Upload Data" />
			<input type="hidden" name="action" value="uploadClientTable" />
		</td>
	</tr>
</table>
</form>
