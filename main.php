<?php

require("config.php");

global $user;
$user = FALSE;
$execTimer = microtime();
if (substr($_SERVER['SERVER_NAME'], 0, 5) == "test.") {
	$testMode = TRUE;
}


// process parameters
$gFunc = strtolower(array_shift(array_keys($_GET)));
$gData = explode("/", $_GET[$gFunc]);

$redirect = NULL;
$output = "HTML";

$tnames = array(1=> "bar", 2=> "beer", 3=> "brewery", 4=> "style", 5=> "q_barbeer");

$phantom = ($_POST['phantom'] == "TRUE");

// ------ LET'S ROCK IT

// database.php is loaded inside auth.php right now
require("auth.php");
require("display.php");
require("mapping.php");
require("forum.php");
require("xml.php");

if ($testMode and userAccess("debug")) {
	print_r($_POST);
}

if (userAccess($st)) {
	switch ($st) {
		case "login":
			if (!$user)
				$loginFail = TRUE;
		case "logout":
			break;
		case "bar":
		case "beer":
		case "brewery":
		case "style":
			submitInfo(array_search($st, $tnames));
			break;
		case "beerChangePhantom":
			$phantom = TRUE;
		case "beerChange":
			submitBarBeer($_POST['barNum'], $phantom);
			break;
		case "modProcess":
			processMods($_POST['mod_table'], $_POST['mod_process'], $_POST['mod_approval'], $_POST['mod_comm']);
			break;
		case "message":
			sendMessage($user["num"], $_POST["m_recipient"], $_POST["m_subject"], $_POST["m_message"]);
			break;
		case "messageDelete":
			submitMessageDelete();
			break;
		case "userSignup":
			processSignup();
			break;
		default:
	}
}

if (($gFunc == "") and (!$testMode)) { 
	$redirect = "/blog/";
}

// if a redirect was specified, do that now
if ($redirect) {
	header("Location: http://" . $_SERVER['HTTP_HOST'] . $redirect);
	return TRUE;
}

if ($gFunc == "xml") {
	$output = "XML";
	header("Content-Type: text/xml");
	echo "<" . "?xml version='1.0' encoding='UTF-8' ?" . ">\n";
	$gFunc = array_shift($gData);
}

if ($output == "HTML") {
	// buffer this shiz so we can change the page title at whim
	ob_start("writeTitle");
	require("pages/preheader.php");
	require("pages/header.php");
}

switch ($gFunc) {
	case "bar":
	case "beer":
	case "brewery":
	case "style":
		if ($output == "XML") {
			XML_DisplayInfo($gFunc, $gData[0], FALSE);
		} elseif ($output == "HTML") {
			if ($st == $gFunc) {
				// do nothing
			} elseif ($gData[1] == "pending") {
				listPendingMods(array_search($gFunc, $tnames), $gData[0]);
			} elseif (is_numeric($gData[0]) or (($gData[0] == "addnew") and userAccess($gFunc))) {
				displayInfo($gFunc, $gData[0], (in_array("edit", $gData) or ($gData[0] == "addnew")));
			} elseif ($gData[0] == NULL) {
				reportRows($gFunc);
			} else {
				display404();
			}
		} else {
			logError("Invalid output type!");
		}
		break;
	case "mod":
		if ($gData[0] == ""){
			$gData[0] = 0;
		}
		if (is_numeric($gData[0])) {
			if ($gData[1]) {
				displayMod($gData[0], $gData[1]);
			} else {
				listPendingMods($gData[0]);
			}
		} else {
			display404();
		}
		break;
	case "search":
		$search = parseSearch($gData);
		displaySearch($search["query"], $search["loc"], $search["tap"], $search["offset"]);
		break;
	case "messages":
		if ($user) {
			displayMessages($gData);
		} else {
			display404();
		}
		break;
	case "forum":
	  if ($user) {
	  	if ($gData[0] == "") {
	  		fListForums();
	  	} elseif ($gData[0] == "topic" and is_numeric($gData[1])) {
	  		fDispTopic($gData[1]);
	  	} elseif (is_numeric($gData[0]) and !$gData[1]) {
	  		fDispForum($gData[0]);
	  	} else {
	  		display404();
	  	}
	  } else {
	  	display404();
	  }
	  break;
  case "signup":
		include("pages/signup.php");
		break;
	case "activate":
		activateAccount($gData[0]);
		break;
	case "help":
		displayHelp($gData);
		break;
	case "test":
		break;
	case "":

		include("pages/front.php");
		break;
	case "gencodes":
		if (userAccess("genCodes")) {
			generateCodes();
		} else {
			display404();
		}
		break;

	case "geocode":
		geocodeBars();

	default:
		display404();
	
}

if ($output == "HTML") {
	flushLogs();
	writeLog();
	require("pages/footer.php");
	ob_end_flush();
}

mysql_close($dblink);


// ---- FUNCTIONS ==============================================

function getState ($s) {
	$s = strtoupper($s);
	$states = array("AL"=>"Alabama", "AK"=>"Alaska", "AZ"=>"Arizona", "AR"=>"Arkansas", "CA"=>"California", "CO"=>"Colorado", "CT"=>"Connecticut", "DE"=>"Delaware", "DC"=>"District of Columbia", "FL"=>"Florida", "GA"=>"Georgia", "HI"=>"Hawaii", "ID"=>"Idaho", "IL"=>"Illinois", "IN"=>"Indiana", "IA"=>"Iowa", "KS"=>"Kansas", "KY"=>"Kentucky", "LA"=>"Louisiana", "ME"=>"Maine", "MD"=>"Maryland", "MA"=>"Massachusetts", "MI"=>"Michigan", "MN"=>"Minnesota", "MS"=>"Mississippi", "MO"=>"Missouri", "MT"=>"Montana", "NE"=>"Nebraska", "NV"=>"Nevada", "NH"=>"New Hampshire", "NJ"=>"New Jersey", "NM"=>"New Mexico", "NY"=>"New York", "NC"=>"North Carolina", "ND"=>"North Dakota", "OH"=>"Ohio", "OK"=>"Oklahoma", "OR"=>"Oregon", "PA"=>"Pennsylvania", "PR"=>"Puerto Rico", "RI"=>"Rhode Island", "SC"=>"South Carolina", "SD"=>"South Dakota", "TN"=>"Tennessee", "TX"=>"Texas", "UT"=>"Utah", "VT"=>"Vermont", "VA"=>"Virginia", "WA"=>"Washington", "WV"=>"West Virginia", "WI"=>"Wisconsin", "WY"=>"Wyoming");
	if (isset($states[$s]))
		return $states[$s];
	return $s;
}

function writeTitle($buffer) {
	global $pageTitle, $output;
	if ($output <> "HTML") return FALSE;
	$pt = (isset($pageTitle) ? ($pageTitle . " | ") : "");
	$pt .= "twincitiesbeer.com";
	return (str_replace("<title>", "<title>$pt", $buffer));
}

function logMessage($msg) {
	global $msgLog;
	$msgLog .= "<p class=\"logMessage\"> $msg </p>\n";
}

function logNotice($msg) {
	global $noticeLog;
	$noticeLog .= "<p class=\"logNotice\"> $msg </p>\n";
}

function logError($msg) {
	global $errorLog;
	$errorLog .= "<p class=\"logError\"> $msg </p>\n";
}

function flushLogs() {
	global $errorLog, $noticeLog, $msgLog;
	echo "\n" . $errorLog;
	echo "\n" . $noticeLog;
	echo "\n" . $msgLog;
	$msgLog = "";
	$noticeLog = "";
	$errorLog = "";
}

function writeLog() {
	global $execTime, $user, $gFunc, $gData;
	$log = fopen('./log/exec.log', "a");
	$c = $gFunc;
	foreach ($gData as $a) {
		$c .= "/" . $a;
	}
	$d = date("y-m-d H:i:s ", time());
	$t = round((microtime() - $execTime) * 1000);
	$ws = $d . ($user["num"] ? $user["num"] : "0") . " " . $t . " " . $c . "\n";
	fwrite($log, $ws);
	fclose($log);
}

function parseSearch($input) {
	// reconstruct $query out of $input as a single string, and make it lowercase
	$query = "";
	$tap = FALSE;
	$offset = 0;

	// check for offset but be sure not to accidentally include a ZIP code as offset
	$n = array_pop($input);
	if (is_numeric($n) and ($n < 10000)) {
		// set the offset 
		$offset = $n;
	} else {
		// restore the value
		$input[] = $n;
	}
	
	foreach ($input as $n) {
		$n = strtolower($n);
		if ($n == "tap") {
			$tap = TRUE;
		} else {
			$query .= " " . $n;
		}
	}

	// remove "?qb=" at beginning of query, if present
	if (substr($query, 0, 4) == "?qb=")
		$query = substr($query, 4);

	// replace underscores if present
	$query = strtr($query, "_", " ");
	// replace "+" if present (e.g. user had javascript turned off so was submitted as a GET query)
	$query = strtr($query, "+", " ");

	// check end of string for zip
	$loc = substr($query, strlen($query)-5);
	if (is_numeric($loc)) {
		$query = substr($query, 0, strlen($query)-5);
		// remove "in" at the end if it's present
		if (substr($query, strlen($query)-4) == " in ") {
			$query = substr($query, 0, strlen($query)-3);
		}
	} else {
		$loc = "";
	}

	// check for "in <city>" in string
	$n = strpos($query, " in ");
	if (!($n === FALSE)) {
		$loc = substr($query, $n + 4);
		$query = substr($query, 0, $n);
	}

	$loc = " " . $loc . " ";
	$loc = str_replace(" st ", " saint ", $loc);
	$loc = str_replace(" st. ", " saint ", $loc);

	// clean up $query and $loc now
	$query = trim($query);
	$loc = trim($loc);

	return array("query" => $query, "loc" => $loc, "tap" => $tap, "offset" => $offset);
}

function generateCodes() {
	// this function both accesses the database and displays; bad form
	$bars = $_POST["codeBar"];

	$quantity = count($bars);

	$output = array();

	$cc = "cdfghjklmnpqtwxy";
	$cv = "aeu";
	$cn = "34679";

	for($n=0; $n < $quantity; $n++) {
		$code = "";
		$code .= substr($cc, rand(0, strlen($cc) - 1), 1);
		$code .= substr($cv, rand(0, strlen($cv) - 1), 1);
		$code .= substr($cc, rand(0, strlen($cc) - 1), 1);
		for($x=0; $x < 6; $x++) {
			$code .= substr($cn, rand(0, strlen($cn) - 1), 1);
		}
		$output[] = $code;
	}

	$q = "INSERT INTO accesscode (code, bar) VALUES ";
	for ($n=0; $n < $quantity; $n++) {
		$q .= '("' . $output[$n] . '", "' . $bars[$n] . '"), ';
	}
	$q = substr($q, 0, strlen($q) - 2);

	if (mysql_query($q)) {
		$names = getInfo("getNames", "bar", $bars);

		echo "<table id=\"codeDisplay\">\n";
		echo "<tr><th>name</th><th>code</th></tr>\n";
		for($n=0; $n<count($bars); $n++) {
			echo '<tr><td><a href="/bar/' . $bars[$n] . '">' . $names[$n]["name"] . '</a></td><td><tt>' . $output[$n] . '</tt></td></tr>' . "\n";
		}
		echo "</table>\n";
		return TRUE;
	} else {
		logError("Adding codes to database failed!");
		return FALSE;
	}
}

function activateAccount($code) {
	$access = 20;

	$a = getInfo("checkActivation", $code);

	if (empty($a)) {
		echo "<p>The activation code is invalid.  Please double-check your activation link, or register again.</p>\n";
		return FALSE;
	} else {
		$usernum = $a[0]["id"];
		$q = "UPDATE user SET access = $access, confirmed='yes' WHERE id = $usernum";
		if (mysql_query($q)) {
			echo "<p>Your account has been activated!  You are now able to make forum posts, send messages to other users, and update information on this site.</p>\n";
			return TRUE;
		} else {
			logError("activateAccount: UPDATE query failed!");
		}
	}
}

?>