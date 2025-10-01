<?php
// Test PDF generation to debug formatting issues
session_start();
require_once('util/genPDF.php');

// Test data - simulate a trade record
$testTradeData = array(
    0 => '1', // pkTradeID
    1 => 'Test Trade', // fldTradeName
    2 => '2025-01-15', // fldTradeDate
    3 => '1000', // fldQuantity
    4 => 'USD', // fldCurrency
    5 => '1', // fldBuyerID (ICE CLEARED)
    6 => '2', // fldSellerID (Regular company)
    7 => '50.00', // fldPrice
    8 => 'Henry Hub', // fldLocation
    9 => 'Natural Gas', // fldCommodity
    10 => 'NYMEX', // fldRegion
    11 => '2025-02-15', // fldDeliveryDate
    12 => '1', // fldUnit
    13 => '1', // fldUnitFrequency
    14 => '0', // fldOptionStyle
    15 => '0', // fldOptionType
    16 => '0', // fldExerciseType
    17 => '0', // fldStrike
    18 => '0', // fldStrikeCurrency
    19 => '0', // fldOffsetIndex
    20 => '0', // fldDeliveryType
    21 => '0', // fldBlock
    22 => '0', // fldHours
    23 => '0', // fldTimeZone
    24 => '0', // fldIndexPrice
    25 => '0', // fldSellerPrice
    26 => '0', // fldSellerCurrency
    27 => 'Test Comments', // fldComments
    28 => '2025-01-15 10:00:00', // fldCreated
    29 => '1', // fldCreatedBy
    30 => '0', // fldStatus
    31 => '0', // fldConfirmed
    32 => '0', // fldConfirmedBy
    33 => '0', // fldConfirmedDate
    34 => '0', // fldCancelled
    35 => '0', // fldCancelledBy
    36 => '0', // fldCancelledDate
    37 => '0', // fldModified
    38 => '0', // fldModifiedBy
    39 => '0', // fldModifiedDate
    40 => '0', // fldDeleted
    41 => '0', // fldDeletedBy
    42 => '0', // fldDeletedDate
    43 => '0', // fldArchived
    44 => 'ICE123456' // fldExchangeID
);

// Mock database connection
$_SESSION['db'] = null;

// Test the PDF generation
try {
    $pdf = generatePDF($testTradeData);
    
    if ($pdf) {
        // Save to temp directory
        $filename = 'test_trade_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = 'temp/' . $filename;
        
        // Ensure temp directory exists
        if (!is_dir('temp')) {
            mkdir('temp', 0755, true);
        }
        
        file_put_contents($filepath, $pdf);
        echo "PDF generated successfully: " . $filepath . "\n";
        echo "File size: " . filesize($filepath) . " bytes\n";
    } else {
        echo "PDF generation failed\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
