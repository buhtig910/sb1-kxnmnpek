	<div id="pageGraphic">
		<p class="quote">Thank you for your interest in Greenlight Commodities.</p>

		<p style="margin-left: 20px;"><strong>Houston Area Office</strong><br />
		5571 Purple Meadow Lane<br />
		Fulshear, TX, 77441</p>
		
		<p style="margin-left: 20px;"><strong>Phone &amp; Fax</strong><br />
		Phone: 713-305-7841<br />
		Fax: 
		</p>
		
		<!--<div class="imageMaskTall"></div>
		<img src="images/asdf.jpg" height="330" width="278" alt="" />-->
	</div>
	
	<div id="contentContainer">
	<script language="javascript" type="text/javascript">
	
		function checkForm(){
			if(document.contact.name.value==''){
				alert('Your name is required to submit this form.');
				return false;
			}
			else if(document.contact.email.value==''){
				alert('Your email address is required to submit this form.');
				return false;
			}
			else if(document.contact.comments.value==''){
				alert('Comments are required to submit this form.');
				return false;
			}
			else 
	
			return true;
		}
	
	</script>

	<h1>Contact Us</h1>


	<?
	if($submitted=='true'){
		//require("functions.php");
		$sendMailResult = sendEmail($_POST["name"],$_POST["email"],$_POST["comments"],$_POST["howHeard"],$_POST["otherHeard"]);

		if(isset($email)){
			addEmailToDatabase($_POST["email"]);
		}
		
		if($sendMailResult){
			echo("<p class=\"confirmMessage\">Your message was sent successfully. Thank you for sending us your comments!</p>\n");
		}
		else
			echo("<p class=\"errorMessage\">There was an error sending your message. Please go back and try again.</p>\n");
	}
	else{
	?>
	
		<p>Please send us a message and we will get back to you shortly.</p>
	
		<form action="<?php echo($PHP_SELF); ?>?p=contact" method="post" onsubmit="return checkForm()" name="contact">		
		
		<p class="fieldLabel" style="margin-bottom: 10px;">Your Name: <br />
			<input type="text" name="name" size="25" maxlength="35" /> <span class="note">(required)</span>
		</p>
		
		<p class="fieldLabel" style="margin-bottom: 10px;">Your Email Address: <br />
			<input type="text" name="email" size="25" maxlength="40" /> <span class="note">(required)</span>
		</p>
		
		<script language="javascript" type="text/javascript">
			function checkSelected(listRef) {
				if(listRef.value == "Other")
					document.getElementById("otherHeard").style.display = "";
				else
					document.getElementById("otherHeard").style.display = "none";
			}
		</script>
					
		
		<p class="fieldLabel">Your Comments: <br />
			<textarea name="comments" rows="6" cols="50"></textarea>
		</p>

		
		<p><input type="submit" value="Send" class="button" /> <input type="reset" name="reset" value="Clear" class="button" />
			<input type="hidden" name="submitted" value="true" />
			<input type="hidden" name="loc" value="<?php echo($loc); ?>" />
		</p>
		</form>
	<?
	}
	?>
	
		
</div>