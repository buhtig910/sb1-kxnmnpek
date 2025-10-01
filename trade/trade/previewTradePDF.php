<?php

@session_start();

// Enable error reporting temporarily for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require("../vars.inc.php");
require("../functions.php");

if( @validateSecurity() ) {
	
	try {
		// First, let's test if we can even create a basic PDF
		echo "Testing PDF generation...<br>";
		
		// Check if FPDF exists
		if (!file_exists("../fpdf/fpdf.php")) {
			throw new Exception("FPDF library not found");
		}
		echo "FPDF library found<br>";
		
		// Try to include FPDF
		require_once("../fpdf/fpdf.php");
		echo "FPDF included successfully<br>";
		
		// Try to create a PDF object
		$pdf = new FPDF();
		echo "FPDF object created<br>";
		
		// Add a page
		$pdf->AddPage();
		echo "Page added<br>";
		
		// Set font
		$pdf->SetFont("Arial","B",16);
		echo "Font set<br>";
		
		// Add some text
		$pdf->Cell(0,10,"Test PDF - Working!",0,1,"C");
		echo "Text added<br>";
		
		// Set headers for PDF output
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="test.pdf"');
		echo "Headers set<br>";
		
		// Output the PDF
		$pdf->Output('I', 'test.pdf');
		echo "PDF output called<br>";
		
	} catch (Exception $e) {
		echo "<h2>PDF Generation Error</h2>";
		echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
		echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
		echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
	} catch (Error $e) {
		echo "<h2>PDF Generation Error</h2>";
		echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
		echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
		echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
	}
} else {
	echo "<h2>Security Error</h2>";
	echo "<p>You do not have permission to view this page.</p>";
}

?>
