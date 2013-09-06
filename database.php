<?php

connectToDB();

function grabQuery($r) {

	switch($r[0]) {
		
		// info queries
		case "barInfo":
			return "SELECT 
				r_bar.id,
				bar.bar, bar.name, bar.address, bar.city,
				bar.zip, bar.phone, bar.url, bar.description,
				bar.brewery, brewery.name AS breweryname,
				bar.lat, bar.lon,
				bar.user, bar.user AS uid, uuser.login AS uname,
				bar.moderator AS mid, muser.login AS mname,
				UNIX_TIMESTAMP(bar.stime) AS utime, UNIX_TIMESTAMP(bar.mtime) AS mtime,
				bar.ucomm, bar.mcomm, bar.status
			FROM r_bar
				INNER JOIN bar ON (r_bar.bar = bar.id)
				LEFT OUTER JOIN r_brewery ON (r_brewery.id = bar.brewery)
				LEFT OUTER JOIN brewery ON (brewery.id = r_brewery.brewery)
				LEFT OUTER JOIN user AS uuser ON uuser.id = bar.user
				LEFT OUTER JOIN user AS muser ON muser.id = bar.moderator 
			WHERE " . ($r[2] ? "bar" : "r_bar") . ".id = " . $r[1];
		case "beerInfo":
			return "SELECT
				beer.id, beer.beer, beer.name,
				beer.style AS styleid,
				style.name AS style,
				beer.brewery AS breweryid,
				brewery.name AS brewery,
				brewery.state,
				brewery.country,
				beer.description,
				beer.user AS uid,
				uuser.login AS uname,
				beer.moderator AS mid,
				muser.login AS mname,
				UNIX_TIMESTAMP(beer.stime) AS utime,
				UNIX_TIMESTAMP(beer.mtime) AS mtime,
				beer.ucomm, beer.mcomm,
				beer.status
			FROM " . ($r[2] ? "beer" : "r_beer INNER JOIN beer ON (r_beer.beer = beer.id)") . "
				INNER JOIN r_brewery ON r_brewery.id = beer.brewery
				INNER JOIN brewery ON r_brewery.brewery = brewery.id
				INNER JOIN r_style ON r_style.id = beer.style
				INNER JOIN style ON r_style.style = style.id 
				LEFT OUTER JOIN user AS uuser ON uuser.id = beer.user 
				LEFT OUTER JOIN user AS muser ON muser.id = beer.moderator 
			WHERE " . ($r[2] ? "beer" : "r_beer") . ".id = " . $r[1];
		case "breweryInfo":
			return "SELECT
				brewery.id, brewery.brewery,
				brewery.name, brewery.state,
				brewery.country, brewery.description,
				brewery.user AS uid,
				uuser.login AS uname,
				brewery.moderator AS mid,
				muser.login AS mname,
				UNIX_TIMESTAMP(brewery.stime) AS utime,
				UNIX_TIMESTAMP(brewery.mtime) AS mtime,
				brewery.ucomm, brewery.mcomm,
				brewery.status
			FROM " . ($r[2] ? "brewery" : "r_brewery INNER JOIN brewery ON (r_brewery.brewery = brewery.id)") . "
				LEFT OUTER JOIN user AS uuser ON uuser.id = brewery.user
				LEFT OUTER JOIN user AS muser ON muser.id = brewery.moderator
			WHERE " . ($r[2] ? "brewery" : "r_brewery") . ".id = " . $r[1];
		case "styleInfo":
			return "SELECT
				style.id, style.name,
				style.description,
				style.user AS uid,
				uuser.login AS uname,
				style.moderator AS mid,
				muser.login AS mname,
				UNIX_TIMESTAMP(style.stime) AS utime,
				UNIX_TIMESTAMP(style.mtime) AS mtime,
				style.ucomm, style.mcomm,
				style.status 
			FROM " . ($r[2] ? "style" : "r_style INNER JOIN style ON (r_style.style = style.id)") . "
				LEFT OUTER JOIN user AS uuser ON uuser.id = style.user
				LEFT OUTER JOIN user AS muser ON muser.id = style.moderator
			WHERE " . ($r[2] ? "style" : "r_style") . ".id = " . $r[1];
		case "barBeer":
			// fixed for mysql 5
			return "SELECT 
				barbeer.id AS bbid,
				r_beer.id AS beerid,
				beer.name AS beername, 
				format.id as formatid,
				format.name AS formatname,
				r_brewery.id AS breweryid,
				brewery.name AS breweryname,
				brewery.state AS brewerystate,
				brewery.country AS brewerycountry,
				r_style.id AS styleid,
				style.name AS stylename 
			FROM barbeer
				INNER JOIN format ON (barbeer.format = format.id)
				INNER JOIN r_beer ON (r_beer.id = barbeer.beer)
				INNER JOIN beer ON (r_beer.beer = beer.id)
				INNER JOIN r_brewery ON (r_brewery.id = beer.brewery)
				INNER JOIN brewery ON (r_brewery.brewery = brewery.id)
				INNER JOIN r_style ON (r_style.id = beer.style)
				INNER JOIN style ON (r_style.style = style.id)
			WHERE (barbeer.bar = " . $r[1] . ")
			ORDER BY barbeer.format DESC, beer.name";
		case "barBeerMod":
			return "SELECT
				q_barbeer.id,
				q_barbeer.type,
				q_barbeer.status,
				q_barbeer.barbeer,
				q_barbeer.bar AS barid,
				bar.name AS barname,
				q_barbeer.beer AS beerid,
				beer.name AS beername,
				format.name AS format,
				q_barbeer.user AS uid,
				uuser.login AS uname,
				q_barbeer.moderator AS mid, 
				muser.login AS mname,
				UNIX_TIMESTAMP(q_barbeer.stime) AS utime, 
				UNIX_TIMESTAMP(q_barbeer.mtime) AS mtime,
				q_barbeer.ucomm,
				q_barbeer.mcomm 
			FROM q_barbeer
				INNER JOIN r_bar ON (r_bar.id = q_barbeer.bar)
				INNER JOIN bar ON (r_bar.bar = bar.id)
				INNER JOIN r_beer ON (r_beer.id = q_barbeer.beer)
				INNER JOIN beer ON (r_beer.beer = beer.id)
				INNER JOIN format ON (format.id = q_barbeer.format)
				LEFT OUTER JOIN user AS uuser ON uuser.id = q_barbeer.user
				LEFT OUTER JOIN user AS muser ON muser.id = q_barbeer.moderator 
			WHERE q_barbeer.id = " . $r[1];
		case "beerBar":
			return "SELECT
				r_bar.id,
				bar.name,
				format.name AS format
			FROM barbeer
				INNER JOIN r_bar ON (r_bar.id = barbeer.bar)
				INNER JOIN bar ON (bar.id = r_bar.bar)
				INNER JOIN format ON (barbeer.format = format.id)
			WHERE (barbeer.beer = " . $r[1] . ") 
			ORDER BY barbeer.format DESC, bar.name";
		case "breweryBeer":
			return "SELECT
				r_beer.id,
				beer.name,
				beer.style,
				style.name AS sname
			FROM beer
				INNER JOIN r_beer ON (r_beer.beer = beer.id)
				INNER JOIN r_brewery ON (r_brewery.id = beer.brewery)
				INNER JOIN brewery ON (r_brewery.brewery = brewery.id)
				INNER JOIN r_style ON (r_style.id = beer.style)
				INNER JOIN style ON (r_style.style = style.id)
			WHERE (beer.brewery = " . $r[1] . ")
			ORDER BY beer.name";
		case "styleBeer":
			return "SELECT
				r_beer.id,
				beer.name,
				beer.brewery,
				brewery.name AS bname 
			FROM beer
				INNER JOIN r_beer ON (r_beer.beer = beer.id)
				INNER JOIN r_brewery ON (r_brewery.id = beer.brewery)
				INNER JOIN brewery ON (r_brewery.brewery = brewery.id)
				INNER JOIN r_style ON (r_style.id = beer.style)
				INNER JOIN style ON (r_style.style = style.id)
			WHERE (r_style.id = " . $r[1] . ")
			ORDER BY beer.name";
		case "getPendingModNumbers":
			return "(
				SELECT
					'bar    ' AS tname,
					COUNT(*) AS pending
				FROM bar
				WHERE status='Pending'
			) UNION (
				SELECT 'beer', COUNT(*)
				FROM beer 
				WHERE status='Pending'
			) UNION (
				SELECT 'brewery', COUNT(*)
				FROM brewery 
				WHERE status='Pending'
			) UNION (
				SELECT 'style', COUNT(*)
				FROM style
				WHERE status='Pending'
			) UNION (
				SELECT 'barbeer', COUNT(*)
				FROM q_barbeer 
				WHERE status='Pending'
			)";
		case "modInfo":
			// 1 = table name
			// 2 = array of row numbers; if FALSE then get all submissions
			// 3 = if TRUE then get modinfo for a specific entry/entries (r_id), else get mod.id ($t.id)
			// 4 = if TRUE then get aggregate data, else return all results
			// 5 = if TRUE then order by uid, status (as required by processMods()), else order by id
			// 6 = if TRUE then return all results, not just pending ones  (note that 2 & 6 together will return all)
			// 7 = if TRUE then return only phantom barbeer edits (for q_barbeer only); also order by beername (overrides previous parameters)
			$bb = ($r[1] == "q_barbeer");
			$t = $r[1];
			if ($r[2]) {
				// allows us to pass a single id instead of forcing an array
				if (is_numeric($r[2])) {
					$r[2] = array($r[2]);
				}
				$z = '"' . implode('", "', $r[2]) . '"';
			}
			$q = "SELECT
				\"$t\" as tname,
				$t.id AS id,
				$t.status,
				UNIX_TIMESTAMP($t.stime) AS stime,
				UNIX_TIMESTAMP($t.mtime) AS mtime,
				$t.user AS uid,
				uuser.login AS uname,
				$t.moderator AS mid,
				muser.login AS mname,
				$t.mcomm, $t.ucomm, 
				" . ($bb ? "
					$t.type, 
					$t.bar AS barid,
					bar.name AS barname,
					$t.beer AS beerid,
					beer.name AS beername,
					format.name AS format"
				: "
					t2.name AS curname,
					$t.name AS newname,
					$t.$t AS r_id
				") . ($r[4] ? ", COUNT(*) AS pending" : "") . "
			FROM $t
				" . ($bb ? " 
					LEFT OUTER JOIN format ON (format.id = q_barbeer.format)
					LEFT OUTER JOIN r_bar ON (r_bar.id = q_barbeer.bar)
					LEFT OUTER JOIN bar ON (bar.id = r_bar.bar)
					LEFT OUTER JOIN r_beer ON (r_beer.id = q_barbeer.beer)
					LEFT OUTER JOIN beer ON (beer.id = r_beer.beer)
				" : " 
					LEFT OUTER JOIN r_$t ON ($t.$t = r_$t.id)
					LEFT OUTER JOIN $t AS t2 ON (t2.id = r_$t.$t)
				") . "
				LEFT OUTER JOIN user AS uuser ON (uuser.id = $t.user)
				LEFT OUTER JOIN user AS muser ON (muser.id = $t.moderator)
				" . ($r[2] ? ($r[3] ? "WHERE " . ($bb ? ($r[7] ? "q_barbeer.bar" : "r_bar.id") : "r_$t.id") . " IN ($z)" : "WHERE $t.id IN ($z)") : "");
			$q .= ($r[6] ? "" : ($r[2] ? " AND " : "WHERE ") . "$t.status = " . ($r[7] ? "'Phantom'" : "'Pending'"));
			$q .= ($r[7] ? " ORDER BY beername" : ($r[4] ? " GROUP BY tname" : ($r[5] ? (" ORDER BY uid, status" . ($bb ? ", barid, type, id" : ", id")) : (" ORDER BY " . ($bb ? "barid, type, id" : "id")))));
			return $q;
			// backup copy of older query
			return "SELECT $t.id AS id, $t.status, $t.user AS uid, uuser.login AS uname, $t.moderator AS mid, muser.login AS mname, $t.mcomm, $t.ucomm, " . ($bb ? "$t.type, $t.bar AS barid, bar.name AS barname, $t.beer AS beerid, beer.name AS beername" : "t2.name AS tname, $t.$t AS r_id") . " FROM $t" . ($bb ? " LEFT OUTER JOIN r_bar ON r_bar.id = $t.bar LEFT OUTER JOIN bar ON bar.id = r_bar.bar LEFT OUTER JOIN r_beer ON r_beer.id = $t.beer LEFT OUTER JOIN beer ON beer.id = r_beer.beer" : " LEFT OUTER JOIN r_$t ON $t.$t = r_$t.id LEFT OUTER JOIN $t AS t2 ON t2.id = r_$t.$t") . " LEFT OUTER JOIN user AS uuser ON uuser.id = $t.user LEFT OUTER JOIN user AS muser ON muser.id = $t.moderator WHERE " . ($r[2] ? ("$t.id IN ($z) ORDER BY uid, status, " . ($bb ? "barid, type, id" : "id")) : ("$t.status = 'Pending' ORDER BY $t.id DESC"));

		// map queries
		case "geocodeList":
			// 1 = get all (approved) bars, else just get ones with no current geocoding
			return "SELECT 
				bar.id, 
				bar.lat, 
				bar.lon, 
				bar.address, 
				bar.zip, 
				zipcode.city, 
				zipcode.state 
			FROM bar, r_bar, zipcode
			WHERE r_bar.bar = bar.id
				AND " . ($r[1] ? "" : "(bar.lat IS NULL OR bar.lon IS NULL) AND ") . "zipcode.zip = bar.zip ORDER BY r_bar.id";
		// search queries
		case "searchTableBasic":
			// 1 = table
			// 2 = search phrase
			// 3 = boolean mode if TRUE
			$t = $r[1];
			$tl = $r[1] . "    ";
			return "SELECT
				'$tl' AS tname, 
				r_$t.id AS id, 
				$t.name AS name, 
				ROUND((MATCH($t.name, $t.description) AGAINST (\"" . $r[2] . '" ' . ($r[3] ? "IN BOOLEAN MODE" : "") . ")), 3) AS score
			FROM $t 
				INNER JOIN r_$t ON (r_$t.$t = $t.id)
			WHERE (MATCH(name, description) AGAINST (\"" . $r[2] . '"' . ($r[3] ? " IN BOOLEAN MODE" : "") . "))";
		case "searchTable":
			// 1 = table
			// 4 = result limit
			// 5 = result offset
			// this query uses "searchTableBasic" as its base, adds LIMIT and OFFSET at the end
			$n = grabQuery(array("searchTableBasic", $r[1], $r[2], $r[3])) . " ORDER BY score DESC, name " . ($r[4] ? ("LIMIT " . $r[4]) : "") . ($r[5] ? (" OFFSET " . $r[5]) : "");
			return $n;
		case "searchTableAll":
			// 1 = ignored
			// 4 = result limit
			// 5 = result offset
			// works the same as searchTable but searches all four main tables, uses searchTableBasic as its base
			$q = "(";
			foreach(array("bar", "beer", "brewery", "style") as $tn) {
				$q .= grabQuery(array("searchTableBasic", $tn, $r[2], $r[3])) . ") UNION (";
			}
			return substr($q, 0, strlen($q) - 8) . " ORDER BY score DESC, tname, name " . ($r[4] ? ("LIMIT " . $r[4]) : "") . ($r[5] ? (" OFFSET " . $r[5]) : "");			
		case "zipBar":
			// 1 = array of zip codes
			// 2 = result limit
			// 3 = offset
			$z = '"' . implode('", "', $r[1]) . '"';
			// ***LOC longitude distance calculation here only works for Twin Cities
			return "SELECT
				\"bar\" AS tname,
				r_bar.id AS barid,
				bar.name AS barname,
				bar.zip,
				ROUND(MIN(SQRT(POW(69.045 * (zip.lat-zipq.lat),2) + POW(49 * (zip.lon-zipq.lon),2))),1) AS distance
			FROM zipcode AS zip, zipcode AS zipq, r_bar 
				INNER JOIN bar ON (r_bar.bar = bar.id)
			WHERE (bar.zip = zip.zip)
				AND (zipq.zip IN (" . $z . ")) 
			GROUP BY barid ORDER BY distance, barname
			" . ($r[2] ? (" LIMIT " . $r[2]) : "") . ($r[3] ? (" OFFSET " . $r[3]) : "");
		case "zipBeer":
			// 1 = array of zip codes
			// 2 = beer to search for
			// 3 = on-tap only? (boolean)
			// 4 = result limit
			// 5 = offset
			$z = '"' . implode('", "', $r[1]) . '"';
			$b = '"' . implode('", "', $r[2]) . '"';
			// ***LOC longitude distance calculation here only works for Twin Cities
			return "SELECT
				\"bar\" AS tname,
				ROUND(MIN(SQRT(POW(69.045 * (zip.lat-zipq.lat),2) + POW(49 * (zip.lon-zipq.lon),2))),1) AS distance,
				r_bar.id AS barid, bar.name AS barname, bar.zip, r_beer.id AS beerid, beer.name AS beername, format.name AS format FROM zipcode AS zip, zipcode AS zipq, r_bar INNER JOIN bar ON (r_bar.bar = bar.id) INNER JOIN barbeer ON (barbeer.bar = r_bar.id) INNER JOIN r_beer ON (r_beer.id = barbeer.beer) INNER JOIN beer ON (r_beer.beer = beer.id) INNER JOIN format ON (format.id = barbeer.format) WHERE (bar.zip = zip.zip) AND zipq.zip IN (" . $z . ") AND barbeer.beer IN (" . $b . ") " . ($r[3] ? "AND barbeer.format = 180 " : "") . "GROUP BY barbeer.id ORDER BY distance, format.id DESC, barname, beername" . ($r[4] ? (" LIMIT " . $r[4]) : "") . ($r[5] ? (" OFFSET " . $r[5]) : "");
		case "getZip":
			$c = substr(strtoupper($r[1]), 0, 16);
			return "SELECT zip FROM zipcode WHERE city = \"$c\"";
		case "doList":
			return "SELECT id, " . $r[2] . " FROM " . $r[1] . " ORDER BY " . ($r[3] ? $r[2] : "id");
		case "doListR":
			$t = $r[1];
			$tc = $r[2];
			if ($t == "beer" and $r[3] == TRUE) {
				// this should probably be more generalized but for now we'll have a special query for beer lists
				return 'SELECT
					r_beer.id, 
					IF(brewery.brand IS NULL, beer.name, CONCAT(beer.name, " (", brewery.brand, ")")) AS name,
					brewery.brand 
				FROM beer
					INNER JOIN r_beer ON (r_beer.beer = beer.id)
					INNER JOIN r_brewery ON (r_brewery.id = beer.brewery)
					INNER JOIN brewery ON (r_brewery.brewery = brewery.id)
				WHERE (beer.brewery = r_brewery.id)
				UNION SELECT
					r_beer.id, 
					IF(brewery.brand IS NULL, beer.name, CONCAT(brewery.brand, " ", beer.name)) AS name,
					brewery.brand
				FROM beer
					INNER JOIN r_beer ON (r_beer.beer = beer.id)
					INNER JOIN r_brewery ON (r_brewery.id = beer.brewery)
					INNER JOIN brewery ON (r_brewery.brewery = brewery.id)
				WHERE (beer.brewery = r_brewery.id)
				ORDER BY name';
			} else {
				return "SELECT
					r_$t.id,
					$t.$tc
				FROM $t, r_$t
				WHERE r_$t.$t = $t.id
					AND (LEFT($t.$tc, 1) <> '-')
				ORDER BY " . ($r[3] ? $tc : "id");
			}
		case "doUsedBeerList":
			return "SELECT
				barbeer.beer AS id,
				beer.name, 	
				MAX(beer.brewery = brewery.id) AS brewpub
			FROM beer, r_beer, barbeer, bar
				INNER JOIN brewery ON (bar.brewery = brewery.id)
			WHERE (r_beer.beer = beer.id)
				AND (barbeer.beer = r_beer.id)
				AND (LEFT(beer.name, 1) <> '-')
			GROUP BY name
			ORDER BY name";
		case "getNames":
			$z = '"' . implode('", "', $r[2]) . '"';
			$t = $r[1];
			return "SELECT $t.id, $t.name FROM $t, r_$t WHERE r_$t.$t = $t.id AND r_$t.id IN (" . $z . ") ORDER BY name";
		
		// messaging queries
		case "newmsgs":
			return 'SELECT count(*) AS newmsgs from message WHERE (status = "unread") and (user = ' . $r[1] . ")";
		case "message":
			return 'SELECT UNIX_TIMESTAMP(message.time) AS time, message.author, message.user, user.login AS authorname, message.status, message.subject, message.body FROM message INNER JOIN user ON (user.id = message.author) WHERE message.id = ' . $r[1];
		case "messageList":
			return 'SELECT message.id, UNIX_TIMESTAMP(message.time) AS time, message.author, user.login AS authorname, message.status, message.subject FROM message INNER JOIN user ON (user.id = message.author) WHERE (message.status <> "deleted") AND message.user = ' . $r[1] . ' ORDER BY message.status, time DESC';
		case "messageAccess":
			$n = '"' . implode('", "', $r[1]) . '"';
			return 'SELECT id, user FROM message WHERE id in (' . $n . ')';
			
		// accesscode queries
		case "checkCode":
			return 'SELECT id, bar FROM accesscode WHERE (code = "' . $r[1] . '") AND (used IS NULL)';
		case "checkLoginName":
			return 'SELECT id FROM user WHERE (login = "' . $r[1] . '")';
		case "checkActivation":
			return 'SELECT id, login FROM user WHERE (activationCode = "' . $r[1] . '")';
			
		// forum queries
		case "fListForums":
			return "SELECT f_forum.id AS fid, f_forum.name AS fname, f_forum.description AS fdesc, (SELECT COUNT(id) FROM f_topic WHERE status = 'Visible' AND f_topic.forum = fid) AS ftopics, (SELECT COUNT(f_post.id) FROM f_topic, f_post WHERE f_post.topic = f_topic.id AND f_topic.forum = fid) AS fposts FROM f_forum WHERE f_forum.access <= " . $r[1] . " ORDER BY fid";
		case "fForumInfo":
			return "SELECT f_forum.id, f_forum.name, f_forum.description, f_forum.access" . ($r[2] ? ", f_topic.name AS topic" : "") . " FROM f_forum, f_topic WHERE " . ($r[2] ? ("f_topic.forum = f_forum.id AND f_topic.id = " . $r[2]) : ("f_forum.id = " . $r[1]));
		case "fListTopics":
			// 1 = forum number
			// 2 = user access
			return "SELECT f_topic.id AS tid, f_topic.name AS tname, COUNT(f_post.id) AS tposts, 'NYI' AS tnposts, (SELECT user FROM f_post, f_topic WHERE f_post.status = 'Visible' AND f_post.topic = tid ORDER BY f_post.stime ASC limit 1) AS fuid, (SELECT user.login FROM f_post, f_topic, user WHERE f_post.status = 'Visible' AND f_post.topic = tid AND f_post.user = user.id ORDER BY f_post.stime ASC limit 1) AS funame, UNIX_TIMESTAMP(MIN(f_post.stime)) AS futime, (SELECT user FROM f_post, f_topic WHERE f_post.status = 'Visible' AND f_post.topic = tid ORDER BY f_post.stime DESC limit 1) AS luid, (SELECT user.login FROM f_post, f_topic, user WHERE f_post.status = 'Visible' AND f_post.topic = tid AND f_post.user = user.id ORDER BY f_post.stime DESC limit 1) AS luname, UNIX_TIMESTAMP(MAX(f_post.stime)) AS lutime FROM f_topic, f_post WHERE (f_post.topic = f_topic.id) AND (f_post.status = 'Visible') AND (f_topic.forum = " . $r[1] . ") GROUP BY f_post.topic ORDER BY lutime DESC"; 
		case "fDispTopic":
			return "SELECT f_post.id AS pid, (SELECT f_topic.forum FROM f_topic, f_post WHERE f_post.id = pid AND f_post.topic = f_topic.id) AS fid, f_post.user AS uid, user.login AS uname, UNIX_TIMESTAMP(f_post.stime) AS stime, (SELECT COUNT(id) FROM f_post WHERE status='Visible' AND f_post.user = uid) AS uposts, f_post.content FROM f_post, user WHERE f_post.user = user.id AND f_post.status = 'Visible' AND f_post.topic = " . $r[1] . " ORDER BY stime";
			
		default:
			return 'SELECT "ERROR"';
	}
}

function getInfo() {
	global $testMode;
	$r = func_get_args();
	$q = grabQuery($r);
	
	if (userAccess("debug") and $testMode) {
		logMessage ('$r[0]=' . $r[0]);
		logMessage ('$r[1]=' . $r[1]);
		logMessage ('query= ' . $q);
	}
		
	
	if ($qresult = mysql_query($q)) {
		while ($d = mysql_fetch_array($qresult, MYSQL_ASSOC)) {
			$a[] = $d;
		}
	} else {
		logError ("getInfo: Query failed!");
		if (userAccess("debug")) {
			logMessage ("mysql_error= " . mysql_error());
			if (!$testMode) {
				logMessage ('$r[0]=' . $r[0]);
				logMessage ('$r[1]=' . $r[1]);
				logMessage ('query=  <pre><br>' . $q . "</pre></br>");
			}
		}
		return FALSE;
	}
	if ($a[0][0] == "ERROR") {
		logError ("getInfo: Query failed: " . $a[0][1]);
		mysql_free_result($qresult);
		return FALSE;
	}
	mysql_free_result($qresult);
	return $a;
}

function connectToDB() {
	global $dblink, $testMode;

	// set timezone for future stuff
	putenv("TZ=US/Central");

	// connect to db
	$dbhost = "tcbeer.decay.us";
	$dbuser = "tcbeer";
	$dbpass = base64_decode("djF0emVu");
	$dbbase = "tcbeer";

	// if this is a test installation, use different parameters
	if ($testMode) {
		$dbhost = "tcbeer2.decay.us";
		$dbbase = "tcbeer2";
	}

	$dblink = mysql_connect($dbhost, $dbuser, $dbpass) or die('Could not connect: ' . mysql_error());
	mysql_select_db($dbbase) or die('Could not select database');
}

function submitBarBeer($bar, $phantom = FALSE) {
	global $user;
	$bar += 0;

	if (!$bar) {
		logNotice("submitBarBeer: No bar specified!  Submissions not processed.");
		return FALSE;
	}

	$ucomm = $_POST['d_comm'];

	$ai = 0;
	$di = 0;

	// consolidate post data
	$data = array();

	if (!$phantom) {
		// grab barbeer data for DEL inserts
		$dels = array();
		if (!empty($_POST['beerDelete'])) {
			foreach($_POST['beerDelete'] as $n) {
				$dels[] = $n + 0;
			}
			if ($a = mysql_query("SELECT id, bar, beer, format FROM barbeer WHERE id IN (" . implode(', ', $dels) . ")")) {
				while ($n = mysql_fetch_array($a)) {
					$data[] = array("type" => "DEL", "barbeer" => $n["id"], "bar" => $n["bar"], "beer" => $n["beer"], "format" => $n["format"]);
				}
			} else {
				logError("submitBarBeer: dels query failed!");
				return FALSE;
			}
		}
	}

	// add data for ADD inserts
	for($n = 0; $n < count($_POST['beerAdd']); $n++) {
			if ($_POST['beerAdd'][$n] > 0) {
				$data[] = array("type" => "ADD", "bar" => $bar, "beer" => ($_POST['beerAdd'][$n] + 0), "format" => ($_POST['beerTap'][$n] + 0));
			}
	}

	$ai = 0;
	$di = 0;

	foreach($data as $d) {
		$query = 'INSERT INTO q_barbeer (stime, ' . ($phantom ? "status, " : "") . 'type, ' . ($d["type"] == "DEL" ? "barbeer, " : "") . 'bar, beer, format, user, ucomm) VALUES (NOW(), ' . ($phantom ? '"Phantom", ' : "") . '"' . $d['type'] . '", ' . ($d["type"] == "DEL" ? "\"{$d['barbeer']}\", " : "") . "\"{$d['bar']}\", \"{$d['beer']}\", \"{$d['format']}\", \"{$user['num']}\", \"$ucomm\")";
		if (mysql_query($query)) {
			if ($d["type"] == "ADD") {
				$ai++;
			} else {
				$di++;
			}
			// check for automod
			if (userAccess("automod") or ($user["bar"] == $bar)) {
				if (!approveMod("barbeer", mysql_insert_id(), TRUE, NULL)) {
					logError("submitBarBeer: approveMod() failed!");
					return FALSE;
				}
			}
		} else {
			logError("submitBarBeer: query failed: " . mysql_error());
		}
	}

	if (($ai + $di) > 0) {
		logMessage("$ai ADD inserts, $di DEL inserts successful.");
		if (!userAccess("automod")) {
			logMessage("You will be sent a message when these changes have been processed by a moderator.");
		}
	} else {
		logNotice("submitBarBeer: No data submitted!");
	}

	return TRUE;
}

function submitInfo ($table) {
	global $user, $gData, $tnames;
	$tn = $tnames[$table];
	// THIS FUNCTION HANDLES THESE TABLES: bar, beer, brewery, style
	// $table = table the data is to be inserted into
	// $sa = submit array

	// grab what row in r_* we wanna update, 0 = new entry
	$r_id = $gData[0] + 0;

	switch ($tn) {
		case "bar":
			$sa = array("user"=>$user['num'], "ucomm"=>$_POST['d_comm'], "bar"=>$r_id, "name"=>$_POST['d_name'], "address"=>$_POST['d_address'], "city"=>$_POST['d_city'], "zip"=>$_POST['d_zip'], "phone"=>$_POST['d_phone'], "description"=>$_POST['d_desc'], "url"=>$_POST['d_url'], "brewery"=>$_POST['d_brewery']);
			break;
		case "beer":
			$sa = array("user"=>$user['num'], "ucomm"=>$_POST['d_comm'], "beer"=>$r_id, "name"=>$_POST['d_name'], "style"=>$_POST['d_style'], "brewery"=>$_POST['d_brewery'], "description"=>$_POST['d_desc']);
			break;
		case "brewery":
			$sa = array("user"=>$user['num'], "ucomm"=>$_POST['d_comm'], "brewery"=>$r_id, "name"=>$_POST['d_name'], "state"=>$_POST['d_state'], "country"=>$_POST['d_country'], "brand"=>$_POST['d_brand'], "description"=>$_POST['d_desc']);
			break;
		case "style":
			$sa = array("user"=>$user['num'], "ucomm"=>$_POST['d_comm'], "style"=>$r_id, "name"=>$_POST['d_name'], "description"=>$_POST['d_desc']);
			break;
		default:
	}

	if ($sa["name"] == "") {
		logNotice("submitInfo: entry name is blank!  Please go back and resubmit.  If this problem persists, contact administration.");
		return FALSE;
	}

	// adding new entry
	// be sure to add stime (submission time)!!
	$qa = "INSERT INTO $tn (stime, ";
	$qb = ") VALUES (NOW(), ";
	// let's do this so we only have to go through the loop once
	foreach ($sa as $c => $d) {
		$qa .= $c . ", ";
		// ** SECURITY: if we're gonna do some sort of SQL injection protection, here would be the place to do it
		$qb .= "\"$d\", ";
	}
	// stripping off the last ", " here
	$query = substr($qa, 0, strlen($qa)-2) . substr($qb, 0, strlen($qb)-2) . ")";

	if ($qresult = mysql_query($query)) {
		$t_id = mysql_insert_id();
		logMessage("<a href=\"/mod/{$table}/{$t_id}\">Submission successful! {$tn}.id = {$t_id}</a>");
		
		// geocode if this is a bar entry
		// ***LOC "MN" is hardcoded here
		if ($tn == "bar") geocode($t_id, $sa["address"], $sa["city"], "MN", $sa["zip"]);
		
		// automod if user has the access
		// also automod if user has a bar owner account
		if (userAccess("automod") or (($table == 1) and ($user["bar"] == $r_id) and ($user["bar"] <> 0))) {
			approveMod($table, $t_id, TRUE, NULL);
		} else {
			logMessage("You will be notified when your submission is processed.");
			if ($table == 1 and $r_id == 0) {
				logMessage("Follow the moderation link above to add new beers to this bar while it is still pending.  If the bar is approved, these beers will be added to the new entry.");
			}
		}
		return TRUE;
	} else {
		logError("submitInfo: Query failed: " . mysql_error());
		return FALSE;
	}
}

function sendMessage($author, $recipient, $subject, $message, $sanitize = TRUE) {
	global $user;
	$data = array("author" => $author, "recipient" => $recipient, "subject" => $subject, "message" => $message);

	if (!$sanitize) {
		// if sanitize is off then we'll assume that the data came internally, e.g. from the moderation function
		// therefore we need to add escape slashes
		// IN FUTURE, we should turn off magic_quotes_gpc and just do all the slashing manually, will work out better
		$data["message"] = addslashes($data["message"]);
		$data["subject"] = addslashes($data["subject"]);
	}

	$q = "INSERT INTO message (time, author, user, subject, body) VALUES (NOW(), ";
	$q .= '"' . implode('", "', $data) . '")';

	if (mysql_query($q)) {
		logMessage("Message sent to user {$data['recipient']}");
	} else {
		logError("sendMessage: Message was not sent! ".  mysql_error());
	} 
}

function processSignup() {
	global $redirect;
	$d = array();
	$access = 10; // default access

	foreach (array("u_login", "u_name", "u_pass1", "u_pass2", "u_email", "u_emailDisp", "u_emailMsg", "u_emailNews", "u_zip", "u_accessCode") as $n) {
		$d[$n] = $_POST[$n];
	}
	
	sanitizeInput ($d);
	
	// process toggle options
	foreach(array("u_emailDisp", "u_emailMsg", "u_emailNews") as $n) {
		if ($d[$n] == "") {
			$d[$n] = 'no';
		} else {
			$d[$n] = 'yes';
		}
	}

	if ($d["accessCode"] <> "") {
		$a = getInfo("checkCode", $d["u_accessCode"]);
		$bar = intval($a[0]["bar"]);
		if ($bar == 0) {
			logError("The access code provided does not seem to be correct.  Please check the code, or leave the code field blank.");
			return FALSE;
		} else {
			$q = "UPDATE accesscode SET used = NOW() WHERE id = " . $a[0]["id"];
			if (!mysql_query($q)) {
				logError("processSignup: UPDATE accesscode failed!");
				return FALSE;
			}
		}
	} else {
		$bar = 0;
	}

	$a = getInfo("checkLoginName", $d["u_login"]);
	if (!empty($a)) {
		logError("The login '" . $d["u_login"] . "' has already been registered.  Please select a different one.");
		return FALSE;
	}
	if (strlen($d["u_login"]) > 12) {
		logError("Your login name is more than 12 characters long.  Please shorten it.");
		return FALSE;
	}
	if ($d["u_login"] == "") {
		logError("Please enter a login name.");
		return FALSE;
	}
	if ($d["u_pass1"] <> $d["u_pass2"]) {
		logtError("Your password entries did not match.  Please try again, making sure that you are entering the same password in both fields.");
		return FALSE;
	}
	if ($d["u_pass1"] == "") {
		logError("Please enter a password.");
		return FALSE;
	}
	if ($d["u_email"] == "") {
		logError("Please enter a valid email address.");
		return FALSE;
	}
	if ($d["u_name"] == "") {
		$d["name"] = $d["login"];
	}

	// generate activation code
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$code = "";
	for($n=0; $n<32; $n++) {
		$code .= $chars[rand(0, strlen($chars) - 1)];
	}
	$q = "INSERT INTO user (signup, access, activationCode, bar, login, pass, name, zip, email, emailDisp, emailMsg, emailNews) VALUES (NOW(), $access, \"$code\", " . ($bar > 0 ? $bar : "NULL") . ', "' . $d["u_login"] . '", "' . sha1($d["u_pass1"]) . '", "' . $d["u_name"] . '", "' . $d["u_zip"] . '", "' . $d["u_email"] . '", "' . $d["u_emailDisp"] . '", "' . $d["u_emailMsg"] . '", "' . $d["u_emailNews"] . '")';

	if (mysql_query($q)) {
		$subject = "TwinCitiesBeer.com Account Activation";
		$body = "    Thank you for registering on TwinCitiesBeer.com!\n\n    In order to activate your account, please click the link below:\nhttp://twincitiesbeer.com/activate/$code\n";
		$header = "From: TwinCitiesBeer Activation <activation@twincitiesbeer.com>\r\n";
		$header .= "Reply-To: admin@twincitiesbeer.com\r\n";
		$header .= "X-Mailer: PHP/" . phpversion() . "\r\n";
		if (mail($d["u_email"], $subject, $body, $header)) {
			$redirect = "/signup/success";
			return TRUE;
		} else {
			logError("processSignup: Mail delivery failed!");
		}
	} else {
		logError("processSignup: Query failed!");
		return FALSE;
	}
}

function processMods($table, $list, $approval, $mcomm) {
	global $user, $tnames;
	$tn = $tnames[$table];

	if (empty($list)) {
		logNotice("processMods: Submission was empty!");
		return FALSE;
	}
	
	$approvedList = array();

	// force all entries in $list to numeric types, for security
	foreach ($list as $n) {
		$n = intval($n);
	}
	// special case for tables 1 and 5, have to grab bar numbers first
	if ((($table == 1) or ($table == 5)) and !userAccess("moderate")) {
		$idl = '"' . implode('", "', $list) . '"';
		$query = "SELECT id, bar FROM $tn WHERE id IN ($idl)";
		$query = "SELECT id, bar FROM q_barbeer WHERE id in ($idl)";
		if ($qresult = mysql_query($query)) {
			while ($a = mysql_fetch_array($qresult)) {
				if ($user["bar"] == $a["bar"]) {
					if (approveMod($table, $a["id"], $approval, $mcomm)) {
						$approvedList[] = $m;
					}
				} else {
					logNotice("Moderation $table/$m : You do not have access to approve this moderation.");
				}
			}
		} else {
			logError("processMods: SELECT query for bar owner failed");
		}
	} else {
		if (userAccess("moderate")) {
			foreach ($list as $m) {
				// make $m numeric (again, just to be sure)
				$m += 0;
				if (approveMod($table, $m, $approval, $mcomm)) {
					$approvedList[] = $m;
				}
			}
		} else {
			logNotice("You do not have access to approve the submitted moderations.");
		}
	}

	if (empty($approvedList)) {
		logNotice("No moderations processed.");
		return FALSE;
	}

	$a = getInfo("modInfo", $tn, $approvedList, FALSE, FALSE, TRUE, TRUE);

	if (empty($a)) {
		logError("processMods: Moderation data empty!  Could not send notification message to user.  Moderations still processed as above.");
		return FALSE;
	}

	// send user message regarding moderations that were successful

	$footer = ($a[0]["mcomm"] ? "<p> Moderator comment: <i>{$a[0]['mcomm']}</i> </p>\n" : "");
	$footer .= "<p> If you have questions, you can contact the moderator directly or reply to this message. </p>\n";

	for ($n = 0; $n < count($a); $n++) {
		$changed = ($a[$n]["uid"] <> $a[$n-1]["uid"]) or ($a[$n]["status"] <> $a[$n-1]["status"]);
		if ($changed) {
			// send old message off if there is an old one
			if ($recipient) {
				sendMessage(1, $recipient, $subject, $body . $footer, FALSE);
			}
			// start new message if userid or status are different from previous row
			$recipient = $a[$n]["uid"];
			$subject = "Your moderation(s) have been " . ($a[$n]["status"] == "Denied" ? "denied" : "approved");
			$body = "<p> The following request(s) by you to " . ($table < 5 ? "edit information" : "add or remove beers") . " have been <b>" . ($approval ? "APPROVED" : "DENIED") . "</b> by moderator <a href=\"/user/{$a[$n]['mid']}\">{$a[$n]['mname']}</a>: </p>\n";
		}
		if ($table == 5) {
			$body .= "<div> <a href=\"/mod/5/{$a[$n]['id']}\">5/{$a[$n]['id']}</a> - " . ($a[$n]['type'] == "ADD" ? "Add" : "Delete") . " beer <b><a href=\"/beer/{$a[$n]['beerid']}\">{$a[$n]['beername']}</a></b> " . ($a[$n]['type'] == "ADD" ? "to" : "from") . " bar <b><a href=\"/bar/{$a[$n]['barid']}\">{$a[$n]['barname']}</a></b> </div>\n";
		} else {
			$body .= "<div> <a href=\"/mod/$table/{$a[$n]['id']}\">$table/{$a[$n]['id']}</a> - ";
			if ($a[$n]['r_id'] == 0) {
				$body .= "Adding new $tn";
			} else {
				$body .= "Changing information for " . $tn . " <b><a href=\"/$tn/{$a[$n]['r_id']}\">{$a[$n]['name']}</a></b>";
			}
			$body .= " </div>\n";
		}
		
	}
	// send off final message
	if ($recipient) {
		sendMessage(1, $recipient, $subject, $body . $footer, FALSE);
	}
}

function approveMod($table, $t_id, $approval, $mcomm) {
	global $user, $tnames;
	$tn = $tnames[$table];

	// CURRENTLY THIS FUNCTION ASSUMES THAT USER ACCESS HAS ALREADY BEEN TESTED

	// $table = table to be changed of course, now numeric
	// $t_id = id for row on mod table (if we're coming here from submitInfo for an automod, the newly inserted row)
	// $approval = TRUE if mod is approved; FALSE if denied
	// $mcomm = moderator comments; this is assumed to have already been sanitized
	// let's assume the numeric variables are not trusted for now so we'll force them to numeric
	$t_id += 0;

	switch ($table) {
		case "barbeer":
		case 5:
			// moderation for barbeer
			// this is going to be kinda ugly, maybe there's a way we can reduce the number of queries we do
			$query = "SELECT q_barbeer.id, q_barbeer.barbeer, q_barbeer.type, q_barbeer.bar, q_barbeer.beer, q_barbeer.status, q_barbeer.format FROM q_barbeer LEFT OUTER JOIN r_bar ON (r_bar.bar = q_barbeer.bar) WHERE q_barbeer.id = '" . $t_id . "'";
			if ($qresult = mysql_query($query)) {
				$a = mysql_fetch_array($qresult);
				if ($approval and $a['status'] == "Pending") {
					if ($a['type'] == "ADD") {
						$query = "INSERT INTO barbeer (bar, beer, format) VALUES (" . $a['bar'] . ', ' . $a['beer'] . ', ' . $a['format'] . ")";
						if (!mysql_query($query)) {
							logError("approveMod: barbeer insert failed! - " . mysql_error());
							return FALSE;
						}
					} elseif ($a['type'] == "DEL") {
						$query = "DELETE FROM barbeer WHERE id = " . $a['barbeer'];
						if (!mysql_query($query)) {
							logError("approveMod: barbeer delete failed! - " . mysql_error());
							return FALSE;
						}
					}
				}
				if (mysql_query("UPDATE q_barbeer SET mtime = NOW(), status = " . ($approval ? '"Approved"' : '"Denied"') . ", moderator = " . $user['num'] . ', mcomm = "' . $mcomm . '" WHERE id = ' . $t_id)) {
					logMessage("approveMod: barbeer mod successful for queue submission " . $t_id);
					return TRUE;
				} else {
					logError("approveMod: q_barbeer update failed! - " . mysql_error());
					return FALSE;
				}
			} else {
				logError("approveMod: q_barbeer select failed!");
				return FALSE;
			}
			break;
		case "style":
		case 4:
		case "brewery":
		case 3:
		case "beer":
		case 2:
		case "bar":
		case 1:
			// moderation for bar, beer, brewery, style
			if ($approval) {
				// get row that this mod was intended for, 0 == new
				if ($qresult = mysql_query("SELECT $tn.$tn AS rid FROM $tn WHERE id = " . $t_id)) {
					$a = mysql_fetch_array($qresult);
					$r_id = $a["rid"];
					$o_id = $r_id;   // old r_id for checking later
				} else {
					logError("approveMod: r_id retrieval failed! - " . mysql_error());
					return FALSE;
				}
				if ($r_id == 0) {
					// check for an unused entry; if one exists then set $r_id to its id
					$query = "SELECT r_$tn.id  FROM r_$tn, $tn WHERE (r_$tn.$tn = $tn.id) AND (LEFT($tn.name, 1) = '-') LIMIT 1";
					if ($qresult = mysql_query($query)) {
						$a = mysql_fetch_array($qresult);
						if (($a['id'] + 0) <> 0) {
							$r_id = $a['id'];
							logMessage("Entry #{$r_id} is unused; new entry will replace it.");
						}
					} else {
						// we don't really care if this query failed, so log an error but otherwise do nothing
						logError("approveMod: check for unused entries failed (non-fatal)");
					}
				}
				if ($r_id == 0) {
					// this is a new entry
					if (mysql_query("INSERT INTO r_$tn ($tn) VALUES ($t_id)")) {
						$r_id = mysql_insert_id();
					} else {
						logError("approveMod: r_$tn insert failed! - " . mysql_error());
						return FALSE;
					}
				} else {
					// updating existing entry
					if (!mysql_query("UPDATE r_$tn SET $tn=$t_id WHERE id = $r_id")) {
						logError("approveMod: r_$tn update failed! - " . mysql_error());
						return FALSE;
					}
				}
			}
			$ad = ($approval ? '"Approved"' : '"Denied"');
			$q = "UPDATE $tn SET status = $ad, moderator = \"${user['num']}\", mtime = NOW(), mcomm = \"$mcomm\", $tn = \"$r_id\" WHERE id = $t_id";
			if (mysql_query($q)) { 
				if ($approval) {
					logMessage("Moderation <a href=\"/mod/$table/$t_id\">$t_id</a> for " . ($r_id == $o_id ? "new " : "") . "$tn entry " . ($r_id ? "<a href=\"/$tn/$r_id\">$r_id</a>" : "$r_id") . " processed - ($ad)");
				} else {
					logMessage("Moderation <a href=\"/mod/$table/$t_id\">$t_id</a> processed - ($ad)");
				}
				return TRUE;
			} else {
				logError("approveMod: $tn update failed!" . mysql_error());
				logMessage($q);
				return FALSE;
			}
			break;
		default:
	}
	return FALSE;
}

function changeMsgStatus($status, $id) {
	if (is_array($id)) {
		$id = '"' . implode('", "', $id) . '"';
	}
	switch ($status) {
		case "d":
			$s = "deleted";
			$ct = FALSE;
			break;
		case "r":
			$s = "read";
			$ct = TRUE;
			break;
		case "u":
			$s = "unread";
			$ct = FALSE;
			break;
		default:
			return FALSE;
	}
	$q = "UPDATE message SET status=\"$s\"" . ($ct ? ", readtime = NOW()" : "") . " WHERE id IN ($id)";

	if (mysql_query($q)) {
		return TRUE;
	} else {
		logError("changeMsgStatus: UPDATE for rows " . $id . " failed! " . mysql_error());
		return FALSE;
	}
}

function submitMessageDelete() {
	global $user;

	if (userAccess("deleteUserMessages")) {
		// if user is a mod, delete whatever the hell they want
		$valid = $_POST["messageDelete"];
	} else {
		// check to make sure we're only deleting the current user's msgs
		$valid = array();
		$a = getInfo("messageAccess", $_POST["messageDelete"]);
		foreach ($a as $r) {
			if ($r["user"] = $user["num"])
				$valid[] = $r["id"];
		}
	}

	if (count($valid) > 0) {
		if (changeMsgStatus("d", $valid)) {
			logMessage(count($valid) . " message" . (count($valid) > 1 ? "s" : "") . " moved to Trash.");
		} else {
			logError("submitMessageDelete: Deletion failed!");
		}
	} else {
		logMessage("No messages to delete!");
	}
}

function authenticateLogin($st, $u, $p) {
	global $user;

	$user = FALSE;
	$domain = ".twincitiesbeer.com";
	
	// if the user manually logged in that takes precedence
	if ($st == "login") {
		$u = $_POST['username'];
		$p = sha1($p);
		$sc = TRUE;
	} elseif ($st == "logout") {
		// clear the cookies
		setcookie("username", "", (time() - 3600), "/", $domain, FALSE);
		setcookie("password", "", (time() - 3600), "/", $domain, FALSE);
		return FALSE;
	} elseif (isset($_COOKIE['username'])) {
		$u = $_COOKIE['username'];
		$p = $_COOKIE['password'];
		$sc = FALSE;
	} else {
		return FALSE;
	}

	$q = mysql_query("SELECT id AS num, access, bar FROM user WHERE login = \"$u\" AND pass = \"$p\"");
	if (mysql_num_rows($q) == 1) {
		if ($sc) {
			// set cookie if it isn't already set
			// 2 weeks from the current date
			$expire = time() + 86400 * 14;
			setcookie("username", $u, $expire, "/", $domain, FALSE);
			setcookie("password", $p, $expire, "/", $domain, FALSE);
		}
		$a = mysql_fetch_array($q, MYSQL_ASSOC);
		$user = array("num" => $a["num"], "name" => $u, "access" => $a["access"], "bar" => $a["bar"]);
		return TRUE;
	} else {
		return FALSE;
	}
}

function sanitizeInput(&$input) {

	$output = array();

	foreach ($input as $k => $d) {
		
		if (!is_numeric($d)) trim($d);

		if (in_array($k, array("submitType", "query", "d_name", "d_city", "d_country", "d_address", "d_brand", "m_subject", "username", "m_subject", "u_login", "u_name"))) {
			// strip everything except alphanumeric, single line
			// strip all html
			$d = preg_replace('/<.*>/', "", $d);
			// strip all newlines
			$d = preg_replace('/\n/', " ", $d);
			// finally, strip extra spaces and tabs
			$d = preg_replace('/([ ]|\t)+/', " ", $d);
		} elseif (in_array($k, array("d_desc", "d_comm", "mod_comm", "m_message"))) {
			// multi-line fields
			// strip all html
			$d = preg_replace('/<.*>/', "", $d);
			// strip duplicate newlines
			$d = preg_replace('/\s*\n\s*/', "\n", $d);
			// finally, strip extra spaces and tabs
			$d = preg_replace('/([ ]|\t)+/', " ", $d);
		} elseif (in_array($k, array("d_phone"))) {
			$d = substr(preg_replace('/\D/', "", $d), 0, 10);
		} elseif (in_array($k, array("d_state"))) {
			$d = strtoupper(substr(preg_replace('/[^A-Za-z]/', "", $d), 0, 2));
		} elseif (in_array($k, array("d_zip", "u_zip"))) {
			$d = substr(preg_replace('/\D/', "", $d), 0, 5);
		} elseif (in_array($k, array("u_email"))) {
			$d = preg_replace('/[^-a-zA-Z0-9_@+.]/', "", $d);
			// ** FIXME: final check removed to avoid warning for third argument
			//preg_match('/.+\+?.*@.+\..+/', $d, &$e);
			//$d = $e[0];
		} elseif (in_array($k, array("d_url"))) {
			// ** SECURITY: TOSS IN URL CHECKING HERE
		} elseif (in_array($k, array("u_login", "u_accesscode"))) {
			$d = strtolower($d);
			$d = preg_replace('/[^-a-zA-Z0-9_]/', "", $d);
		} elseif (in_array($k, array("password", "u_pass1", "u_pass2", "u_emailDisp", "u_emailMsg", "u_emailNews"))) {
			// exempt from sanitization -- BE VERY CAREFUL WITH THESE
		} elseif ($k == "mod_approval") {
			$d = ($d == "Approve");
		} else {
			// force to numeric
			// walk through the array if it exists
			if (is_array($d)) {
				foreach ($d as $ka => $da) {
					$da = intval($da);
				}
			} else {
				$d = intval($d);
			}
		}
		if (!is_numeric($d)) trim($d);
		$output[$k] = $d;
	}
	$input = $output;
}

function userAccess($action) {
	global $user;
	$a = $user["access"];

	switch ($action) {
		case "userSignup":
		case "login":
		case "logout":
			return TRUE;
		case "beer":
		case "bar":
		case "brewery":
		case "style":
		case "beerChange":
		case "editInfo":
		case "message":
		case "messageDelete":
		case "submitNew":
			if ($a >= 20) return TRUE;
		case "automod":
		case "moderate":
		case "modProcess":
			if ($a >= 50) return TRUE;
		case "deleteUserMessages":
		case "genCodes":
		case "debug":
			if ($a >= 90) return TRUE;
	}
	return FALSE;
}

?>
