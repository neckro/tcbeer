<?php

// ANYTHING THAT OUTPUTS HTML GOES IN HERE

function displayInfo($table, $r, $edit = FALSE, $mod = FALSE) {
	global $pageTitle, $user;
	$r = intval($r);

	if ($mod) {
		if (!userAccess("moderate")) {
			if ($a["user"] <> $user["num"]) {
				if (($table == "bar") and ($user["bar"] <> $r)) {
					// if all of these checks fail, then user does not have access to view this mod
					logError("You do not have access to view the moderation details for this entry.");
					return FALSE;
				}
			}
		}
	}

	if ($r <> 0) {
		if ($table == "q_barbeer") {
			$a = getInfo("modInfo", $table, $r, FALSE, FALSE, FALSE, TRUE);
		} else {
			$a = getInfo($table . "Info", $r, $mod);
		}
		$a = $a[0];
		if ($mod) {
			$pageTitle = "Entry details";
		} else {
			$pageTitle = $a["name"];
		}
	}

	if ($edit and !userAccess($table)) {
		display404();
		return FALSE;
	}

	// buffer this here so there's no code duplication
	$comm = NULL;
	if ($user and !userAccess("moderate")) {
		$comm .= '<tr><td align="right">comments to moderator: </td><td><textarea name="d_comm" rows=5 cols=30></textarea></td></tr>' . "\n";
		$comm .= '<tr><td></td><td>The data you submit will be reviewed by our moderators.  You will receive a message when this entry has been processed.</td></tr>' . "\n";
	}

	if (empty($a) and ($r <> 0)) {
		if ($mod) {
			logError("ERROR: No such table: $table and/or id: $r");
			return FALSE;
		} else {
			logError("ERROR: No such table: $table and/or id: $r");
			return FALSE;
		}
	}

	switch ($table) {
		case "bar":
			$barBrewery = $a["brewery"];

			if ($edit) {
				echo "<FORM ACTION=\"/bar/$r\" method=\"POST\"><br>\n";
				echo '<INPUT TYPE="HIDDEN" NAME="submitType" VALUE="bar">' . "\n";
				echo "<TABLE border=0 valign=\"top\">\n";
				echo '<tr><td align="right">name:</td><td><INPUT TYPE="TEXT" NAME="d_name" VALUE="' . htmlspecialchars($a["name"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">address:</td><td><INPUT TYPE="TEXT" NAME="d_address" VALUE="' . htmlspecialchars($a["address"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">city:</td><td><INPUT TYPE="TEXT" NAME="d_city" VALUE="' . htmlspecialchars($a["city"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">zip:</td><td><INPUT TYPE="TEXT" NAME="d_zip" VALUE="' . htmlspecialchars($a["zip"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">phone:</td><td><INPUT TYPE="TEXT" NAME="d_phone" VALUE="' . htmlspecialchars($a["phone"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">URL:</td><td><INPUT TYPE="TEXT" NAME="d_url" VALUE="' . htmlspecialchars($a["url"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">description:</td><td><TEXTAREA NAME="d_desc" rows=10 cols=50>' . htmlspecialchars($a["description"]) . "</TEXTAREA></td></tr>\n";
				echo '<tr><td align="right">related brewery:<br>(numeric id)</td><td><INPUT TYPE="TEXT" NAME="barBrewery" VALUE="' . htmlspecialchars($a["brewery"]) . "\"></td></tr>\n";
				echo $comm;
				echo "<tr><td></td><td><INPUT TYPE=\"SUBMIT\" VALUE=\"Submit\"></td></tr></TABLE></FORM>\n";
			} else {
				echo '<div class="showinfo">';
				echo "<h1>" . $a["name"] . "</h1>\n";
				echo '<div class="info">' . "\n";
				echo ($a["address"] ? $a["address"] . " &bull; " : "") . ($a["city"] ? $a["city"] . ", MN " : "") . ($a["zip"] <> "00000" ? $a["zip"] : "");
				if ($a["address"] and $a["city"]) {
					echo " [<a href=\"http://maps.google.com/maps?q=" . urlencode($a["address"] . ", " . $a["city"] . " MN " . ($a["zip"] ? $a["zip"] : "")) . "&hl=en\">map</a>] <br>\n";
				}
				if ($a["phone"]) {
					echo "(" . substr($a["phone"], 0, 3) . ") " . substr($a["phone"], 3, 3) . "-" . substr($a["phone"], 6, 4) . "\n";
				} else {
					echo "(Phone number not available.)\n";
				}
				if ($a["url"]) {
					echo (($a["url"] == "") ? "" : (' [<a href="' . $a["url"] . "\">website</a>]\n"));
				} else {
					echo "(Website not available.)\n";
				}
				echo ((intval($a["brewery"]) == 0) ? "" : ("<br>Related brewery: <a href=\"/brewery/" . $a["brewery"] . '">' . $a["breweryname"] . "</a> (Beers from this brewery are in <b>bold</b>.)"));
				echo "</div>\n";
				echo formatText("desc", $a["description"]);
				modInfo("bar", $r, $a, $mod);
				echo "</div>\n";
			}

			// if we're not adding a new bar
			if (!$edit) {
				// print beers available

				// there must be a more elegant way to do this logic
				// basically this means don't show the phantom beerlist if the mod is not pending
				$changeAccess = (userAccess("beerChange"));
				if ($changeAccess) {
					if ($mod and ($a["status"] <> "Pending")) {
						$changeAccess = FALSE;
					}
				}
				
				if ($changeAccess) {
					echo '<FORM ACTION="' . ($mod ? "/mod/1/" : "/bar/") . $r . '" method="POST">' . "\n";
					echo '<INPUT type="hidden" name="submitType" value="beerChange">';
					echo '<INPUT type="hidden" name="barNum" value="' . $r . '">';
					if ($mod) {
						echo '<INPUT type="hidden" name="phantom" value="TRUE">';
					}
				}

				// generate table list
				if ($mod) {
					$a = getInfo("modInfo", "q_barbeer", $r, TRUE, FALSE, FALSE, FALSE, TRUE);
				} else {
					$a = getInfo("barBeer", $r);
				}

				for($n = 0; $n < count($a); $n++) {
					if ($changeAccess and !$mod) {
						$ba[$n] .= '<INPUT type="checkbox" name="beerDelete[]" value="' . $a[$n]["bbid"] . '"></td><td>';
					}
					$ba[$n] .= '<a href="/beer/' . $a[$n]["beerid"] . '">';
					if ($a[$n]["breweryid"] == $barBrewery) {
						$ba[$n] .= "<b>" . $a[$n]["beername"] . "</b>";
					} else { 
						$ba[$n] .= $a[$n]["beername"];
					}
					$ba[$n] .= "</a></td><td>" . $a[$n]["formatname"];
				}

				printTable($ba, "beerList");
				
				// recruitment blurb
				if (!$changeAccess) {
					echo "\n" . '<p>Is this information correct?  If not, please consider taking a moment of your time to email us corrections at <img width=195 height=14 src="/i/gfx/email-14-white.gif" alt="beer at this address" \>.  If you wish to take a more active role in helping us maintain this database, you can <a href="/signup/">create an account</a> to submit corrections yourself. </p>' . "\n";
				}

				if ($changeAccess) {
					echo "<p>Add beers: ";
					echo ($mod ? "<br>(These will not be approved unless this moderation is also approved.)" : "");
					echo "</p>\n";
					displayBeerAdd();
					if ($mod) {
						echo '<p><INPUT type="submit" value="Submit"></p>' . "\n";
					} else {
						echo '<p><INPUT type="submit" value="Delete Checked / Submit New"></p>' . "\n";
					}
					echo "</FORM>\n";
				}

			}
			break;

		case "beer":
			if ($edit) {
				echo "<FORM ACTION=\"/beer/$r\" method=\"POST\"><br>\n";
				echo '<INPUT TYPE="HIDDEN" NAME="submitType" VALUE="beer">' . "\n";
				echo "<TABLE border=0>\n";
				echo "<tr><td align=\"right\">name:</td><td><INPUT TYPE=\"TEXT\" NAME=\"d_name\" VALUE=\"" . htmlspecialchars($a["name"]) . "\"></td></tr>\n";
				echo "<tr><td align=\"right\">style:</td><td><SELECT NAME=\"d_style\">";
				echo listOptions("style", $a["styleid"]);
				echo "</SELECT></td></tr>\n";
				echo "<tr><td align=\"right\">brewery:</td><td><SELECT NAME=\"d_brewery\">";
				echo listOptions("brewery", $a["breweryid"]);
				echo "</SELECT></td></tr>\n";
				echo '<tr><td align=\"right\">description:</td><td><TEXTAREA NAME="d_desc" rows=10 cols=50>' . htmlspecialchars($a["description"]) . "</TEXTAREA></td></tr>\n";
				echo $comm;
				echo "<tr><td></td><td><INPUT TYPE=\"SUBMIT\" VALUE=\"Submit\"></td></tr></TABLE></FORM>\n";
			} else {
				echo '<div class="showinfo">';
				echo "<h1>" . $a["name"] . "</h1>\n";
				echo '<div class="info">' . (ereg("^[aeiouAEIOU]", $a["style"]) ? "An" : "A") . " <a href=\"/style/" . $a["styleid"] . '">' . $a["style"] . '</a> brewed by <a href="/brewery/' . $a["breweryid"] . '">' . $a["brewery"] . "</a> in " . (($a["state"] <> "") ? (getState($a["state"]) . ", ") : "") . $a["country"] . "</div>\n";
				echo formatText("desc", $a["description"]);
				modInfo("beer", $r, $a, $mod);
				echo "\n</div>\n";
			}

			if (!$mod) {
				if (!($a = getInfo("beerBar", $r)))
					return FALSE;

				for($n = 0; $n < count($a); $n++) {
					$ba[$n] = '<a href="/bar/' . $a[$n]["id"] . '">' . $a[$n]["name"] . '</a></td><td>' . $a[$n]["format"];
				}
				printTable($ba, "beerList");
			}

			break;

		case "brewery":
			if ($edit) {
				echo "<FORM ACTION=\"/brewery/$r\" method=\"POST\">\n";
				echo '<INPUT TYPE="HIDDEN" NAME="submitType" VALUE="brewery">' . "\n";
				echo "<TABLE border=0>\n";
				echo '<tr><td align="right">name:</td><td><INPUT TYPE="TEXT" NAME="d_name" VALUE="' . htmlspecialchars($a["name"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">state:</td><td><INPUT TYPE="TEXT" SIZE=2 NAME="d_state" VALUE="' . htmlspecialchars($a["state"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">country:</td><td><INPUT TYPE="TEXT" NAME="d_country" VALUE="' . htmlspecialchars($a["country"]) . "\"></td></tr>\n";
				echo '<tr><td align="right">description:</td><td><TEXTAREA NAME="d_desc" rows=10 cols=50>' . htmlspecialchars($a["description"]) . "</TEXTAREA></td></tr>\n";
				echo '<tr><td align="right">brand name:</td><td><INPUT TYPE="TEXT" NAME="d_brand" VALUE="' . htmlspecialchars($a["brandname"]) . "\"><br>This will be prefixed to all beer names under this brewery; if unsure leave this blank</td></tr>\n";
				echo $comm;
				echo "<tr><td></td><td><INPUT TYPE=\"SUBMIT\" VALUE=\"Submit\"></td></tr></TABLE></FORM>\n";
			} else {
				echo '<div class="showinfo">';
				echo "<h1>" . $a["name"] . "</h1>\n";
				echo '<div class="info">Located in ' . (($a["state"] <> "") ? (getState($a["state"]) . ", ") : "") . $a["country"] . "</div>\n";
				if (!empty($a["brandname"])) {
					echo '<div class="info">Brand name: <b>';
					echo $a["brandname"];
					echo "</b></div>\n";
				}

				echo formatText("desc", $a["description"]);
				modInfo("brewery", $r, $a, $mod);
				echo "\n</div>\n";
			}

			if (!$mod) {
				if (!($a = getInfo("breweryBeer", $r)))
					return FALSE;

				for($n = 0; $n < count($a); $n++) {
					$ba[$n] = '<a href="/beer/' . $a[$n]["id"] . '">' . $a[$n]["name"] . '</a></td><td><a href="/style/' . $a[$n]["style"] . '">' . $a[$n]["sname"] . "</a>";
				}

				printTable($ba, "beerList");
			}
			break;

		case "style":
			if ($edit) {
				echo "<FORM ACTION=\"/style/$r\" method=\"POST\">\n";
				echo '<INPUT TYPE="HIDDEN" NAME="submitType" VALUE="style">' . "\n";
				echo "<TABLE border=0>\n";
				echo "<tr><td align=\"right\">name:</td><td><INPUT TYPE=\"TEXT\" NAME=\"d_name\" VALUE=\"" . htmlspecialchars($a["name"]) . "\"></td></tr>\n";
				echo '<tr><td align=\"right\">description:</td><td><TEXTAREA NAME="d_desc" rows=10 cols=50>' . htmlspecialchars($a["description"]) . "</TEXTAREA></td></tr>\n";
				echo $comm;
				echo "<tr><td></td><td><INPUT TYPE=\"SUBMIT\" VALUE=\"Submit\"></td></tr></TABLE></FORM>\n";
			} else {
				echo '<div class="showinfo">';
				echo "<h1>" . $a["name"] . "</h1>\n";
				echo formatText("desc", $a["description"]);
				modInfo("style", $r, $a, $mod);
				echo "\n</div>\n";
			}

			if (!$mod) {
				if (!($a = getInfo("styleBeer", $r)))
					return FALSE;

				for($n = 0; $n < count($a); $n++) {
					$ba[$n] = '<a href="/beer/' . $a[$n]["id"] . '">' . $a[$n]["name"] . '</a></td><td><a href="/brewery/' . $a[$n]["brewery"] . '">' . $a[$n]["bname"] . "</a>";
				}

				printTable($ba, "beerList");
			}
			break;
		case "q_barbeer":
			echo "<div class=\"showinfo\">\n";
			echo "<p><strong>Type:</strong> {$a['type']} </p>\n";
			echo "<p><strong>Bar:</strong> <a href=\"/bar/{$a['barid']}\">{$a['barname']}</a> </p>\n";
			echo "<p><strong>Beer:</strong> <a href=\"/beer/{$a['beerid']}\">{$a['beername']}</a> </p>\n";
			echo "<p><strong>Format:</strong> {$a['format']} </p>\n";
			modInfo("q_barbeer", $r, $a, TRUE);
			break;
	}
	return TRUE;
}

function displayBeerAdd() {
	// form to add more beers
	// $b/$f are the beer/format lists, generate them once so we don't tax the server generating it 4x
	$b = listOptions("beer", 0);
	$f = listOptions("format", 100, FALSE);

//	for($n = 0; $n < 4; $n++) {
	echo '<DIV id="addbeerlist">' . "\n";
	echo "\t" . '<SELECT name="beerAdd[]" size=1><OPTION value="" SELECTED>(none)</OPTION>' . $b . "</SELECT>\n";
	echo "\t" . '<SELECT name="beerTap[]" size=1>' . $f . "</SELECT><br>\n";
	echo "</DIV>\n";
?><script type="text/javascript"> <!--
	var t=document.getElementById("addbeerlist").innerHTML;
	for(n=0;n<=3;n++) {
		document.write(t);
	}
--></script>

<?php
}

function displayMod($section, $modnum) {
	global $tnames;
	$tn = $tnames[$section];

	if ($tn) {
		displayInfo($tn, $modnum, FALSE, TRUE);
	} else {
		// invalid section specified
		display404();
		return FALSE;
	}
}

function listPendingMods($table, $id = 0) {
	global $user, $tnames;

	$tn = $tnames[$table];
	$tableData = NULL;

	// allow bar owners to view pending mods for their own
	$ua = (userAccess("moderate") or (($table == 1) and ($id == $user["bar"])));

	if (!$ua) {
		logMessage("listPendingMods: You do not have access.");
		return FALSE;
	}

	if ($table == 0) {
		// show # of pending mods in all tables
		$a = getInfo("getPendingModNumbers");
		echo "<p>Pending submissions:</p>";
		echo "<table class=\"standard\">\n";
		echo "<th align=\"right\">table</th><th>#</th>\n";
		$n = 1;
		foreach ($a as $r) {
			echo '<tr><td align="right"><a href="/mod/';
			echo $n++ . '">' . $r["tname"] . '</a></td><td>' . $r["pending"] . "</td></tr>\n";
		}
		echo "</table>\n";
		return TRUE;
	}
	if ($id > 0) {
		// show pending mods for a specific table entry
		$a = getInfo("modInfo", $tn, $id, TRUE);
		if (!empty($a)) {
			if ($table == 5) {
				$action = "/bar/$id/pending";
				echo "<p>Pending beer change submissions for bar id# $id:</p>\n";
			} else {
				$action = "/$tn/$id/pending";
				echo "<p>Pending submissions for $tn id# $id:</p>\n";
			}
		} else {
			echo "<p>No pending submissions for this entry.</p>\n";
		}
	} elseif ($tn) {
		$a = getInfo("modInfo", $tn);
		if (!empty($a)) {
			$action = "/mod/$table";
			echo "<p>Pending submissions for table $tn:</p>\n";
		} else {
			echo "<p>No pending entries for this table.</p>\n";
		}
	} else {
		display404();
		return FALSE;
	}
	if (!empty($a)) {

		echo "<form action=\"$action\" method=\"POST\">\n";
		echo '<input type="hidden" name="submitType" value="modProcess">';
		echo '<input type="hidden" name="mod_table" value="' . $table . "\">\n";
		echo "<table class=\"standard\">\n";
		echo "<tr><th></th><th>#</th><th>time</th><th>user</th>";
		echo ($table == 5 ? "<th>type</th><th>bar</th><th>beer</th><th>format</th>" : "<th>$tn</th><th>replaces</th>") . "<th>&nbsp;</th></tr>\n";
		foreach ($a as $r) {
			$date = ($r['stime'] ? date('ymd&\n\b\s\p;H:i', $r["stime"]) : "unknown");
			echo "<tr><td><input type=\"checkbox\" name=\"mod_process[]\" value=\"{$r['id']}\"></td><td>{$r['id']}</td><td>$date</td><td><a href=\"/user/{$r['uid']}\">{$r['uname']}</a></td>";
			if ($table == 5) {
				echo "<td>{$r['type']}</td><td><a href=\"/bar/{$r['barid']}\">{$r['barname']}</a></td><td><a href=\"/beer/{$r['beerid']}\">{$r['beername']}</a></td><td>{$r['format']}</td>";
			} else {
				echo "<td>{$r['newname']}</td><td>" . ($r['curname'] == $r['newname'] ? "[same]" : ($r['curname'] == "" ? "[new]" : "<a href=\"/$tn/{$r['r_id']}\">{$r['curname']}</a>")) . "</td>";
			}
			echo "<td><a href=\"/mod/$table/{$r['id']}\">[details]</a></td></tr>\n";
		}
		echo "</table>\n";
		echo "<p>Moderator comments: <br><textarea rows=5 cols=30 name=\"mod_comm\"></textarea>";
		echo "<br>(Note: If you wish to have separate comments for each moderation, submit them one at a time!)</p>\n";
		echo '<select name="mod_approval" size=1><option value="Approve">Approve</option><option value="Deny">Deny</option></select>' . "\n";
		echo "<input type=\"submit\" value=\"Process checked\">\n";
		echo "</form>\n";
	}

	// if this is a specific bar entry, show pending barbeers as well
	if (($table == 1) and ($id > 0)) {
		listPendingMods(5, $id);
	}
}

function displaySearch($query, $location, $tap = FALSE, $offset = 0) {

	$limit = 25;
	$beerLimit = 10;
	$offsetPage = $limit;

	// take one extra search result
	$limit++;

	// sanitize
	$query = array("query" => $query);
	sanitizeInput($query);
	$query = $query["query"];

	if (empty($location)) {
		// location has not been specified, so do a search of all 4 tables
		// first we do the regular fulltext search with relevance

		$sr = getInfo("searchTableAll", NULL, $query, FALSE, $limit, $offset);

		if (empty($sr)) {
			// no results were returned, so do a boolean search for incompletes instead
			// filter out existing asterisks
			$q = str_replace("*", "", $query);
			// now add our own asterisks and do query
			$q = str_replace(" ", "* ", $q) . "*";
			$sr = getInfo("searchTableAll", NULL, $q, TRUE, $limit, $offset);
			if ($sr)
				echo "<p>No full matches found.  Showing partial matches for: ";
		} else {
			// first search worked
			echo "<p>Showing all items matching: ";
		}
		echo '<strong>' . stripslashes(htmlspecialchars($query)) . "</strong></p>\n";
		$tc = array("score" => "Relevance", "tname" => "Type", "*" => "Name");
	} else {
		// location was specified
		// create array of relevant zip codes
		if (is_numeric($location)) {
			$zip = array($location);
		} else {
			$zip = array();
			if ($a = getInfo("getZip", $location)) {
				for($n = 0; $n < count($a); $n++) {
					$zip[] = $a[$n]["zip"];
				}
			}
		}

		if (empty($query))  {
			// bar in zip search (only zipcode was entered)
			echo "<p>Showing bars in ZIP code <strong>$location</strong>:</p>\n";
			$sr = getInfo("zipBar", $zip, $limit, $offset);
			$tc = array("distance" => "Distance", "*bar" => "Bar", "zip" => "ZIP");
		} else {
			// beer in zip search
			echo "<p>Showing beers in <strong>" . strtoupper($location) . "</strong> matching '<strong>" . stripslashes(htmlspecialchars($query)) . "</strong>' ";
			if ($tap) {
				echo "<strong>on tap</strong>:</p>\n<p><a href=\"/search/" . str_replace(" ", "_", $query . " in " . $location) . "\">(Show beers in all serving formats.)</a></p>\n";
			} else {
				echo "in <strong>all formats</strong>:</p>\n<p><a href=\"/search/tap/" . str_replace(" ", "_", $query . " in " . $location) . "\">(Show only on-tap beers.)</a></p>\n";
			}
			$a = getInfo("searchTable", "beer", $query, FALSE, $beerLimit, 0);
			// get highest-ranking beer
			echo "<p>Matching beers: ";
			// this is a hack
			if (empty($a)) {
				echo "None!";
			} else {
				echo "<strong> ";
				$b = array();
				$l = array();
				foreach ($a as $n) {
					$b[] = $n["id"];
					$l[] = $n["name"];
				}
				$l = implode(', ', $l);
				echo $l . "</strong>";
			}
			echo "</p>\n";

			$sr = getInfo("zipBeer", $zip, $b, $tap, $limit, $offset);
			$tc = array("distance" => "Distance", "*bar" => "Bar", "zip" => "ZIP", "*beer" => "Beer", "format" => "Format");
		}
		// this is a total hack
		if ($sr) {
			foreach($sr as $k => $a) {
				$sr[$k]["distance"] = $a["distance"] . " mi.";
			}
		}
	}

	if ($sr) {
		// display results table
		echo "<table class=\"standard\" id=\"searchResults\">\n";
		echo "<tr>";
		foreach ($tc as $a => $b) {
			echo "<th>" . $b . "</th>";
		}
		echo "</tr>\n";

		// don't show the last result if there are more
		for($n=0; ($n < count($sr)) and ($n < $limit - 1); $n++) {
			echo "<tr>";
			foreach ($tc as $a => $b) {
				echo "<td>";
				if (substr($a, 0, 1) == "*") {
					$a = substr($a, 1);
					$t = $a;
					if ($a == "")
						$t = $sr[$n]["tname"];
					echo '<a href="/' . $t . '/' . $sr[$n][$a . "id"] . '">' . stripslashes(htmlspecialchars($sr[$n][$a . "name"])) . '</a>';
				} else {
					echo stripslashes(htmlspecialchars($sr[$n][$a]));
				}
				echo "</td>";
			}
			echo "</tr>\n";
		}
		echo '<tr id="offset">';
		echo '<td>';
		if ($offset - $offsetPage >= 0) {
			echo '<a href="/search/' . currentSearchString($query, $location) . "/" . ($offset - $offsetPage) .  '">&lt;&lt;prev</a>';
		} else {
			echo '&nbsp;';
		}
		echo "</td>" . "<td colspan=" . (count($tc) - 2) . " >&nbsp;</td><td>";
		if (count($sr[$limit - 1]) > 0) {
			echo '<a href="/search/' . currentSearchString($query, $location) . "/" . ($offset + $offsetPage) .  '">next&gt;&gt;</a>';
		} else {
			echo "&nbsp;";
		}
		echo "</tr></table>";
	} else {
		echo "\n<div class=\"alert\">No results found!</div>\n";
	}
}

function displayMessages($gd) {
	global $user;

	if (is_numeric($gd[0])) {
		// show specific message
		$gd = intval($gd[0]);
		$msg = getInfo("message", $gd);
		$msg = $msg[0];

		if (!empty($msg) and ($msg["user"] == $user["num"])) {
			// display msg
			echo "<div id=\"message\">\n";
			echo '<p class="msgheader"><strong>Date: </strong>' . date("Y M j @ G:i T", $msg['time']) . "\n";
			echo '<br><strong>From: </strong>' . '<a href="/user/' . $msg['author'] . '">' . $msg['authorname'] . "</a>\n";
			echo '<br><strong>Subject: </strong>' . $msg['subject'] . "\n</p>\n<hr>\n\n";
			echo formatText("msgBody", $msg['body']);
			echo "</div>\n";
			// mark msg read if unread
			if ($msg['status'] == "unread")
				changeMsgStatus("r", $gd);
			echo "<hr><p><a href=\"/messages/\">Return to index</a></p>\n";
		} else {
			// invalid msg id
			echo "<p>Invalid message ID.</p>";
		}
	} else {
		switch ($gd[0]) {
			case "write":
				// form to write new msg
				echo '<div id="messageForm"><form method="POST" action="/messages/">' . "\n";
				echo '<input type="hidden" name="submitType" value="message">' . "\n";
				echo "<table>\n";
				echo '<tr><td>To:</td><td><select name="recipient" size=1>' . listOptions("user", 1, TRUE) . "</select></td></tr>\n";
				echo "<tr><td>Subject:</td><td><input type=\"text\" name=\"subject\"></td></tr>\n";
				echo '<tr><td>Message:</td><td><textarea name="message"></textarea>' . "</td></tr>\n";
				echo '<tr><td></td><td><input type="submit" value="Send message">' . "</td></tr>\n";
				echo "</table>\n";
				echo "</form></div>";
				break;
			default:
				// show message index
				echo '<p><a href="/messages/write">Send a new message</a></p>' . "\n";

				$ml = getInfo("messageList", $user["num"]);
				if (empty($ml)) {
					echo "<p>Your inbox is empty.</p>\n";
				} else {
					echo '<form method="POST" action="/messages/">' . "\n";
					echo '<input type="hidden" name="submitType" value="messageDelete">' . "\n";
					echo "<table class=\"standard\">\n";
					echo "<tr><th>delete</th><th>date</th><th>from</th><th>subject</th></tr>\n";
					foreach ($ml as $r) {
						echo "<tr" . ($r["status"] == "unread" ? ' class="unread"' : "") . '><td><input type="checkbox" name="messageDelete[]" value="' . $r["id"] . '"></td><td>' . date("j M g:ia", $r["time"]) . '</td><td><a href="/user/' . $r["author"] . '">' . $r["authorname"] . '</a></td><td><a href="/messages/' . $r["id"] . '">' . $r["subject"] . "</td></tr>\n";
					}
					echo '<tr><td colspan=4 id="submitbox"><input type="submit" value="Delete checked messages"></td></tr>' . "\n";
					echo "</table>\n";
				}
		}
	}
}

function currentSearchString($query, $location) {
	return str_replace(" ", "_", $query . ($location ? " in " . $location : ""));
}

function displayHelp($topic) {
	$topic = $topic[0];

	switch ($topic) {
		case "search":
			include("pages/help-search.php");
		break;
	default:
		?>
	<p>No help available on this topic.</p>
		<?php
	}
}

function printTable($a, $styleName = NULL, $cols = 1) {
		
	echo "\n<table class=\"standard\" " . ($styleName ? (' id="' . $styleName . '"') : "") . ">\n";

	$rows = ceil(count($a) / $cols);

	for($v = 0; $v < $rows; $v++) {
		echo "\t<tr>";
		for($h = 0; $h < $cols; $h++) {
			$p = $a[$v + ($rows * $h)];
			if ($p <> "") {
				echo "<td>" . $p . "</td>";
			}
		}
		echo "\t</tr>\n";
	}
	echo "</table>\n";
}

function addNew($t) {
	// simple function to spit out the Add New (beer, bar, &c.) link
	if (userAccess("editInfo")) {
		echo "<a href=\"/" . $t . "/0&amp;edit=yes\">[add new]</a><br>\n";
	}
}

function modInfo($table, $id, $data, $mod = FALSE) {
	// THIS SHIT IS COMPLIMACATED
	global $user;
	// does user have moderator access for the entry?
	$ua = (userAccess("moderate") or ($data["uid"] == $user["num"]) or (($table == "bar") and ($data["bar"] == $user["bar"])));

	// add [edit] link if necessary, shows last user update
	echo '<p class="update">';
	if (userAccess("editInfo") and !$mod) {
		echo '[<a href="/' . $table . '/' . $id . "/edit\">edit</a>] ";
		$a = getInfo("modInfo", $table, $id, TRUE, TRUE);
		$s = "";
		if (empty($a)) {
			echo "No mods";
		} else {
			$np = $a[0]["pending"];
			$s = $np . " mod" . ($np == 1 ? "" : "s");
		}
		if ($table == "bar") {
			if (!$s) {
				echo " and ";
			} else {
				$s .= " and ";
			}
			$a = getInfo("modInfo", "q_barbeer", $id, TRUE, TRUE);
			$np = $a[0]["pending"];
			$np = ($np ? $np : "no");
			$s .= $np . " beer" . ($np == 1 ? "" : "s");
		}
		if ($ua) {
			echo "<a href=\"/$table/$id/pending\">$s</a>";
		} else {
			echo $s;
		}
		echo " pending.";
	}

	echo '<br>Submitted by <strong><a href="/user/' . $data["uid"] . '">' . $data["uname"] . "</a></strong> on " . ($data["utime"] == 0 ? "[unknown]" : date("Y M j @ G:i T", $data["utime"]));	
	echo " " . ($data["ucomm"] == "" ? "(No comments.)" : ("- <i>" . $data["ucomm"] . "</i>"));
	if ($ua) {
		echo "<br>\n";
		echo 'Status: <strong>' . $data["status"] . "</strong>";
		if ($data["status"] <> "Pending") {
			echo ' by <strong><a href="/user/' . $data["mid"] . '">' . $data["mname"] . "</a></strong> on " . ($data["mtime"] == 0 ? "[unknown]" : date("Y M j @ G:i T", $data["mtime"]));	
			if ($data["mid"] == $data["uid"]) {
				echo " (<em>Auto-Moderation.</em>)";
			} else {
				echo " " . ($data["mcomm"] == "" ? "(No comments.)" : ("- <i>" . $data["mcomm"] . "</i>"));
			}
		}
	}
		echo "</p>\n";
}

function formatText($c, $d) {
	// formats text for presentation
	$t = "\n";
	if ($d <> "") {
		// remove consecutive line breaks
		$d = str_replace("\n\n", "\n", $d);
		// add paragraph tags
		$d = str_replace("\n", "</p>\n<p>", $d);
		$t .= '<div class="' . $c . '">';
		// add paragraph tags at beginning and end now
		$t .= "\n<p>" . $d . "</p>\n";
		$t .= "</div>\n";
	}
	return $t;
}

function listOptions($tableName, $defaultSelect, $doSort = TRUE) {
	$tc = ($tableName == "user" ? "login" : "name");
	// $tableName NEEDS TO BE TRUSTED
	if (in_array($tableName, array("format", "user"))) {
		$a = getInfo("doList", $tableName, $tc, $doSort);
	} else {
		$a = getInfo("doListR", $tableName, $tc, $doSort);
	}
	$t = "";
	foreach ($a as $r) {
		$t .= '<OPTION value="' . $r["id"] . '"';
		if ($r["id"] == $defaultSelect) {
			$t .= " SELECTED";
		}
		$t .= '>' . htmlspecialchars($r[$tc]) . "</OPTION>\n";
	}
	return $t;
}

function reportRows($tableName) {
	global $gFunc;
	$gc = (userAccess("genCodes") and ($tableName == "bar"));

	// I'm not sure why I originally did it this way
/*
	if ($tableName == "beer") {
		$q = getInfo("doUsedBeerList");
	} else { */
		$q = getInfo("doListR", $tableName, "name", TRUE);
/*	} */

	if (userAccess("submitNew")) {
		echo "<p>Are we missing something?  <a href=\"/" . $gFunc . "/addnew\">Add a new " . $tableName . ".</a></p>\n";
	}
	echo "<div class=\"itemlist\">\n";

	echo ($gc ? ('<form method="post" action="/gencodes/">' . "\n" . '<input type="hidden" name="submitType" value="genCodes">' . "\n") : "");
	
	foreach ($q as $l) {
		echo ($gc ? ('<input type="checkbox" name="codeBar[]" value = "' . $l["id"] . '">') : "");
		echo '<a href="/' . $tableName . "/" . $l["id"] . '">';
		if ($l['brewpub'] == 1) {
			echo '<b>' . $l['name'] . '</b>';
		} else {
			echo $l['name'];
		}
		echo "</a><br>\n";
	}

	echo ($gc ? ('<input type="submit" value="Generate codes for selected">' . "\n" . '</form>' . "\n") : "");
	echo "</div>\n";
}

function display404() {
	include("pages/404.php");
}

?>