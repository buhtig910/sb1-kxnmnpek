<?php
echo "PHP is working!<br>";
echo "Current directory: " . getcwd() . "<br>";
echo "File exists: " . (file_exists('test_pdf_generation.php') ? 'YES' : 'NO') . "<br>";
echo "Server time: " . date('Y-m-d H:i:s') . "<br>";
?>
