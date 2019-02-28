#!/usr/bin/php
<?php
/* Sub-script of "calendrier.php". Builds an associative array with stunty rosters' name and associated picons
and put in .json file to be kept locally */

$rosters = array("20", "21", "22", "25", "26", "27", "28", "33", "34", "35", "36", "39", "40", "62", "63", "71"); # stunty rosters' list 


foreach ($rosters as $rosters) {
	
	$id = $rosters;
	$rosterURL = file_get_contents("https://fumbbl.com/api/roster/get/$id/xml") or die("erreur : ne peut recuperer fichier roster");
	$rosterXML = simplexml_load_string($rosterURL) or die("erreur : mauvais fichier roster");

	$name = "$rosterXML->name";
	$picon = array();
	
	$i = 0;
	foreach ($rosterXML->stars as $stars) {
		foreach ($stars->stars as $star) {
			
			$pos = $star['id'];

			$starURL = file_get_contents("https://fumbbl.com/api/position/get/$pos/xml") or die("erreur : ne peut recuperer fichier position");
			$starXML = simplexml_load_string($starURL) or die("erreur : mauvais fichier position");

			$picon[$i] = "$starXML->icon";
			$i += 1;

		}
	}
	
	foreach ($rosterXML->positions as $positions) {
		foreach ($positions->position as $position) {
			
			$pos = $position['id'];

			$posURL = file_get_contents("https://fumbbl.com/api/position/get/$pos/xml") or die("erreur : ne peut recuperer fichier position");
			$posXML = simplexml_load_string($posURL) or die("erreur : mauvais fichier position");

			$picon[$i] = "$posXML->icon";
			$i += 1;
		}
	}

	$piconArray["$name"] = $picon;
	unset($picon);
	unset($name);
}


$encodedString = json_encode($piconArray);
file_put_contents('piconJBM.json', $encodedString); # keep picon.JSON in the same folder as "calendrier.php"

?>
