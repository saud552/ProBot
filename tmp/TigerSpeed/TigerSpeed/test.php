<?php

include_once('apifiles/1.php');
$api = new Api();
$order = $api->order(array('service' => 2405, 'link' => 'https://google.com', 'quantity' => 500));
$order_js =json_encode($order);	
echo $order_js ;