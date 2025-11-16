<?php
#error_reporting(-1);

ob_start();
include('aymn.php');

define("API_KEY",$API_KEY);
function bot($method,$datas=[]){
$aymnnn = http_build_query($datas);
$url = "https://api.telegram.org/bot".API_KEY."/".$method."?$aymnnn";
$aymnnn = file_get_contents($url);
return json_decode($aymnnn);
}

$my_bot = [
    [['text' => $name_bot, 'url' => $url_bot]],
];
$up = file_get_contents('php://input');
$update = json_decode($up);
if ($update->callback_query) {
    $chat_id2 = $update->callback_query->message->chat->id;
    $id2 = $update->callback_query->from->id;
    $first_name = $update->callback_query->from->first_name;
    $message_id2 = $update->callback_query->message->message_id;
    $data = $update->callback_query->data;
    $exdata = explode("|", $data);
}

function get_coin_info($c){
    if($c == 'usd'){
        return [1,'دولار 🇺🇸'];
    }
    if($c == 'y'){
        return [570,'ريال قـ 🇾🇪'];
    }
    if($c == 's'){
        return [4,'ريال 🇸🇦'];
    }
    if($c == 'd'){
        return [2000,'آسيا 🇮🇶'];
    }
    if($c == 'Aymn'){
return [100,'Speed ♠️'];
}
    if($c == 'j'){
        return [50,'جنيه 🇪🇬'];
    }
    if($c == 'r'){
        return [4,'درهم 🇦🇪'];
    }
    if($c == 'g'){
        return [4,'ريال 🇶🇦'];
    }
    if($c == 'o'){
        return [2300,'ريال جـ 🇾🇪'];
    }
}
include('./sql_class.php');
if (mysqli_connect_errno()) {
    return;
}
$sq = array_reverse($sql->sql_readarray('order_waiting'));
shuffle($sq);
$t = '0';
foreach($sq as $array){
    $t += 1;
    $user = $array['user'];
    $cap = $array['caption'];
    $ms_user = $array['ms_user'];
    $ms_channel = $array['ms_channel'];
    $order_id = $array['order_id'];
    $price = $array['price'];
    $num_order = $array['num_order'];
    $apis = $array['api'];
    require_once('apifiles/'.$apis.".php");
    if($apis == '1'){
        $api = new Api();
    }elseif($apis == '2'){
        $api = new Api2();
     }elseif($apis == '3'){
        $api = new Api3();
    }elseif($apis == '4'){
        $api = new Api4();
    }elseif($apis == '5'){
        $api = new Api5();
    }elseif($apis == '7'){
        $api = new Api7();
    }elseif($apis == '10'){
        $api = new Api10();
    }elseif($apis == '11'){
        $api = new Api11();
    }elseif($apis == '12'){
        $api = new Api12();
    }
    $status = json_decode(json_encode($api->status($order_id)));

    /**
     * In progress
     * Partial
     * Completed
     * Canceled
     * {"charge":"0.14","start_count":null,"status":"Completed","remains":"0","currency":"USD"}
     */
    $stut = $status->status;
        /**
     * Completed
     */
     echo "طلب: $order_id - الحالة: $stut<br>";
     
    if ($stut == 'Completed'){
        $capt = "*✅︙طلب رشق مكتمل بنجاح.*".$cap;
        $captAymn = "*✅︙طلب رشق مكتمل بنجاح.*".$cap;
        $sql->sql_write('order_done(user,type,caption,api,price,order_id,remains,num_order)', "VALUES('$user','$stut','$capt','$apis','$price','$order_id','0','$num_order')");  
        $sql->sql_del('order_waiting', 'order_id', $order_id);   
        bot('sendMessage', [
            'chat_id' => $user,
            'text' => $capt,
            'parse_mode'=>markdown,
            'disable_web_page_preview' => true,
            'reply_to_message_id'=>$ms_user,
        ]);
            bot('sendMessage', [
                'chat_id' => $EngAymn1,
                'text' => $captAymn,
                'parse_mode'=>markdown,
                'disable_web_page_preview' => true,
'reply_markup'=>json_encode([
'inline_keyboard'=> [
[['text'=>"عرض بيانات العميل 👤.",'callback_data'=>"EngAymnZpon|".$user]],
]
])
            ]);
        }
    if($t == 30){
        break;
    }
}