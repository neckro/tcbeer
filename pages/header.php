<script language="javascript" type="text/javascript">
<!--
	// google adsense stuff
	google_ad_client = "pub-4270514752784812";
	google_alternate_ad_url = "http://www.twincitiesbeer.com/i/misc/adsense.html";
	google_ad_width = 100;
	google_ad_height = 600;
	google_ad_format = "120x600_as";
	google_ad_type = "text";
	google_ad_channel ="";
	google_color_border = "CCCCCC";
	google_color_bg = "FFFFFF";
	google_color_link = "000000";
	google_color_url = "666666";
	google_color_text = "333333";

	// blatantly ripped off from a9
	function submitSearch(form) {
		var bq = form.bq.value;
		if (bq) {
			if (typeof(encodeURIComponent) != "undefined") {
				value = encodeURIComponent(bq);
			} else {
			bq = escape(bq);
			}
		bq = bq.replace(/\ /g, "_");
		location.href = "/search/" + bq;
		}
	  return false;
	}
//-->
</script>

</head>

<body>

	<table id="pageLayout" cellpadding=0 cellspacing=0>
		<tr>
		<td id="headerLogin" colspan=3>
			<div id="headerLoginContent">
			<?php if ($user) {
				$nm = getInfo("newmsgs", $user["num"]);
				$nm = $nm[0]["newmsgs"];
				?>
				<form action="" method="post"><input type="hidden" name="submitType" value="logout">
				<div>You are logged in as: <strong><?= $user["name"] ?></strong> <input type="submit" class="button" value="Logout"></form></div>
				<div>You have <?php
				if ($nm == 0) {
						echo "no new messages";
				} else {
					echo '<a href="/messages/">' . $nm . ' new message' . ($nm > 1 ? "s" : "") . "</a>";
				}
				?> and no new forum replies.</div>
			<?php } else {
					if ($loginFail) { ?>
				<div><strong>Login Failed!</strong> Please verify that your username and password are correct.<br>
				<a href="/signup/">Sign up here</a> if you don't have an account yet.</div>
				<?php } else { ?>
					<div>You are not currently logged in.<br>
					Don't have an account? <a href="/signup/">Sign up</a> to join the TwinCitiesBeer.com community!</div>
				<?php } ?>
			<form action="<?= $curPath ?>" method="post">
			<input type="hidden" name="submitType" value="login">
					<div>username: <input type="text" name="username">
					password: <input type="password" name="password">
					<input type="submit" class="button" value="Login"></div>
			</form>
			<?php } ?>
			</div>
		</td>
		</tr>
		<tr>
		<td id="searchBar" colspan=3 valign="top">
			<div id="searchBarContent">
			<form action="/search/" method="get" onsubmit="return submitSearch(this)">
				<a href="/help/search">Search Help</a> 
				<input type="text" name="bq">
				<input type="submit" class="button" value="Search">
			</form>
			</div>
		</td>
		</tr>
		<tr>
		<td id="leftBar">
		<div id="leftBarContent">
			<!-- <div><a href="/">Home</a></div> -->
			<div><a href="/blog/">Beer Blog</a></div>
			<hr>
			<div><a href="/bar/">Bar Index</a></div>
			<div><a href="/beer/">Beer Index</a></div>
			<div><a href="/brewery/">Brewery Index</a></div>
			<div><a href="/style/">Style Index</a></div>
			<hr>
			<?php if ($user) { ?>
			<div><a href="/forum/">Forums</a></div>
			<?php if (userAccess("moderate")) { ?>
			<hr>
			<div><a href="/mod/">Moderate Submissions</a></div>
			<?php } ?>
			<hr>
			<div><a href="/prefs/">Your Account</a></div>
			<div><a href="/messages/">Read/Send Messages</a></div>
			<hr>
			<?php } ?>
			<!-- <div><a href="/about/">About Us</a></div> -->
		</div>
		</td>
		<td id="prime">
		<div id="primeContent">
