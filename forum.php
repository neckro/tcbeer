<?php

// forum functions

function fListForums() {
	global $user;
	
	$a = getInfo("fListForums", $user["access"]);
	
	if (empty($a)) {
		logNotice("You do not have access to any forums.");
	} else {
		echo "<table class=\"forum\" cellspacing=0>\n";
		echo "<th>#</th><th>name / description</th><th>topics</th><th>posts</th></tr>\n";
		foreach($a as $d) {
			echo "<tr><td>{$d['fid']}</td><td><a href=\"/forum/{$d['fid']}\">{$d['fname']}</a>";
			echo '<br><div class="fDesc">' . $d["fdesc"] . "</div></td>";
			echo "<td>{$d['ftopics']}</td><td>{$d['fposts']}</td>";
			echo "<tr>\n";
		}
		echo "</table>\n";
	}
}

function fDispForum($fid) {

	if (!fForumHeader($fid)) {
		return FALSE;
	}

	$a = getInfo("fListTopics", $fid);
	if (empty($a)) {
		logNotice("There are no messages in this forum.");
	} else {
		echo "<table class=\"forum\" cellspacing=0>\n";
		echo "<th>posts</th><th>topic</th><th>new<br>posts</th><th>author</th><th>last<br>post</th></tr>\n";
		foreach($a as $d) {
			$d['lutime'] = parseDate($d['lutime']);
			echo "<tr><td class=\"lg\">" . $d["tposts"] . "</td><td class=\"lg\"><a href=\"/forum/topic/" . $d["tid"] . '">' . $d["tname"] . '</a></td>';
			echo "<td class=\"lg\">{$d['tnposts']}</td>";
			echo "<td><div><a href=\"/user/{$d['fuid']}\">{$d['funame']}</a></div></td>";
			echo "<td><div>{$d['lutime']} by <a href=\"/user/{$d['luid']}\">{$d['luname']}</a></div></td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}

function parseDate($t) {
	// get time difference
	$td = time() - $t;
	if (date("Ymd") == date("Ymd", $t)) {
		// today?
		$tt = "Today @ " . date("g:ia", $t);
	} elseif (date("Ymd", $t) == date("Ymd", time() - 86400)) {
		// yesterday?
		$tt = "Yesterday @ " . date("g:ia", $t);
	} elseif ($td < 4320000) {
		// in the last 5 days?
		$tt = date("l @ g:ia", $t);
	} elseif ($td < 15724800) {
		// in the last 6 months?
		$tt = date("M j @ g:ia", $t);
	} else {
		// > 6 months ago?
		$tt = date("Y M j @ g:ia", $t);
	}
	return $tt;
}

function fForumHeader($fid, $tid = NULL) {
	global $user;
	
	$f = getInfo("fForumInfo", $fid, $tid);
	$f = $f[0];
	
	if ($user['access'] < $f['access']) {
		logNotice("You do not have permission to view this forum.");
		return FALSE;
	}
	if (empty($f)) {
		logNotice("Invalid forum number!");
		return FALSE;
	}
	echo "<table class=\"forum\"><tr><td><h1>{$f['name']}</h1>";
	if ($tid) {
		echo "</td></tr></table>\n";
		return $f['topic'];
	} else {
		echo "<div>{$f['description']}</div></td></tr></table>\n";
		return TRUE;
	}
}

function fDispTopic($tid) {
	
	if (!$topic = fForumHeader(NULL, $tid)) {
		return FALSE;
	}

	$a = getInfo("fDispTopic", $tid);
	if (empty($a)) {
		logNotice("Invalid topic!");
		return FALSE;
	}

	echo "<table class=\"forum\" cellspacing=0>\n";
	echo "<tr><td colspan=2><h2>Topic: $topic</h2></td></tr>\n";
	foreach($a AS $d) {
		$stime = parseDate($d['stime']);
		echo "<tr><td><div class=\"lg\"><a href=\"/user/{$d['uid']}\">{$d['uname']}</a><br>$stime</div>Posts: {$d['uposts']}<br>";
		echo "<img width=100 height=100 src=\"/i/avatars/{$d['uid']}\"></td>";
		echo "<td>{$d['content']}</td></tr>\n";
	}
		echo "</table>\n";
}

?>