<?php
	global $user;

	$user = FALSE;
	$loginFail = FALSE;
	require("database.php");
	// authenticate user now
	sanitizeInput($_POST);

	// check for submissions
	$st = $_POST['submitType'];

	authenticateLogin($st, $_POST['username'], $_POST['password']);
?>