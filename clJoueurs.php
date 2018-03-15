#!/usr/bin/php
<?php
$rawData = file_get_contents('https://fumbbl.com/xml:group?id=9761&op=matches');
$rawXML = simplexml_load_string($rawData);

//Stock les variables des joueurs
$players=array();

//Building the stats
foreach($rawXML->matches as $row){
    foreach($row->match as $match){
        foreach($match->home->performances->performance as $performance){
            //Ici on a les joueurs isolé
            $perf = current($performance);

            //Nouveau joueur
            if(empty($players[$perf['player']])){
                $players[$perf['player']] = $perf;
            }else{
                //Joueur existant on add les stats
                $data = $players[$perf['player']];

                $data['completions'] = $data['completions'] + $perf['completions'];
                $data['touchdowns'] = $data['touchdowns'] + $perf['touchdowns'];
                $data['interceptions'] = $data['interceptions'] + $perf['interceptions'];
                $data['casualties'] = $data['casualties'] + $perf['casualties'];
                $data['mvps'] = $data['mvps'] + $perf['mvps'];
                $data['passing'] = $data['passing'] + $perf['passing'];
                $data['rushing'] = $data['rushing'] + $perf['rushing'];
                $data['blocks'] = $data['blocks'] + $perf['blocks'];
                $data['fouls'] = $data['fouls'] + $perf['fouls'];

                $players[$perf['player']] = $data;
                unset($data);
            }
        }
    }
}

$fp = fopen('players.csv', 'w');

//Building the header
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
