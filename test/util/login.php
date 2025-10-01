		<?php
		
		if($_POST["action"] == "login"){
			$message = login($_POST["username"],$_POST["password"]);			
		}
		
		if(@validateSecurity()){
			echo("<div class=\"\">Logged in: <strong>".$_SESSION["username"]."</strong> | <a href=\"index.php?action=logout\">logout</a></div>\n");
		}
		else {
			
		?>
		<script language="javascript" type="text/javascript" src="scripts/jquery.js"></script>
        
		<form name="loginForm" action="" method="post">
		<table border="0" cellspacing="3" cellpadding="0">
          <tr>
            <td>Username:</td>
            <td><input name="username" id="username" type="text" size="15" /></td>
			<td rowspan="3">
				<?php
				if($message != ""){
					echo("<p class=\"errorMessage\">$message</p>\n");
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
            <td><input name="login" type="submit" id="login" value="Login" class="buttonSmall" />
				<input type="hidden" name="action" value="login" />
                <br /><br />
                <p><a href="index.php?p=forgot">Forgot Password?</a></p>
			</td>
          </tr>
        </table>
		</form>
        
        <script language="javascript" type="text/javascript">
			$("#username").focus();
		</script>
		
		<?php
		}
		?>