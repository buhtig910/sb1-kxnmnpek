		<?php
		
		// Login processing is now handled in index.php
		// This file only displays the login form
		
		if(@validateSecurity()){
			echo("<div class=\"\">Logged in: <strong>".$_SESSION["username"]."</strong> | <a href=\"index.php?action=logout\">logout</a></div>\n");
		}
		else {
			
		?>
        
		<form name="loginForm" action="" method="post">
		<table border="0" cellspacing="3" cellpadding="0">
          <tr>
            <td>Username:</td>
            <td><input name="username" id="username" type="text" size="15" /></td>
			<td rowspan="3">
				<?php
				if(isset($loginError) && $loginError != ""){
					echo("<p class=\"errorMessage\">$loginError</p>\n");
				}
				?>
			</td>
          </tr>
          <tr>
            <td>Password: </td>
            <td><input name="password" type="password" size="15" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input name="login" type="submit" id="login" value="Login" class=\"buttonSmall\" />
				<input type="hidden" name="action" value="login" />
                <br /><br />
                <p><a href="index.php?p=forgot">Forgot Password?</a></p>
			</td>
          </tr>
        </table>
		</form>
        
        <script language="javascript" type="text/javascript">
			// Focus on username field when page loads
			document.getElementById("username").focus();
		</script>
		
		<?php
		}
		?>