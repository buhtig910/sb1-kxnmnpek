<?php

require("../vars.inc.php");
require("confirmObj.php");

$clientID= "Calpine Energy Services";
$regionID= "WEST";
$productTypeID=2;
$locationID=151; /* 151-154 */

$confirm = new Confirm();
$confirm->setRegion($regionID);
$confirm->setProductType($productTypeID);
$confirm->setLocationID($locationID);

$valid = $confirm->init($clientID);

if($valid) {
?>

<p>Show Buyer Currency: <?php echo($confirm->showBuyerCurrency()); ?></p>

<p>Show Seller Price: <?php echo($confirm->showSellerPrice()); ?></p>

<p>Seller Price Text: <?php echo($confirm->getSellerPriceText()); ?></p>

<p>Show Delivery: <?php echo($confirm->showDelivery()); ?></p>

<p>Show Product: <?php echo($confirm->showProduct()); ?></p>

<p>Show Region: <?php echo($confirm->showRegion()); ?></p>

<p>Product Type Text: <?php echo($confirm->getProductTypeText()); ?></p>

<?php
}
else {
	echo("No match found");	
}

?>