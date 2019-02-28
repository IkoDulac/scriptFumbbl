#!/usr/bin/php
<?php
// outdated script writen before new API functions where added to website (or before i knew they exist !)

$rawData = file_get_contents('testfile.xml') or die("error: cannot fetch url");
$rawXML = simplexml_load_string($rawData) or die("error: cannot create object");

$players=array();

function pdata() {
        
    global $players, $perf;

    if(empty($players[$perf['player']])){
        $players[$perf['player']] = $perf;
    }else{ 

    $data = $players[$perf['player']];

    $data['completions'] += $perf['completions'];
    $data['touchdowns'] += $perf['touchdowns'];
    $data['interceptions'] += $perf['interceptions'];
    $data['casualties'] += $perf['casualties'];
    $data['mvps'] += $perf['mvps'];
    $data['passing'] += $perf['passing'];
    $data['rushing'] += $perf['rushing'];
    $data['blocks'] += $perf['blocks'];
    $data['fouls'] += $perf['fouls'];
    $data['turns'] += $perf['turns'];

    $players[$perf['player']] = $data;
    }
    return $players[$perf['player']];
}

foreach($rawXML->matches as $matches) {
    foreach($matches->match as $match) {
        foreach($match->home->performances->performance as $performance) {

        $perf = current($performance);
        $players[$perf['player']] = pdata();    
        }

        foreach($match->away->performances->performance as $performance) {

        $perf = current($performance);
        $players[$perf['player']] = pdata();    
        }
    }  
}


$fp = fopen('playerstest.csv', 'w');

//entete
$headers = current($players);
$val = array();
foreach($headers as $key => $header){
    $val[]=$key;
}

fputcsv($fp, $val);



echo '[table]';
foreach ($players as $player) {
    echo '[tr]';
    fputcsv($fp, $player);
    foreach($player as $pdata){
       echo '[td]'.$pdata.'[/td]';
    }
    echo '[/tr]';
}
echo '[/table]';
fclose($fp);

?>
