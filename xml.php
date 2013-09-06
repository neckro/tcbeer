<?php

function XML_DisplayInfo($table, $r, $mod) {
	$r = intval($r);

	switch ($table) {
		case "bar":
			$a = getInfo("barInfo", $r, $mod);
			$a = $a[0];
			if (empty($a)) return FALSE;
			xml_prepare($a);
			echo '<bar id="' . $a["id"] . "\">\n";
			echo "\t<info>\n";
			echo "\t\t<name>" . $a["name"] . "</name>\n";
			echo "\t\t<address>" . $a["address"] . "</address>\n";
			// ***LOC: assuming minnesota again
			echo "\t\t<city>" . $a["city"] . "</city> <state>MN</state> <zip>" . $a["zip"] . "</zip>\n";
			echo "\t\t<lat>" . $a["lat"] . "</lat><lon>" . $a["lon"] . "</lon>\n";
			echo "\t\t<phone>" . $a["phone"] . "</phone>\n";
			echo "\t\t<url>" . $a["url"] . "</url>\n";
			echo ((intval($a["brewery"]) == 0) ? "" : ("\t<brewery id=\"" . $a["brewery"] . '"><name>' . $a["breweryname"] . "</name></brewery>\n"));
			echo "\t\t<description>\n" . $a["description"] . "\n\t\t</description>\n\n";
			echo "\t</info>\n";
			
			if ($mod) {
				$a = getInfo("modInfo", "q_barbeer", $r, TRUE, FALSE, FALSE, FALSE, TRUE);
			} else {
				$a = getInfo("barBeer", $r);
			}

			echo "\n\t<beerlist>\n";
			if (empty($a)) {
				echo "\t</beerlist>\n";
				echo "</bar>\n";
				return FALSE;
			}
			xml_prepare($a);
			for($n = 0; $n < count($a); $n++) {
				/*
				echo "\t\t<beer id=\"" . $a[$n]["beerid"] . "\">\n";
				echo "\t\t\t<name>" . $a[$n]["beername"] . "</name>\n";
				echo "\t\t\t<format id=\"" . $a[$n]["formatid"] . "\">" . $a[$n]["formatname"] . "</format>\n";
				echo "\t\t\t<brewery id=\"" . $a[$n]["breweryid"] . "\">\n";
				echo "\t\t\t\t<name>" . $a[$n]["breweryname"] . "</name>\n";
				echo ($a[$n]["brewerystate"] ? ("\t\t\t<state>" . $a[$n]["brewerystate"] . "</state>\n") : "");
				echo "\t\t\t\t<country>" . $a[$n]["brewerycountry"] . "</country>\n";
				echo "\t\t\t</brewery>\n";
				echo "\t\t\t<style id=\"" . $a[$n]["styleid"] . "\">" . $a[$n]["stylename"] . "</style>\n";
				echo "\t\t</beer>\n";
				*/
				echo "\t\t<beer ";
				// echo "id=\"" . $a[$n]["beerid"] . '" name="' . $a[$n]["beername"] . '"';
				foreach(array("beerid", "beername", "formatid", "formatname", "breweryid", "breweryname", "brewerystate", "brewerycountry", "styleid", "stylename") as $label) {
					echo " " . $label . '="' . $a[$n][$label] . '"';
				}
				echo " />\n";
				// echo "\t\t</beer>\n";
			}
			echo "\t</beerlist>\n";
			echo "\n</bar>\n";

			break;
			
		case "beer":
		case "style":
		case "brewery":
			$a = getInfo($table . "Info", $r, $mod);
			$a = $a[0];
			xml_prepare($a);
			echo "<" . $table . ' id="' . $a["id"] . "\">\n";
			echo "\t<name>" . $a["name"] . "</name>\n";
			
			
			break;
	}
	
	return TRUE;
}

function xml_prepare(&$array) {

	foreach($array as $key => $value) {
		if (is_array($value)) {
			xml_prepare($value);
		} else {
			// it's not necessary to encode to UTF8 right now, since the DB is currently using UTF8 natively
			// $value = utf8_encode($value);
			$value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
		}
	$array[$key] = $value;
	}
}

?>