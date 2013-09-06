<?php
	$up = getInfo("userInfo", $un);
	$up = $up[0];
	
	if (empty($up)) {
		logNotice("That user does not exist.");
	} else {
		uToggleParse($up);

?>

	<h1>Profile for <?= $up["login"] ?></h1>

	<table id="userinfo">
	<tr>
		<td>name:</td>
		<td><?= $up["name"] ?></td>
	</tr><tr>
		<td>user class:</td>
		<td><?= userClass($up["access"]) ?></td>
	</tr><tr>
		<td>forum posts:</td>
		<td><?= $up["uposts"] ?></td>
	</tr><tr>
		<td>email:</td>
		<td><b><?= (($up["emailDisp"] and userAccess("showEmail")) or userAccess("showEmailAll") or ($up["access"] >= 90)) ? $up["email"] : "Undisclosed" ?></b></td>
	</tr><tr>
		<td>ZIP code:</td>
		<td><?= $up["zip"] ?>
		</td>
	</tr><tr>
		<td>Bio:</td>
		<td><?= formatText(NULL, $up["bio"]) ?></td>
	</tr>
	
	</table>

<?php
	}
?>