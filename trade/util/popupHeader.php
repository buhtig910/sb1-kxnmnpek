<?php
@session_start();

require_once("../functions.php");
require_once("../vars.inc.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<link rel="icon" href="../images/b2t_icon.ico" type="image/ico" />
<link rel="shortcut icon" href="../images/b2t_icon.ico" type="image/ico" />

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title>Greenlight Commodities</title>

<?php
if($_COOKIE['viewPref']=="true")
	echo("<link href=\"/css/styles-dark.css\" rel=\"stylesheet\" type=\"text/css\" id=\"style-link\"/>");
else
	echo("<link href=\"/css/styles.css\" rel=\"stylesheet\" type=\"text/css\" id=\"style-link\" />");
?>

<!--[if lte IE 7]>
<link href="../css/styles_ie.css" rel="stylesheet" type="text/css"/>
<![endif]-->
<script language="javascript" type="text/javascript" src="../scripts/scripts.js"></script>
<script language="javascript" type="text/javascript" src="../scripts/jquery.js"></script>



</head>

<body class="popup">

<!-- START OVERALL PAGE WRAPPER -->
<div id="pageContainerPopup">

	<!-- START PAGE HEADER -->
	<div id="pageHeaderPopup">
		<!--<img src="../images/bosworth_logo_popup.jpg" alt="Greenlight Commodities" title="" width="252" height="39" border="0" align="left" />-->
        <p class="logo">Greenlight Commodities</p>
    </div>
	<!-- END PAGE HEADER -->

	<div id="mainContentWrapperPopup">
		<div id="mainContent" class="mainContentPop">

