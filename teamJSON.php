#!/usr/bin/php

<?php
// calendrier.php sub-script to be used at the beginning of each season and when new teams are added to the group


$groupid = "9761";

$groupURL = file_get_contents("https://fumbbl.com/xml:group?id=$groupid&op=members") or die("erreur : ne peut recuperer le fichier groupe");
$groupXML = simplexml_load_string($groupURL) or die("erreur : mauvais fichier groupe");

$team = array();

foreach ($groupXML->members as $members) {
	
	foreach ($members->team as $teamid) {
		
		$id = current($teamid['id']);
		$team["$id"]['id'] = $id;

		$teamURL = file_get_contents("https://fumbbl.com/api/team/get/$id/xml") or die("erreur : ne peut recuperer le fichier equipe");
		$teamXML = simplexml_load_string($teamURL) or die("erreur : mauvais fichier equipe");

		$team["$id"]['name'] = trim(preg_replace('/(\[.*\])|(\(.*\))/', '', $teamXML->name));  # team's name without [*] or (*)
		$tcoach = $teamXML->coach->name;
		
		switch ("$tcoach") {
			case "Grumeau_leMalpropre" :
				$team["$id"]['coach'] = "Grumeau";
				break;
			case "RayMontador" :
				$team["$id"]['coach'] = "RayMo";
				break;
			case "AZALNUBIZAR" :
				$team["$id"]['coach'] = "Azal";
				break;
			case "muaddib68" :
				$team["$id"]['coach'] = "Muad";
				break;
			case "Hathi91" :
				$team["$id"]['coach'] = "Hathi";
				break;
			case "LonGusBarbe" :
				$team["$id"]['coach'] = "LonGus";
				break;
			case "GotrekFBB" :
				$team["$id"]['coach'] = "Gotrek";
				break;
			case "krom72" :
				$team["$id"]['coach'] = "Krom";
				break;
			default :
				$team["$id"]['coach'] = "$tcoach";
		}

		$turl = $teamXML->coach->name;
		$team["$id"]['coachURL'] = "$turl";
		$troster = $teamXML->roster['id'];
		$team["$id"]['rosterID'] = "$troster";
		$rostername = $teamXML->roster->name;
		$team["$id"]['rosterName'] = "$rostername";
	}
}

$encodedString = json_encode($team);
file_put_contents('teamJBM.json', $encodedString); # to be kept in same folder as calendrier.php

?>
