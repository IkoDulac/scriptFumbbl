#!/usr/bin/php
<?php
/* Builds a BBcode calender using "https://fumbbl.com/api/tournament/schedule/#####", "https://fumbbl.com/xml:group?id=####&op=members",
and "https://fumbbl.com/api/roster/get/##/xml".
script was writen by Grumeau in feb 2019 to help with 'JBM' group maintenance */

$date = date_create("2019/03/10");		# first deadline
$nbMatch= 7; 							            # match per round, regular season
$nbJour = 13;							            # numbre of round, regular season
$nomTounois = "Championnat JBM VII";	# names for bbcode
$nomCoupe = "Coupe JBM VII";			    #
$nomPO = "Phases finales JBM VII";		#

$teamContents = file_get_contents('teamJBM.json');		# containing info on the group's teams
$team = json_decode($teamContents, true);				      # build array from local teamJBM.json (update with teamJBM.php script when new teams are added)
$piconContents = file_get_contents('piconJBM.json');	# containing a list of picons to be accessed with array_random function
$piconArray = json_decode($piconContents, true);		  # build array from local piconJBM.json (file is writen with piconJBM.php script)


$saisonURL = file_get_contents("https://fumbbl.com/api/tournament/schedule/46463");		# last five digits are tournament ID)
// $saisonURL = file_get_contents("saison.txt"); 										                  # optional line to use if schedule files are stored locally
$scheduleSaison = json_decode($saisonURL);												                    # format : object
$coupeURL = file_get_contents("https://fumbbl.com/api/tournament/schedule/46464");		#
//$coupeURL = file_get_contents("coupe.txt");											                    #
$scheduleCoupe = json_decode($coupeURL);												                      #
$playoffURL = file_get_contents("https://fumbbl.com/api/tournament/schedule/46465");	#
//$playoffURL = file_get_contents("PO.txt");											                    #
$schedulePO = json_decode($playoffURL);													                      #



// $strings for bbcode headings
function strFinale($rnd){
	switch ($rnd) {
		case '1':
			$string = "huitième de finale";
			break;
		case '2':
			$string = "quart de finale";
			break;
		case '3':
			$string = "demie finale";
			break;
		case '4':
			$string = "finale";
			break;	
		default:
			$string ="";
			break;
		}
		return $string;
}

// $arrays for bbcode headings
for ($i = 1; $i <=$nbJour; $i++) {
	$Saison[$i]['blockID'] = "j$i";
	if ($i == 1) {
		$Saison[$i]['titre'] = "$nomTounois – 1re journée";
	}else{
		$Saison[$i]['titre'] = "$nomTounois – $i"."e journée";
	}
}
for ($i = 1; $i <=4; $i++) {
		$Coupe[$i]['blockID'] = "c$i";
		$str = strFinale($i);
		$Coupe[$i]['titre'] = "$nomCoupe – $str";
}
for ($i = 1; $i <=4; $i++) {
		$PO[$i]['blockID'] = "p$i";
		$str = strFinale($i);
		$PO[$i]['titre'] = "$nomPO – $str";
}


// some $vars to be used in the next for loop
$total = count($scheduleSaison);
if ($matchesCoupe) {
$total = $total + count($scheduleCoupe);
}
if ($matchesPO) {
	$total = $total + count($schedulePO);
}
$tournois = "Saison";
$s = 0;
$c = 0;
$p = 0;
$t = "s";

// build $match array following JBM's calender alternating between regular season and a KO tournament (called "Coupe") and endind with playoffs
// $key is the current element taken from array of either tournament. $$t is the key of corresponding tournament's array
for ($j=0; $j < $total ; $j++) {
	$key = ${"schedule$tournois"}[$$t];
	if ((${"schedule$tournois"}[$$t]->round == 8 | ${"schedule$tournois"}[$$t]->round == 10 | ${"schedule$tournois"}[$$t]->round == 12 | !${"schedule$tournois"}[$$t]) & $matchesCoupe) {
		$tournois = "Coupe";
		$t = "c";
		$key = $scheduleCoupe[$$t];
	}elseif ($tournois == "Coupe" & $round !== ${"schedule$tournois"}[$$t]->round & ${"schedule$tournois"}[$$t] == true) {
		$tournois = "Saison";
		$t = "s";
		$key = $scheduleSaison[$$t];
	}elseif ($tournois == "Coupe" & !${"schedule$tournois"}[$$t] & $matchesPO) {
		$tournois = "PO";
		$t = "p";
		$key = $schedulePO[$$t];
	}
	
	if ($key->result == true) {
		$matchID = $key->result->id;
		$match[$j]['ID'] = $matchID;
		$homeScore = $key->result->teams[0]->score;
		$awayScore = $key->result->teams[1]->score;
		$match[$j]['score'] = "[url=/p/match?id=$matchID t=_blank]$homeScore – $awayScore"."[/url]";
	} else {
		$match[$j]['score'] = "-";
	}
	
	$match[$j]['round'] = $key->round;

	$homeID = $key->teams[0]->id;
	$match[$j]['homeID'] = $homeID;													                      # 6 digits ID, for URL
	$match[$j]['homeTeam'] = $team[$homeID][name];									              # team's name without (*) or [*]
	$rdmKey = array_rand($piconArray[$team[$homeID][rosterName]]);					      # 
	$match[$j]['homePicon'] = $piconArray[$team[$homeID][rosterName]][$rdmKey];		# random picon
	$match[$j]['homeCoach'] = $team[$homeID][coach];								              # coatch's name to appear in bbcode
	$match[$j]['homeCoachID'] = $team[$homeID][coachURL];							            # actual fumbbl' coatch's name for URL

	$awayID = $key->teams[1]->id;													                        # same as above, for away team
	$match[$j]['awayID'] = $awayID;
	$match[$j]['awayTeam'] = $team[$awayID][name];
	$rdmKey = array_rand($piconArray[$team[$awayID][rosterName]]);
	$match[$j]['awayPicon'] = $piconArray[$team[$awayID][rosterName]][$rdmKey];
	$match[$j]['awayCoach'] = $team[$awayID][coach];
	$match[$j]['awayCoachID'] = $team[$awayID][coachURL];

	switch ($tournois) {
		case 'Saison':
			$s++;
			break;
		case 'Coupe':
			$c++;
			break;
		case 'PO':
			$p++;
			break;	
		default:
			echo "expect the unexpected";
			break;
	}
}




$calBBcode = fopen("calendrierJBM.txt", 'w');

// writes bbcode's headings
function entete($tourney, $matchInfo){
	global $date, $Saison, $Coupe, $PO, $round, $calBBcode;
	$round = $matchInfo['round'];
	$titre = $$tourney[$round]['titre'];
	$blockID = $$tourney[$round]['blockID'];
	$dateStr = date_format($date, "d/m/Y");
	fwrite($calBBcode, "[/block][block=hidden automargin pad5 bg=#CCABD4 height=520px width=735px group=jour id=".$blockID."][block=floatright][b][size=12]Deadline : ".$dateStr."[/size][/b][/block][font=nyala][color=purple][size=16][b][align=left]".$titre."[/align][/b][/size][/color][/font]\n");
	date_add($date, date_interval_create_from_date_string("10 days"));
}
// writes bbcode's individual matchreport (and scheduled match)
function matchReport($matchInfo){
	global $calBBcode;
	fwrite($calBBcode, "\n[block width=735px height=44px][block=floatleft right pad10 width=280px][url=/~".$matchInfo['homeCoachID']." t=_blank]".$matchInfo['homeCoach']."[/url] – [url=/p/team?team_id=".$matchInfo['homeID']." t=_blank]".$matchInfo['homeTeam']."[/url][/block][block=floatleft right width=45px][picon=".$matchInfo['homePicon']." x=2][/block][block=floatleft center width=85px][size=20][b]".$matchInfo['score']."[/b][/size][/block][block=floatleft left width=45px][picon=".$matchInfo['awayPicon']." x=4][/block][block=floatleft left pad10 width=280px][url=/p/team?team_id=".$matchInfo['awayID']." t=_blank]".$matchInfo['awayTeam']."[/url] – [url=/~".$matchInfo['awayCoachID']." t=_blank]".$matchInfo['awayCoach']."[/url][/block][/block]");
}

// bbcode, the if statement is used to follow JBM's calender
$tournois = "Saison";
entete($tournois, $match[0]);
for ($j=0; $j < $total ; $j++) {

	if (($match[$j]['round'] == 8 | $match[$j]['round'] == 10 | $match[$j]['round'] == 12 | !$match[$j]) & $matchesCoupe) {
		$tournois = "Coupe";
	}elseif ($tournois == "Coupe" & $round !== $match[$j]['round'] & $match[$j] == true) {
		$tournois = "Saison";
	}elseif ($tournois == "Coupe" & !$match[$j] & $matchesPO) {
		$tournois = "PO";
	}
	if ($round !== $match[$j]['round']) {
		entete($tournois, $match[$j]);
	}
	matchReport($match[$j]);
}
fclose($calBBcode);

?>
