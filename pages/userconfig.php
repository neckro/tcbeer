<?php
	$up = getInfo("userInfo", $user["num"]);
	$up = $up[0];
	
	uToggleParse($up);
?>

	<p><a href="/user/<?= $user['num'] ?>">View your profile</a></p>

	<form action="/prefs/" method="post">
	<input type="hidden" name="submitType" value="userPrefs">
	<table id="userinfo">
	<tr>
		<td>name:</td>
		<td><input type="text" name="u_name" value="<?= $up["name"] ?>">
		</td>
	</tr><tr>
		<td>old password:</td>
		<td><input type="password" name="u_pass"></td>
	</tr><tr>
		<td>new password:</td>
		<td><input type="password" name="u_pass1"></td>
	</tr><tr>
		<td>confirm new password:</td>
		<td><input type="password" name="u_pass2"></td>
	</tr><tr>
		<td>email:</td>
		<td>
			<b><?= $up["email"] ?></b><br>
			(changing of email address temporarily out of order - we're working on it, folks)
<!--		<input type="text" name="u_email" value="<?= $up["email"] ?>"> -->
		</td>
	</tr><tr>
		<td><input type="checkbox" name="u_emailDisp"<?= $up["emailDisp"] ? " CHECKED" : "" ?>></td>
		<td>Show my email address to other users on my profile page.</td>
	</tr><tr>
		<td><input type="checkbox" name="u_emailMsg"<?= $up["emailMsg"] ? " CHECKED" : "" ?>></td>
		<td>Notify me via email when other users send me messages.</td>
	</tr><tr>
		<td><input type="checkbox" name="u_emailNews"<?= $up["emailNews"] ? " CHECKED" : "" ?>></td>
		<td>Notify me via email of TwinCitiesBeer.com news and events.</td>
	</tr><tr>
		<td>ZIP code:</td>
		<td>
			<input type="text" name="u_zip" value="<?= $up["zip"] ?>">
		</td>
	</tr><tr>
		<td>Bio:</td>
		<td>
			Tell us a little bit about yourself. <br>
			<textarea name="u_bio" cols=40 rows=20><?= $up["bio"] ?></textarea>
		</td>
	
	</tr>
	
	<tr>
		<td></td>
		<td><input type="submit" value="Submit Preferences"></td>
	</tr>
	</table>
	</form>
