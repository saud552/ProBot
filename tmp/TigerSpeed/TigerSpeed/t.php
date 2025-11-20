<?php

include('apifiles/3.php');

function get_serv($file, $serv){
    require_once('apifiles/'.$file.".php");
    if($file == '1'){
        $api = new Api();
    }elseif($file == '2'){
        $api = new Api2();
    }elseif($file == '3'){
        $api = new Api3();
    }
    $services = $api->services();
    foreach($services as $s){
        $ss = json_decode(json_encode($s));
        if ($ss->service == $serv){
            $api = '';
            return [
                'rate' => $ss->rate,
                'min' => $ss->min,
                'max' => $ss->max
            ];
        }
    }
    $api = '';
    return false;
}

$g = get_serv('3', '7839');
print_r($g);