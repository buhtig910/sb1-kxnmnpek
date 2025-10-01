<?php	
session_start();

require_once("functions.php");
require_once("vars.inc.php");

// Check if user is logged in and needs to change password on first login
// Only redirect if this is actually a first-time user (fldFirstLogin = 1)
if(isset($_SESSION["loggedIn42"]) && $_SESSION["loggedIn42"] == "true1" && isset($_SESSION["firstLogin"]) && $_SESSION["firstLogin"] == 1) {
	// This is a first-time user who needs to change their temporary password
	header("Location: change_password.php");
	exit;
}

// Process login form submission
if($_POST["action"] == "login" && !empty($_POST["username"]) && !empty($_POST["password"])) {
	$username = $_POST["username"];
	$password = $_POST["password"];
	
	$loginResult = login($username, $password);
	if($loginResult === true) {
		// Debug: Check session values
		error_log("Login successful for user: " . $username . ", firstLogin: " . (isset($_SESSION["firstLogin"]) ? $_SESSION["firstLogin"] : "not set"));
		
		// Login successful, check if this is a first login
		if(isset($_SESSION["firstLogin"]) && $_SESSION["firstLogin"] == 1) {
			// This is a first-time user, redirect to password change page
			error_log("Redirecting to password change page for first-time user: " . $username);
			header("Location: change_password.php");
			exit;
		} else {
			// Regular user, redirect to refresh the page
			error_log("Regular user login, redirecting to: " . $_SERVER['REQUEST_URI']);
			header("Location: " . $_SERVER['REQUEST_URI']);
			exit;
		}
	} else {
		// Login failed, store error message
		$loginError = $loginResult;
	}
}

//redirect from non-www to www
/*$haystack = $_SERVER["HTTP_HOST"];
$needle = "www.";
$found = stristr($haystack,$needle);

if($found) {
	$newURL = "http://www.".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
	header("Location: ".$newURL);
}*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<!--<link rel="icon" href="images/b2t_icon.ico" type="image/ico" />
<link rel="shortcut icon" href="images/b2t_icon.ico" type="image/ico" />-->

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="google-site-verification" content="MrKSZ5ZLj2JnFHymepTRlQ36_R8sqpLhgRQCK0b9AQc" />

<?php

if($_GET["p"] == null)
	$p = "home";
else
	$p = $_GET["p"];

if($_GET["action"] == "logout") {
	session_destroy();
}

$query = "SELECT * FROM ".$tblMetaData." WHERE fldPage=\"".$p."\"";
//echo($query);
$result = mysqli_query($_SESSION['db'],$query);

@$metaData = mysqli_fetch_row($result);
/* 	
0 = page
1 = title
2 = description
3 = keywords
*/
?>
<title><?php echo($metaData[1]); ?> | GreenlightCommodities.com</title>

<meta name="keywords" content="<?php echo($metaData[3]); ?>" />
<meta name="description" content="<?php echo($metaData[2]); ?>" />

<?php
if($_COOKIE['viewPref']=="true")
	echo("<link href=\"css/styles-dark.css\" rel=\"stylesheet\" type=\"text/css\" id=\"style-link\"/>");
else
	echo("<link href=\"css/styles.css\" rel=\"stylesheet\" type=\"text/css\" id=\"style-link\" />");
?>

<link href="css/switch.css" rel="stylesheet" type="text/css" />
<link href="css/calendar.css" rel="stylesheet" type="text/css" />

<!-- Fix calendar z-index issues -->
<style>
/* Ensure form elements are above calendar popups */
.fieldGroup, .groupContent, input, select, label {
    position: relative;
    z-index: 10;
}

/* Calendar popups should be below form elements */
.yui-calcontainer {
    z-index: 5 !important;
}

.yui-calendar {
    z-index: 5 !important;
}

/* Ensure login fields are visible */
#loginFields {
    position: relative;
    z-index: 15;
}

/* Fix field group layout */
.fieldGroup {
    position: relative;
    overflow: visible;
}

.groupContent {
    position: relative;
    z-index: 10;
}
</style>


<!--[if lte IE 7]>
<link href="css/styles_ie.css" rel="stylesheet" type="text/css"/>
<![endif]-->

<script language="javascript" type="text/javascript" src="scripts/jquery.js"></script>

<script type="text/javascript">
let resizeTimeout;

function resizeScrollable() {
  const el = document.querySelector('.scrollable');
  if (!el) return;

  const topOffset = el.getBoundingClientRect().top;
  const bottomPadding = 100; // increased to account for footer height and spacing
  el.style.height = (window.innerHeight - topOffset - bottomPadding) + "px";
}

function debouncedResize() {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(resizeScrollable, 100);
}

// Run on window resize with debouncing
window.addEventListener('resize', debouncedResize);

// Run after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  resizeScrollable();
});

// Run after a short delay to ensure all content is loaded
setTimeout(function() {
  resizeScrollable();
}, 200);

// Run after images and other resources are loaded
window.addEventListener('load', function() {
  setTimeout(function() {
    resizeScrollable();
  }, 300);
});
</script>

<?php if(validateSecurity()) { ?>
<link href="css/colorbox.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="javascript" type="text/javascript" src="scripts/cookie.js"></script>
<script language="javascript" type="text/javascript" src="scripts/jquery.colorbox-min.js"></script>
<?php } ?>

<?php
if($_GET["action"] == "logout")
	echo("<script language=\"javascript\"> $.cookie(\"windowCt\",0, {path: '/'} ); </script>");
?>


</head>

<body>

<!-- START OVERALL PAGE WRAPPER -->
<div id="pageContainer">

	<!-- START PAGE HEADER -->
	<div id="pageHeader">
		<!--<a href="index.php"><img src="images/bosworth_logo.jpg" alt="Greenlight Commodities" title="" width="438" height="119" border="0" align="left" /></a>-->
		<a href="index.php" class="logo">Greenlight Commodities</a>
		<div id="loginFields">
			<?php 
			
			// Temporarily show login fields for layout testing
			if(@validateSecurity()){
				require("util/login.php");
				
				?>
                	<!-- Rounded switch -->
                    <br />
                    <p style="vertical-align: middle;"><strong>Dark mode:</strong>
                    <label class="switch">
                      <input type="checkbox" onclick="toggleMode(this);" <?php if($_COOKIE['viewPref']=="true") echo("checked=checked"); ?>>
                      <span class="slider round"></span>
                    </label></p>
                <?php
				 
			} else {
				// Show login form when not logged in
				?>
				<div class="login-form">
					<form name="loginForm" action="" method="post">
						<table border="0" cellspacing="3" cellpadding="0">
							<tr>
								<td>Username:</td>
								<td><input name="username" id="username" type="text" size="15" /></td>
							</tr>
							<tr>
								<td>Password: </td>
								<td><input name="password" type="password" size="15" /></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><input name="login" type="submit" id="login" value="Login" class="buttonSmall" />
									<input type="hidden" name="action" value="login" />
								</td>
							</tr>
						</table>
					</form>
				</div>
				<?php
			}
			
			?>
		</div>
		
		<!-- Dark mode toggle - moved outside loginFields so it's always visible -->
		<div id="darkModeToggle" style="float: right; padding: 5px 10px 0 0;">
			<?php if(@validateSecurity()) { ?>
				<!-- Rounded switch -->
				<p style="vertical-align: middle; margin: 0;"><strong>Dark mode:</strong>
				<label class="switch">
					<input type="checkbox" onclick="toggleMode(this);" <?php if($_COOKIE['viewPref']=="true") echo("checked=checked"); ?>>
					<span class="slider round"></span>
				</label></p>
				

			<?php } ?>
		</div>
  </div>
	<div id="primaryNav">
		<a href="index.php" <?php if($p == "" || $p == "home") echo("class=\"selected\""); ?>>Home</a>
		<!--<a href="index.php?p=markets" <?php if($p == "markets") echo("class=\"selected\""); ?>>Markets</a>
		<a href="index.php?p=about" <?php if($p == "about") echo("class=\"selected\""); ?>>Firm Profile</a>
		<a href="index.php?p=login" <?php if($p == "login") echo("class=\"selected\""); ?>>Client Login</a>
		<a href="index.php?p=careers" <?php if($p == "careers") echo("class=\"selected\""); ?>>Careers</a>-->
		<a href="index.php?p=contact" <?php if($p == "contact") echo("class=\"selected\""); ?>>Contact</a>
		<a href="index.php?p=employee" class="navRight<?php if($p == "employee") echo(" selected"); ?>">Employee Login</a>
	</div>
	<!-- END PAGE HEADER -->
	
	<?php
	// Temporarily show subnav for layout testing
	// if(validateSecurity()){
	?>
	<div id="subNavLeft">
		<div id="subNavRight">
			<div id="subNav">
				<a href="index.php?p=dashboard" <?php if($p == "dashboard") echo("class=\"selected\""); ?>>Dashboard</a>
                <a href="index.php?p=indices" <?php if($p == "indices") echo("class=\"selected\""); ?>>Indices</a>
				<?php if(isAdmin()) { ?>
				<a href="index.php?p=clients" <?php if($p == "clients") echo("class=\"selected\""); ?>>Traders</a>
                <a href="index.php?p=company" <?php if($p == "company") echo("class=\"selected\""); ?>>Company</a>		
				<a href="index.php?p=brokers" <?php if($p == "brokers") echo("class=\"selected\""); ?>>Brokers</a>
				<a href="index.php?p=invoice" <?php if($p == "invoice") echo("class=\"selected\""); ?>>Invoicing</a>
                <a href="index.php?p=locations" <?php if($p == "locations") echo("class=\"selected\""); ?>>Locations</a>
                <a href="index.php?p=confirm_hub" <?php if($p == "confirm_hub") echo("class=\"selected\""); ?>>ConfirmHub</a>
				<?php } ?>
                <a href="index.php?p=reports" <?php if($p == "reports") echo("class=\"selected\""); ?>>Reporting</a>
			</div>
		</div>
	</div>
	<?php
	// }
	?>

	<div id="mainContentWrapper">
		<div id="mainContent">
		
			<!-- START PAGE CONTENT -->
			<?php
			if($p==null)
				require("home.php");
			else{
				$getPage = preg_replace("/http:/","",$p);
				//$getPage = preg_replace("www","",$getPage);
				
				// Whitelist of allowed pages for security
				$allowedPages = array(
					'home', 'dashboard', 'indices', 'clients', 'company', 'brokers', 
					'invoice', 'locations', 'confirm_hub', 'reports', 'contact', 
					'about', 'careers', 'markets', 'employee', 'forgot', 'reset'
				);
				
				if(in_array($getPage, $allowedPages) && file_exists($getPage.".php"))
					require($getPage.".php");
				else{
					?>
					<div id="contentContainer">
						<p class="error">The page you requested does not exist.</p>
						<p><a href="javascript:void(0);" onclick="history.go(-1);">Return to previous page</a><br />
							<a href="index.php">Go to the home page</a></p>
					</div>
					<?php
				}
			}
			?>
			<!-- END PAGE CONTENT -->
			<div class="clear"></div>
		</div>
	</div>

	<!-- margin for cross browser spacing -->
	<div id="contentBottom"></div>

	<!-- START PAGE FOOTER -->
  <div id="pageFooter">
		&copy; <?php echo(date("Y")); ?> Greenlight Commodities <span class="pipe">&nbsp;|&nbsp;</span>
		<a href="index.php">Home</a> <span class="pipe">&nbsp;|&nbsp;</span>
		<a href="index.php?p=about">Firm Profile</a> <span class="pipe">&nbsp;|&nbsp;</span>
		<a href="index.php?p=contact">Contact</a>
		
		<?php 
		// Show Super Admin indicator if user is Super Admin
		if(validateSecurity() && isSuperAdmin()) {
			echo '<span class="pipe">&nbsp;|&nbsp;</span>';
			echo '<img src="images/superadmin.png" alt="Super Administrator" title="SU" class="superadmin-indicator" />';
		}
		?>
	</div>
	<!-- END PAGE FOOTER -->
</div>
<!-- END OVERALL PAGE WRAPPER -->


<?php
if(strpos($HTTP_HOST,"greenlightcommodities.com") > 0){
?>
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("UA-10991882-1");
	pageTracker._trackPageview();
	} catch(err) {}</script>
<?php
}
?>


</body>
</html>