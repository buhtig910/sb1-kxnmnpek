<?php

require("simple_html_dom.php");


class Scrape {

	private $pageURL;
	private $typeVal;
	private $insertCount = 0;
	private $insertCount2 = 0;
	
	
	/**********************
	* set scrape page URL
	**********************/
	public function setURL($url) {
		$this->pageURL = $url;
	}
	
	/*********************
	* set report type
	*********************/
	public function setType($typeVal) {
		$this->typeVal = $typeVal;
	}
	
	public function getRowCount() {
		return $this->insertCount;	//ice insert row count
	}
	
	public function getRowCount2() {
		return $this->insertCount2; //nymex insert row count
	}

	/*********************
	* pull the page DOM and parse it into the DB
	*********************/
	public function getPage() {
		
		$html = file_get_html($this->pageURL);

		if($html->find('.default',1)) { //updated from #priceTable on 5.3.12
			$contentArr = array(); //get all content in arr to generate email report
			
			foreach($html->find('.default') as $parentTable) { //get the parent table
				foreach($parentTable->find('tr') as $row) { //get each row
					$i = 0;
					$query = "INSERT INTO tblB2TScrape_NA VALUES (";
	
					$rowArr = array(); //get row data to store in content arr
					
					foreach($row->find('td') as $cell) { //get each cell
						$contents = $cell->plaintext;

						if(strpos($contents, "00:00:00") > 0) {
							//$contents = str_replace("00:00:00","",$contents);
							//$contents = preg_replace("/(EDT)(EST)/","",$contents);
							$contents = date("Y-m-d",strtotime($contents));
						}
						
						if($i>=1)
							$query.= ",";
							
						$contents = trim($contents);
						$query.= "\"".$contents."\"";
						array_push($rowArr, $contents); //add content to row arr
						
						$i++;
					}
					$query.= ",\"".$this->getType()."\")";
					array_push($rowArr, $this->getType()); //add content to row arr
					
					if($i >= 1) { //skip first table row
						//echo($query."<br />");
						$result = mysqli_query($_SESSION['db'],$query);
						@$count = mysqli_affected_rows();
						if($count == 1)
							$this->insertCount++;
						else {
							//echo("failed");
						}
					}
					array_push($contentArr,$rowArr);
				}
			}
			
			return $contentArr;
		}
		
		// clean up memory
		$html->clear();
		unset($html);
	}
	
	
	/************************
	* getNYMEXFile
	************************/
	public function getNYMEXFile() {
		$handle = fopen($this->pageURL,"r");
		$loop = 0;
		while(($data = fgetcsv($handle, 800, ",")) !== FALSE) {
			$num = count($data);
			//echo "<p> $num fields in line $row: <br /></p>\n";
			//$row++;
			
			if($loop > 0) {
				$insertString = "";
				for ($i=0; $i < $num; $i++) {
					if($i < ($num - 1))
						$insertString.= "\"".$data[$i]."\",";
					else
						$insertString.= "\"".reverseDate($data[$i])."\"";
						
					if($this->typeVal == "Futures" && $i == 3)
						$insertString.= "\"\",\"\","; //empty cells
				}
				
				$query = "INSERT INTO tblB2TScrape_NYMEX VALUES (\"\",$insertString,\"".$this->typeVal."\")";
				//echo($query."<br>");
				$result = mysqli_query($_SESSION['db'],$query);
				@$count = mysqli_affected_rows();
				if($count == 1)
					$this->insertCount2++;
			}
			
			$loop++;
		}		
		fclose($handle);		
	}
	
	
	/*******************
	* parseRows
	*
	* parse array rows built above to create email report
	*******************/
	public function parseRows($powerArr,$cellStyleOdd,$cellStyleEven) {
		for($i=0; $i<count($powerArr); $i++) {
			$bodyContent.= "<tr>";
			for($j=0; $j<count($powerArr[$i]); $j++) {
				if($i % 2 == 0)
					$rowStyle = $cellStyleEven;
				else
					$rowStyle = $cellStyleOdd;
				
				if(count($powerArr[$i]) == 1) {
					$colSpan=12;
					$cellContent = "<strong>".$powerArr[$i][$j]."</strong>";
				}
				else {
					$colSpan="";
					$cellContent = $powerArr[$i][$j];
				}

				$bodyContent.= "<td $rowStyle colspan=\"$colSpan\">".$cellContent."</td>";
			}
			$bodyContent.= "</tr>";
		}
		
		return $bodyContent;
	}
	
	
	/**** private memebrs ****/
	
	private function getType() {
		return $this->typeVal;	
	}
	
}

?>