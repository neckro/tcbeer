<?php

define("MAPS_KEY", "ABQIAAAAobOWjivgb0YnMisU48sSJxSySskyNaf03oUo0FyhBdNXxHld6xQW7uLCLQpQ6EZ0iKuKc31jCwpXVw");
define("MAPS_URL", "http://maps.google.com/maps/geo?output=csv&key=" . MAPS_KEY);
define("MAPS_RETRIES", "20");
define("MAPS_SLEEPTIME", "100000");

function geocode($gcBar, $gcAddress, $gcCity, $gcState, $gcZip) {

	$pending = TRUE;
	$addressString = $gcAddress . ", " . $gcCity . " " . $gcState . " " . $gcZip;
	$retries = 0;
	while($pending and $retries < MAPS_RETRIES) {
		$url = (MAPS_URL . "&q=" . urlencode($addressString));
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$csvData = curl_exec($ch);
		curl_close($ch);
		// the preceding curl nonsense is to get around Dreamhost restrictions, else the line would just be:
		// $csvData = file_get_contents($url);
		$csvSplit = split(",", $csvData);
		$dStatus = $csvSplit[0];
		$lat = $csvSplit[2];
		$lon = $csvSplit[3];
		if ($dStatus == "200") {
			// success
			$pending = FALSE;
			$query = "UPDATE bar SET lat = " . $lat . ", lon = " . $lon . " WHERE id = " . $gcBar;
			if (mysql_query($query)) {
				logMessage("geocode successful for id " . $gcBar);
			} else {
				logError("geocode: UPDATE query failed for id " . $gcBar . "!");
			}
		} elseif ($dStatus == "620") {
			// delay and try again
			usleep(MAPS_SLEEPTIME);
			$retries++;
		} else {
			$pending = FALSE;
			logError("Geocode failed - status " . ($status ? $status : "n/a") . " for string '" . $addressString . "'");
		}
	}
}

function geocodeBars($codeAll = FALSE) {

	$bars = getInfo("geocodeList", $codeAll);
	
	for ($a = 0; $a < count($bars); $a++) {
		geocode($bars[$a]["id"], $bars[$a]["address"], $bars[$a]["city"], $bars[$a]["state"], $bars[$a]["zip"]);
	}		
}	


?>