#!/usr/local/bin/php.cli

<?php

require("scrapeObj.php");
require("../vars.inc.php");
require("../functions.php");


/********************************
* NYMEX Data Scrape
*********************************/
//NYMEX Futures
$baseURL3 = "ftp://ftp.cmegroup.com/pub/settle/nymex_future.csv";
//NYMEX Options
$baseURL4 = "ftp://ftp.cmegroup.com/pub/settle/nymex_option.csv";

//create scrape obj
$scraper3 = new Scrape();
$scraper4 = new Scrape();

//NYMEX Futures
$scraper3->setURL($baseURL3);
$scraper3->setType("Futures");
$powerArr = $scraper3->getNYMEXFile();

//NYMEX Options
$scraper4->setURL($baseURL4);
$scraper4->setType("Options");
$gasArr = $scraper4->getNYMEXFile();

echo("NYMEX Futures Rows Inserted: ".$scraper3->getRowCount2()."<br>");
echo("NYMEX Options Rows Inserted: ".$scraper4->getRowCount2()."<br>");

?>