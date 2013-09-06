<?php if (in_array("success", $gData)) { ?>
	<p> Signup successful!  Please check your email for the confirmation link.  You will have to do this in order for your account to be fully activated. </p>
<?php } else { ?>
	<form action="/signup/" method="post">
	<input type="hidden" name="submitType" value="userSignup">
	<table id="signup">
	<tr>
		<td>login:</td>
		<td><p>Your login can be up to 12 characters long.  No spaces or punctuation except for underscore (<tt>_</tt>) or dash (<tt>-</tt>), please.</p>
			<input type="text" name="u_login" value="<?= $_POST["login"] ?>">
		</td>
	</tr><tr>	
		<td>name:</td>
		<td><p>Your name.  You can use an alias if you wish.</p>
			<input type="text" name="u_name" value="<?= $_POST["name"] ?>">
		</td>
	</tr><tr>
		<td>password:</td>
		<td><input type="password" name="u_pass1"></td>
	</tr><tr>
		<td>confirm password:</td>
		<td><input type="password" name="u_pass2"></td>
	</tr><tr>
		<td>email:</td>
		<td><p>You must enter a valid email address.  A confirmation email will be sent to this address with a link you must follow to activate this account. We will only use this address to notify you of new messages and TwinCitiesBeer.com news and events, if you choose.  For details, see our <a href="/help/privacy">Privacy Policy</a>.</p>
		<input type="text" name="u_email" value="<?= $_POST["email"] ?>">
		</td>
	</tr><tr>
		<td></td>
		<td>
		<input type="checkbox" name="u_emailDisp" CHECKED>Show my email address to other users on my profile page.<br>
		<input type="checkbox" name="u_emailMsg"<?= $_POST["emailMsg"] == "on" ? " CHECKED" : "" ?>>Notify me via email when other users send me messages.<br>
		<input type="checkbox" name="u_emailNews"<?= $_POST["emailNews"] == "on" ? " CHECKED" : "" ?>>Notify me via email of TwinCitiesBeer.com news and events.
		</td>
	</tr><tr>
		<td>ZIP code:</td>
		<td><p>You can enter your ZIP code to personalize your searches.  You can leave this blank if you wish.</p>
		<input type="text" name="u_zip" value="<?= $_POST["zip"] ?>">
		</td>
	</tr><tr>
		<td>Access code:</td>
		<td><p>If you were given an access code by us, please enter it here.  If you don't know what this is, just leave it blank.</p>
		<input type="text" name="u_accessCode" value="<?= $_POST["accessCode"] ?>">
		</td>
	</tr><tr>
		<td></td>
		<td><input type="submit" value="Process Signup"></td>
	</tr>
	</table>
	</form>
<?php } ?>