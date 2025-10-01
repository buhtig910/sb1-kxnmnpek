<?php
// Script to create Natural Gas Simple Trade Type
// This creates a copy of trade type 6 but without option fields

require_once('../vars.inc.php');

echo "Creating Natural Gas Simple Trade Type...\n\n";

// 1. Check current trade types
echo "Current Trade Types:\n";
$query = "SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits FROM tblB2TTradeType ORDER BY pkTypeID";
$result = mysqli_query($_SESSION['db'], $query);
while($row = mysqli_fetch_assoc($result)) {
    echo "ID: {$row['pkTypeID']}, Name: {$row['fldTradeName']}, Group: {$row['fldProductGroup']}, Units: {$row['fldUnits']}\n";
}

// 2. Get next available ID
$query = "SELECT COALESCE(MAX(pkTypeID) + 1, 1) as nextID FROM tblB2TTradeType";
$result = mysqli_query($_SESSION['db'], $query);
$row = mysqli_fetch_assoc($result);
$nextID = $row['nextID'];
echo "\nNext available trade type ID: $nextID\n";

// 3. Insert new trade type
$query = "INSERT INTO tblB2TTradeType (pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID) VALUES ($nextID, 'Natural Gas Simple', 'Natural Gas', 'MMBTU', 0, 0, 0)";
$result = mysqli_query($_SESSION['db'], $query);
if($result) {
    echo "✓ Trade type created successfully with ID: $nextID\n";
} else {
    echo "✗ Error creating trade type: " . mysqli_error($_SESSION['db']) . "\n";
    exit;
}

// 4. Create template for the new trade type
$templateQuery = "INSERT INTO tblB2TTemplates (
    fldTemplateName,
    fldOffsetIndex,
    fldDeliveryType,
    fldBuyerPrice,
    fldBuyerCurrency,
    fldSellerPrice,
    fldSellerCurrency,
    fldOptionStyle,
    fldOptionType,
    fldExerciseType,
    fldStrike,
    fldStrikeCurrency,
    fldBlock,
    fldHours,
    fldTimeZone,
    fldUnitFrequency,
    fldIndexPrice,
    fldCustomFields,
    fkTypeID
) VALUES (
    'Natural Gas Simple Template',
    1,  -- Show offset index
    1,  -- Show delivery
    1,  -- Show buyer price
    1,  -- Show buyer currency
    1,  -- Show seller price
    1,  -- Show seller currency
    0,  -- Hide option style
    0,  -- Hide option type
    0,  -- Hide exercise type
    0,  -- Hide strike price
    0,  -- Hide strike currency
    1,  -- Show block
    1,  -- Show hours
    1,  -- Show timezone
    1,  -- Show unit frequency
    1,  -- Show index price
    0,  -- No custom fields
    $nextID
)";

$templateResult = mysqli_query($_SESSION['db'], $templateQuery);
if($templateResult) {
    echo "✓ Template created successfully\n";
} else {
    echo "✗ Error creating template: " . mysqli_error($_SESSION['db']) . "\n";
}

// 5. Verify the new trade type
echo "\nVerification:\n";
$query = "SELECT * FROM tblB2TTradeType WHERE pkTypeID = $nextID";
$result = mysqli_query($_SESSION['db'], $query);
$row = mysqli_fetch_assoc($result);
echo "New Trade Type: " . print_r($row, true) . "\n";

$query = "SELECT * FROM tblB2TTemplates WHERE fkTypeID = $nextID";
$result = mysqli_query($_SESSION['db'], $query);
$row = mysqli_fetch_assoc($result);
echo "New Template: " . print_r($row, true) . "\n";

echo "\n✅ Natural Gas Simple Trade Type created successfully!\n";
echo "You can now access it at: http://trade.greenlightcommodities.com/trade/tradeInput.php?tradeType=$nextID\n";
?>
