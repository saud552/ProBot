<?php


#error_reporting(-1);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
$twas = file_get_contents("data/id/$id/twas.txt");
ob_start();
include('aymn.php');

$my_bot = [
    [['text' => $name_bot, 'url' => $url_bot]],
];
define("API_KEY",$API_KEY);
function bot($method,$datas=[]){
$aymnnn = http_build_query($datas);
$url = "https://api.telegram.org/bot".API_KEY."/".$method."?$aymnnn";
$aymnnn = file_get_contents($url);
return json_decode($aymnnn);
}
function shortNumber($num) 
{
    $units = ['', 'K', 'M', 'B', 'T'];
    for ($i = 0; $num >= 1000; $i++) {
        $num /= 1000;
    }
    return round($num, 1) . $units[$i];
}
 
function rand_text(){
    $abc = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0");
    $fol = '#'.$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)].$abc[rand(5,36)];
    return $fol;
}


function check_m($id, $chat){
    $join = bot('getChatMember', ["chat_id" => $chat, "user_id" => $id])->result->status;
    if($join == 'left' or $join == 'kicked'){
        return false;
    }else{
        return true;
    }
}

$up = file_get_contents('php://input');
$update = json_decode($up);
if ($update->message) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text = $message->text;
    $extext = explode(" ", $text);
    $EngAymnsh7n = explode("|", $text);
    $first_name = $update->message->from->first_name;
    $username = $message->from->username;
    $username2 = $update->message->from->username;
    $id = $message->from->id;
    $message_id = $message->message_id;
    $entities = $message->entities;
    $language_code = $message->from->language_code;
    $tc = $update->message->chat->type;
    $jsons = json_decode(file_get_contents('data/data.json'), true);
    $get_jsons = json_decode(file_get_contents('data/data.json'));
    $re_message = $update->message->reply_to_message;
    $re_text = $re_message->text;
    $apii3 = base64_decode(""); 
}


//data callback
if ($update->callback_query) {
    $chat_id2 = $update->callback_query->message->chat->id;
    $id2 = $update->callback_query->from->id;
    $first_name = $update->callback_query->from->first_name;
    $message_id2 = $update->callback_query->message->message_id;
    $username = $message->from->username;
    $data = $update->callback_query->data;
    $exdata = explode("|", $data);
    $jsons = json_decode(file_get_contents('data/data.json'), true);
    $get_jsons = json_decode(file_get_contents('data/data.json'));
}


if($update->inline_query->query){
    $inline = $update->inline_query;
    $query_id = $inline->id;
    $query = $inline->query;
    $query_form_id = $inline->from->id;
    if($query == 'mylink'){
        bot('answerInlineQuery',[
            'inline_query_id'=>$query_id,    
            'cache_time'=>0,
'parse_mode'=>"MarkDown", 
            'results' => json_encode([[
                'type'=>'article',
                'id'=> base64_encode(rand(5,55)),
                'title'=>"✅ ⌯ إضغط هنا لنشر رابط الدعوة الخاص بك 🚀.",
                'description'=>"🛍️ ⌯ سيتم وضع رابطك الخاص في الإعلان تلقائياً",
                'disable_web_page_preview'=>'true',
                'input_message_content'=>['disable_web_page_preview'=>true,'message_text'=>"*☑️ ⌯ بوت $NameBotG 🤖.*

*🤖 ⌯ البوت الاول* في تقديم خدمات الرشق لجميع تطبيقات *السوشيال ميديا 🧿.*
*[ تليجرام - انستجرام - تيك توك - يوتيوب - تويتر - فيسبوك - سناب شات - ثريدز - سوبتيفاي - ديسكورد - لايكي - كواي ] 🚀.*

*🔗 ⌯ سيرفرات* مختارة من مختصين في التمويلات السريعة العالمية لتصعيد الحسابات *بأقصى سرعة واقوى جودة 🏆.*

*🛍️ ⌯ خدمات مجانية* نمنحها للعملاء الجدد *لتجربة سرعة وجودة* سيرفرات الرشق من *بوت $NameBotG ✅.*"],
                    'reply_markup' => ['inline_keyboard' => [ 
                        [['text' => "🤖 ⌯ الدخول إلى البوت ☑️.", 'url' => $link_invite.$query_form_id]],
                        ]
                    ]
            ]])
        ]);
    }
}
$bans = explode("\n", file_get_contents("data/ban.txt"));
$is_ok = file_get_contents('data/is_ok.txt');
$is_no = file_get_contents('data/is_no.txt');
$ex_is_ok = explode("\n", $is_ok);
$ex_is_no = explode("\n", $is_no);
$files = file_get_contents('files/'.$id.'.txt');

// ملف لتخزين آخر وقت إرسال رسالة الحظر لكل مستخدم
$ban_timing_file = 'data/ban_timing.json';
$ban_timings = json_decode(file_get_contents($ban_timing_file), true);
if (!$ban_timings) {
    $ban_timings = [];
}

$current_time = time(); // الوقت الحالي بالثواني
$ban_message_delay = 10; // الفارق الزمني المطلوب بين الرسائل (3 ثواني)

if($message) {
    if (!in_array($id, $adminss)) {
        if (in_array($id, $ex_is_no) or in_array($id, $bans)) {
            // تحقق من آخر وقت تم فيه إرسال رسالة الحظر لهذا المستخدم
            if (!isset($ban_timings[$id]) || ($current_time - $ban_timings[$id] >= $ban_message_delay)) {
                bot('sendmessage', [
                    'chat_id' => $id,
                    'text' => "*⛔ ⌯ يبدو أنك محظور من إستخدام البوت ،*
⚠️ ⌯ إذا كنت تعتقد أنه تم حضرك من إستخدام البوت *عن طريق الخطأ* فقم بمراسلة الادارة : *$aymn ☑️.*",
                    'parse_mode' => "MarkDown",
                    'reply_to_message_id' => $message_id
                ]);
                // تحديث وقت آخر إرسال لهذا المستخدم
                $ban_timings[$id] = $current_time;
                // حفظ التحديثات في الملف
                file_put_contents($ban_timing_file, json_encode($ban_timings));
            }
            return;
        }
    }
}

$json_config = json_decode(file_get_contents('data/config.json'), true);
$config = json_decode(file_get_contents('data/config.json'));
$run = $config->run;

$members = file_get_contents('data/members.txt');
$exmembers = explode("\n", $members);
if (!in_array($id, $exmembers) and $update->message){
    $jsonsstart = json_decode(file_get_contents('data/cache.json'), true);
    $get_jsonsstart = json_decode(file_get_contents('data/cache.json'));
    if(in_array($extext[1], $exmembers)){
        if($extext[0] == '/start' && $extext[1] != null){
            $jsonsstart["$id"] = $extext[1];
            file_put_contents("data/cache.json", json_encode($jsonsstart));
            $IS_LINK = true;
        }
    
    }
    $ch_sub = $config->channel;
    $ch_sub1 = $config->channel2;
    $ch_sub2 = $config->channel3;
    $MyAymn = str_replace("@","",$ch_sub);
    $MyAymn1 = str_replace("@","",$ch_sub1);
    $MyAymn2 = str_replace("@","",$ch_sub2);
    // تحقق من القناة الأولى
    $join1 = bot('getChatMember', ["chat_id" => $ch_sub, "user_id" => $id])->result->status;
    
    // تحقق من القناة الثانية
    $join2 = bot('getChatMember', ["chat_id" => $ch_sub1, "user_id" => $id])->result->status;
    
    // تحقق من القناة الثالثة
    $join3 = bot('getChatMember', ["chat_id" => $ch_sub2, "user_id" => $id])->result->status;

    if($config->runchannel != 'stop'){
        // التحقق من الاشتراك في كلا القناتين
        if (($join1 == 'left' or $join1 == 'kicked') || ($join2 == 'left' or $join2 == 'kicked') || ($join3 == 'left' or $join3 == 'kicked')) {
            bot('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "
*- مرحباً عزيزي المستخدم 👤.*

⛔ ⌯ حتى تتمكن من *إستخدام البوت بالطريقة الصحيحة* من دون الوقوع في المشاكل ، يجب عليك أولاً *الاشتراك في قناة البوت 💁🏻‍♂️.*

*🏆 ⌯ قناة البوت : $ch_sub ☑️.*
*🏆 ⌯ قناة البوت : $ch_sub1 ☑️.*
*🏆 ⌯ قناة البوت : $ch_sub2 ☑️.*

*- إشترك* ثم إضغط */start ✅.*
                ",
                'parse_mode'=>"MarkDown",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=> [
                        [['text'=> "✅︙القناة الرسمية.",'url'=>"https://t.me/".$MyAymn]],
                        [['text'=> "✅︙قناة إشعارات الخدمة.",'url'=>"https://t.me/".$MyAymn1]],
                        [['text'=> "✅︙قناة الإثباتات.",'url'=>"https://t.me/".$MyAymn2]],
                    ]
                ])
            ]);
            return;
        }
    }
$ch_sub2 = $config->channel;
    $join = bot('getChatMember', ["chat_id" => $ch_sub2, "user_id" => $id])->result->status;
    if($config->runchannel != 'stop'){
        if ($join == 'left' or $join == 'kicked') {
            bot('sendMessage',[
                    'chat_id' => $chat_id,
                    'text' =>" *- مرحباً عزيزي المستخدم 👤.*

⛔ ⌯ حتى تتمكن من *إستخدام البوت بالطريقة الصحيحة* من دون الوقوع في المشاكل ، يجب عليك أولاً *الاشتراك في قناة البوت 💁🏻‍♂️.*

*🏆 ⌯ قناة البوت : $ch_sub2 ☑️.*

*- إشترك* ثم إضغط */start ✅.*",
'parse_mode'=>"MarkDown",
                ]
            );
            return;
        }
    }
    $get_s = $get_jsonsstart->{$id};
    if($get_s != null or $IS_LINK){
        if (!$message->contact->user_id && !in_array($id, $ex_is_ok) && !in_array($id, $ex_is_no)) {
            bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "*👋🏻︙مرحباً بك عزيزي* [$first_name](tg://user?id=$id) 
*🤖︙بوت تـايـجـر سـبـيـد - 𝐓𝐢𝐠𝐞𝐫𝐒𝐩𝐞𝐞𝐝 🤖. هو بوت يقوم بتقديم كافة الخدمات الرائجة في مواقع التواصل الإجتماعي.*

*✅︙للدخول إلى البوت ، يجب علينا أولاً التأكد من أنك إنسان حقيقي 👤.*

*⤵️︙يرجى الضغط على الزر بالأسفل للتحقق.*",
'parse_mode'=>"MarkDown",
                'reply_to_message_id' => $message_id,
                "reply_markup" => json_encode([
                    "keyboard" => [
                        [["text" => "☑️ ⪼ التحقق من الحساب.", "request_contact" => true]],
                    ]
                ])
            ]);
            return;
        }
        
        if (!in_array($id, $ex_is_ok) && !in_array($id, $ex_is_no)) {
            if ($message->contact->user_id == $id) {
                $number = "+".$message->contact->phone_number;
                foreach ($ban_num as $one) {
                    if (preg_match("/(".$one.")/", $number, $mach)) {
                        $is_ban = false;
                        break;
                    } else {
                        $is_ban = true;
                    }
                }

                if ($is_ban) {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "*👤 ⌯ جهة الاتصال وهمية...!
⛔ ⌯ تم حظرك من إستخدام البوت*",
'parse_mode'=>"MarkDown",
                        'reply_to_message_id' => $message_id,
                        'reply_markup' => json_encode([
                            'remove_keyboard' => true
                        ])
                    ]);
                    bot('sendmessage', [
                        'chat_id' => $dev1,
                        'text' => "*🚫 ⌯ تم حظر مستخدم جديد 🤖.*
*✅︙ السبب : جهة الإتصال وهمية ⚠️.*

*⤵️ ⌯ معلومات المستخدم :*

*👤 ⌯ أسمه* : [$first_name](tg://user?id=$id)  
*🌐 ⌯ يوزره : $username*
*🔗 ⌯ رقمه : $number*
",
'parse_mode'=>"MarkDown",

]);
                    file_put_contents('data/is_no.txt', $id."\n", FILE_APPEND);
                    return;
                } else {
                    bot('sendmessage', [
                        'chat_id' => $chat_id,
                        'text' => "*✅ ⌯ تم تأكيد جهة الاتصال الخاصة بك ،*
*🤖 ⌯ إبدأ في إستخدام البوت وتمتع بخدماته* الان عبر الضغط على */start ☑️.*",
'parse_mode'=>"MarkDown",
                        'reply_to_message_id' => $message_id,
                        'reply_markup' => json_encode([
                            'remove_keyboard' => true
                        ])
                    ]);
                    bot('sendmessage', [
                        'chat_id' => $dev1,
                        'text' => "*✅ ⌯ تم دخول شخص الى رابط دعوة عميل آخر.*

*⤵️ ⌯ معلومات المستخدم :*

*👤︙أسمه :* [$first_name](tg://user?id=$id) 
*🌐 ︙يوزره : $username*
*🔗︙رقمه : $number*",
'parse_mode'=>"MarkDown",

]);
                    file_put_contents('data/is_ok.txt', $id."\n", FILE_APPEND);
                    include_once('./sql_class.php');
                    if (mysqli_connect_errno()) {
                        return;
                    }
                    $jsonsstart["$id"] = null;
                    file_put_contents("data/cache.json", json_encode($jsonsstart));
                    $us = $sql->sql_select('users', 'user', $get_s);
                    $coin = $us['coin'];
                    $invite = $config->invite;
                    $return = $coin + $invite;
$ALDORAFY = $us['mycoin'];
$AYMN3MK = $us['coinfromuser'];
$AMKAYMN = $AYMN3MK + $invite;
                    $us = $sql->sql_edit('users', 'coin', $return, 'user', $get_s);
                   $us = $sql->sql_edit('users', 'coinfromuser', $AMKAYMN, 'user', $get_s);
$AYMNN = get_coin_info($ALDORAFY);
$AYMNENGG = $AYMNN[0] * $invite;
$AYMNENGGG = $AYMNN[0] * $return;
$AYMNeng = $AYMNN[1];
                    bot('sendmessage', [
                        'chat_id' => $get_s,
                        'text' => "*☑️ ⌯ قام شخص بالدخول الى البوت عن طريق الرابط الخاص بك ،*
*💸 ⌯ تم اضافة $AYMNENGG $AYMNeng* الى رصيدك ، وأصبح رصيدك الان *$AYMNENGGG $AYMNeng .*",
'parse_mode'=>"MarkDown",
                    ]);
                
                #return;
                }
            } else {
                bot('sendmessage', [
                    'chat_id' => $chat_id,
                    'text' => "🙄 جهة الاتصال ليست تابعة لك..",
                    'reply_to_message_id' => $message_id
                ]);
                return;
            }
        }
    }
}

 
if ($message->text && !in_array($id, $exmembers)) {
    file_put_contents('data/members.txt', $id . "\n", FILE_APPEND);
    include_once("./sql_class.php");
    $all = count($exmembers);
    #$sql = new mysql_api_code($db);
    if($get_s == null){
        $get_s = 'None';
    }
    $v = $sql->sql_write('users(coin,user,spent,charge,mycoin,fromuser,coinfromuser)', "VALUES('0','$id','0','0','usd','$get_s','0')");
    bot('sendMessage', [
        'chat_id' => $dev1,
        'text' => "☑️ ⌯ تم دخول شخص جديد الى البوت !.

*👤 ⌯ الاسم :* [$first_name](tg://user?id=$id) 
*🆔 ⌯ الايدي : $id*
*🌐 ⌯ اليوزر : $username*
*🔗 ⌯ رقمه : $number*

*✅ ⌯ إجمالي عدد مستخدمين البوت : $all 🪗.*",
        'parse_mode' => "MarkDown",
    ]);
}


$ENGAIMN = "@TigerSpeedCH";
$ENGAIMN1 = "@TigerSpeed1";
$ENGAIMN2 = "@OY_ED";
$getch2li = str_replace("@",'',$ENGAIMN);
$getch3li = str_replace("@",'',$ENGAIMN1);
$getch4li = str_replace("@",'',$ENGAIMN2);

// تحقق من القناة الأولى
$join1 = bot('getChatMember', ["chat_id" => $ENGAIMN, "user_id" => $id])->result->status;

// تحقق من القناة الثانية
$join2 = bot('getChatMember', ["chat_id" => $ENGAIMN1, "user_id" => $id])->result->status;

// تحقق من القناة الثالثة
$join3 = bot('getChatMember', ["chat_id" => $ENGAIMN2, "user_id" => $id])->result->status;

if($config->runchannel != 'stop'){
    // التحقق من الاشتراك في كلا القناتين
    if (($join1 == 'left' or $join1 == 'kicked') || ($join2 == 'left' or $join2 == 'kicked') || ($join3 == 'left' or $join3 == 'kicked')) {
        bot('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "
*- مرحباً عزيزي المستخدم 👤.*

⛔ ⌯ حتى تتمكن من *إستخدام البوت بالطريقة الصحيحة* من دون الوقوع في المشاكل ، يجب عليك أولاً *الاشتراك في قنوات البوت 💁🏻‍♂️.*

*🏆 ⌯ القناة الرسمية : $ENGAIMN ☑️.*
*🏆 ⌯ قناة إشعارات الخدمة : $ENGAIMN1 ☑️.*
*🏆 ⌯ قناة الإثباتات : $ENGAIMN2 ☑️.*

*- إشترك* ثم إضغط */start ✅.*
        ",
        'parse_mode'=>"MarkDown",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>"✅︙القناة الرسمية.",'url'=>"https://t.me/$getch2li"]],
                [['text'=>"✅︙قناة إشعارات الخدمة.",'url'=>"https://t.me/$getch3li"]],
                [['text'=>"✅︙قناة الإثباتات.",'url'=>"https://t.me/$getch4li"]],
            ]
        ])
    ]);
    return;
}
}

if($message->text){
    $ch_sub = $config->channel;
    $ch_sub1 = $config->channel2;
    $ch_sub2 = $config->channel3;
    $MyAymn = str_replace("@","",$ch_sub);
    $MyAymn1 = str_replace("@","",$ch_sub1);
    $MyAymn2 = str_replace("@","",$ch_sub2);

    // تحقق من القناة الأولى
    $join1 = bot('getChatMember', ["chat_id" => $ch_sub, "user_id" => $id])->result->status;
    
    // تحقق من القناة الثانية
    $join2 = bot('getChatMember', ["chat_id" => $ch_sub1, "user_id" => $id])->result->status;
    
    // تحقق من القناة الثالثة
    $join3 = bot('getChatMember', ["chat_id" => $ch_sub2, "user_id" => $id])->result->status;

    if($config->runchannel != 'stop'){
        // التحقق من الاشتراك في كلا القناتين
        if (($join1 == 'left' or $join1 == 'kicked') || ($join2 == 'left' or $join2 == 'kicked') || ($join3 == 'left' or $join3 == 'kicked')) {
            bot('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "
*- مرحباً عزيزي المستخدم 👤.*

⛔ ⌯ حتى تتمكن من *إستخدام البوت بالطريقة الصحيحة* من دون الوقوع في المشاكل ، يجب عليك أولاً *الاشتراك في قناة البوت 💁🏻‍♂️.*

*🏆 ⌯ قناة البوت : $ch_sub ☑️.*
*🏆 ⌯ قناة البوت : $ch_sub1 ☑️.*
*🏆 ⌯ قناة البوت : $ch_sub2 ☑️.*

*- إشترك* ثم إضغط */start ✅.*
                ",
                'parse_mode'=>"MarkDown",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=> [
                        [['text'=> "✅︙القناة الرسمية.",'url'=>"https://t.me/".$MyAymn]],
                        [['text'=> "✅︙قناة إشعارات الخدمة.",'url'=>"https://t.me/".$MyAymn1]],
                        [['text'=> "✅︙قناة الإثباتات.",'url'=>"https://t.me/".$MyAymn2]],
                    ]
                ])
            ]);
            return;
        }
    }
}

function get_serv($file, $serv){
    require_once('apifiles/'.$file.".php");
    if($file == '1'){
        $api = new Api();
    }elseif($file == '2'){
        $api = new Api2();
    }elseif($file == '3'){
        $api = new Api3();
    }elseif($file == '4'){
         $api = new Api4();
    }elseif($file == '5'){
         $api = new Api5();
    }elseif($file == '6'){
         $api = new Api6();
    }elseif($file == '7'){
         $api = new Api7();
    }elseif($file == '8'){
         $api = new Api8();
    }elseif($file == '9'){
         $api = new Api9();
    }elseif($file == '10'){
         $api = new Api10();
    }elseif($file == '11'){
         $api = new Api11();
    }elseif($file == '12'){
         $api = new Api12();
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


function get_vip($charge){
    if($charge < 100){
        return 0;
    }
    if($charge >= 550){
        $vip = 5;
    }elseif($charge >= 450){
        $vip = 4;
    }elseif($charge >= 350){
        $vip = 3;
    }elseif($charge >= 200){
        $vip = 2;
    }elseif($charge >= 100){
        $vip = 1;
    }
    return $vip;
}

function is_multi_ten($num){
    if($num <= 1){
        return false;
    }
    if($num % 10 == 0)  {
        return true;
    }else{
        return false;
    }
}
function isint($num){
    if ($num < 0){
        return false;
    }
    if(is_numeric($num)){
        return true;
    }else{
        return false;
    }
}

function get_coin_info($c){
    if($c == 'usd'){
        return [1,'$'];
    }
    if($c == 'y'){
        return [550,'ر.ي'];
    }
    if($c == 's'){
        return [4,'ر.س'];
    }
    if($c == 'd'){
        return [2000,'د.ع'];
    }
    if($c == 'Youssef'){
return [100,'Speed ♠️'];
}
    if($c == 'j'){
        return [50,'ج.م'];
    }
    if($c == 'r'){
        return [4,'درهم 🇦🇪'];
    }
    if($c == 'g'){
        return [4,'ر.ق'];
    }
    if($c == 'o'){
        return [1617,'ر.ي'];
    }
    if($c == 'saba'){
        return [57,'وحدة'];
    }
    if($c == 'ruble'){
        return [27,'₽.'];
    }
}
$admin_button = [
 [['text' => "💸︙تعيين نقاط المشاركة.", 'callback_data' => "addinvite"],['text' => "💸︙نسبة الربح في الأرقام.", 'callback_data' => "Profit"]],
 [['text' => "🌐︙الدول المتوفرة", 'callback_data'=> "SendCountriesList"]],
    [['text' => "☑️︙اضافة قسم رئيسي.", 'callback_data' => "addcoll"],['text' => "⛔︙حذف قسم رئيسي.", 'callback_data' => "delcoll"]],
    [['text' => "☑️︙اضافة قسم.", 'callback_data' => "adddivi"],['text' => "⛔︙حذف قسم.", 'callback_data' => "deldivi"]],
    [['text' => "☑️︙اضافة خدمات.", 'callback_data' => "addserv"],['text' => "⛔︙حذف خدمات.", 'callback_data' => "delserv"]],
    [['text' => "✅ ⌯ اضافة رصيد.", 'callback_data' => "addbalance"],['text' => "❌ ⌯ خصم رصيد.", 'callback_data' => "delbalance"]],
    [['text' => "💠 ⌯ نسبة تحويل الرصيد.", 'callback_data' => "sel"],['text' => "💠 ⌯ الحد الأدنى للتحويل.", 'callback_data' => "selmin"]],
    [['text' => "🧿 ⌯ تعيين قناة الاشتراك.", 'callback_data' => "addsub"],['text' => "🧿 ⌯ تعيين الخصوصية.", 'callback_data' => "addhelp"]],
     [['text' => "✅ ⪼ إسترجاع رصيد.", 'callback_data'=> "backbalance"]],
];
$adminAymns = [
    [['text' => "☑️︙اضافة قسم رئيسي.", 'callback_data' => "addcoll"],['text' => "⛔︙حذف قسم رئيسي.", 'callback_data' => "delcoll"]],
    [['text' => "☑️︙اضافة قسم.", 'callback_data' => "adddivi"],['text' => "⛔︙حذف قسم.", 'callback_data' => "deldivi"]],
    [['text' => "☑️︙اضافة خدمات.", 'callback_data' => "addserv"],['text' => "⛔︙حذف خدمات.", 'callback_data' => "delserv"]],
];
$aymnaldorafy = [
[['text' => "☑️︙رصيد سبأفون.", 'url' => "tg://user?id=".$aldorafy],['text' => "☑️︙إيداع كريمي.", 'url' => "tg://user?id=".$aldorafy]],
                [['text'=> "☑️︙بطائق سوا.", 'url'=> "tg://user?id=".$aldorafy],['text' => "☑️︙بطائق موبايلي.", 'url'=> "tg://user?id=".$aldorafy]],
                [['text'=> "☑️︙حوالة نجم.", 'url'=> "tg://user?id=".$aldorafy],['text' => "☑️︙حوالة إمتياز", 'url'=> "tg://user?id=".$aldorafy]],
                [['text'=> "☑️︙Payeer", 'url'=> "tg://user?id=".$aldorafy],['text' => "☑️︙USDT - TRX", 'url'=> "tg://user?id=".$aldorafy]],
                [['text'=> "🪗︙طريقة دفع اخرى.", 'url' => "tg://user?id=".$aldorafy]],
                [['text'=> "🔙 ⌯ رجوع.",'callback_data'=>"back2"]],
];
$aldorafystop = [
[['text' => "✅ ⪼ قناة البوت.", 'url'=> $ch_bot ]],
];
$back = [
    [['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back"]],
];
$taked = [
[['text' => "✅︙موافق.", 'callback_data' => "done2"]],
[['text' => "🚫︙إلغاء.", 'callback_data' => "back2"]],
];
$tsweet = [
[['text' => "تصويتات تليجرام الأسرع 🔥 ⪼ 0.50$", 'callback_data' => "vote"]],
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$kashf = [
[['text' => "☑️ ⪼ إحصائيات جميع عملاء البوت.", 'callback_data' => "BotAccount"]],
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$TigerSpeed = [
[['text'=>"💻 ⪼ Api Docs", 'url'=>"tigerspeed.store/api"]],
[['text' => "🧑🏻‍💻 ⪼ Developer.", 'url' => "tg://user?id=".$dev]],
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$YoussefBin = [
[['text'=> "💸 ⪼ أسعار الشحن وطرق الدفع.", 'url'=>"https://t.me/TigerSpeed1/4"]],
[['text' => "✅ ⪼ إرسال صورة الإيصال.", 'url' => "tg://user?id=".$dev]],
[['text'=> "🌐 ⪼ الشحن التلقائي.", 'callback_data'=> "USDT"]],
[['text'=> "🅿️ ⪼ بايير تلقائي.", 'callback_data'=> "payeer"]],
[['text'=> "⭐ ⪼ بايننس تلقائي.", 'callback_data'=> "binance"]],
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$backServ = [
[['text'=> "🔙 ⪼ العودة الى قائمة الخدمات", 'callback_data'=> "selcetcoll|".$code]],
];
$back2 = [
    [['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$back_add = [
    [['text' => "🔙 ⪼ رجوع.", 'callback_data' => "addusers"]],
    
];
$ENGAymn = [
[['text' => "⭐ ⪼ مشاركة إعلان تلقائي..", 'switch_inline_query' => "mylink"]],
[['text' => "🚀 ⪼ إعلان كتابي.", 'callback_data' => "Aymn3mk"]],
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]],
];
$start = [
[['text' => "🚀 ⪼ قسم الرشق.", 'callback_data' => "addusers"],['text' => "🗳️ ⪼ تصويتات تليجرام.", 'callback_data' => "tsweet"]],
[['text' => "🔍 ⪼ البحث برقم الخدمة", 'callback_data' => "search_service_id"]],
[['text' => "🤑 ⪼ ربح رصيد.", 'callback_data' => "Aymnfree"],['text' => "💸 ⪼ شحن حسابك.", 'callback_data' => "buymoney"]],
[['text' => "📊 ⪼ الإحصائيات.", 'callback_data' => "accounty"],['text' => "⚙️ ⪼ الإعدادات.", 'callback_data' => "i3dadatAymn"]],
[['text' => "☑️ ⪼ قناة الإثباتات.", 'url' => $channel],['text' => "🤖 ⪼ شرح البوت.",'callback_data' => "damfni"]],
[['text'=> "🪙 ⪼ تغيير عملة حسابك.",'callback_data'=> "changecoin"],['text'=> "🤩 ⪼ ربط خدمات API", 'callback_data'=> "webaymn"]],
[['text'=> "💬 ⪼ طلب مساعدة.", 'callback_data'=> "super"]],
];
$changecoin = [
    [['text' => "الدولار الأمريكي 💲.", 'callback_data' => "selectcoin|usd"]],
        [['text' => "العملة اليمنية القديمة 1$ = [ 550 ريال ] 🇾🇪", 'callback_data' => "selectcoin|y"]],
    [['text' => "العملة اليمنية الجديدة 1$ = محدث [ 1617 ريال ] 🇾🇪", 'callback_data' => "selectcoin|o"]],
    [['text' => "العملة السعودية 1$ = [ 4 ريال] 🇸🇦", 'callback_data' => "selectcoin|s"]],
    [['text' => "العملة العراقية 1$ = [ 2000 دينار] 🇮🇶", 'callback_data' => "selectcoin|d"]],
    [['text' => "العملة المصرية 1$ = [ 50 جنيه] 🇪🇬", 'callback_data' => "selectcoin|j"]],
    [['text' => " العملة القطرية 1$ = [ 4 ريال] 🇶🇦", 'callback_data' => "selectcoin|g"]],
    [['text' => "وحدات سبأفون اليمنية 1$ = [ 57 وحدة] 📲", 'callback_data' => "selectcoin|saba"]],
    [['text' => "🤖 ⌯ الروبل [ 27 روبل] ₽.", 'callback_data' => "selectcoin|ruble"]],
[['text'=> "عملة ".$NameBotG." 1$ = [ 100 Speed] 🤖.", 'callback_data'=> "selectcoin|Youssef"]],
    [['text' => "🔙 ⌯ رجوع.", 'callback_data' => "back2"]],
];
$damfni = [
    [['text' => "📢︙شرح كيفية إستخدام البوت.", 'url' => $ch_bot]],
        [['text' => "📢︙شرح تجميع النقاط من البوت.", 'url' => $ch_bot]],
    [['text' => "📢︙شرح طريقة طلب خدمة.", 'url' => $ch_bot]],
   [['text' => "☑️︙الشروط والخصوصية.", 'callback_data' => "help"]],
   [['text' => "🔙 ⌯ رجوع.", 'callback_data' => "back2"]],
];
$AYMN1TOP = [
[['text'=> "🔄 ⪼ تحويل الرصيد.",'callback_data'=> "sendmoney"]],
[['text'=> "🛍️ ⪼ طلباتي.",'callback_data'=> "mystat"]],
   [['text' => "🔙 ⪼ رجوع. ", 'callback_data' => "back2"]],
];
$ok = [
    [['text' => "✅ ⪼ موافق.", 'callback_data' => "done"]], 
[['text' => "⛔ ⪼ إلغاء.", 'callback_data' => "EngBayahya"]],
];

if ($update->message) {
    if($run == 'stop' && !in_array($id, $adminss) && !in_array($id, $adminsAymn)){
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*🤖 ⌯ البوت تحت الصيانة ،
☑️ ⌯ سيتم إشعاركم فور الاكتمال في قناة البوت :*

[⌯ قناة البوت الرسمية 💎]($ch_bot).",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
'reply_markup'=>json_encode([
            'inline_keyboard'=> $aldorafystop
])
        ]);
        return;
    }

    if ($text == '/startttt') {
        include('./sql_class.php');
        $sq = $sql->sql_select('users', 'user', $id);
        $coin = $sq['coin'];
$Ayymnn = $sq['charge'];
        $mycoin = $sq['mycoin'];
        $info_coin = get_coin_info($mycoin);
        $coin_after_coin = $info_coin[0] * $coin;
        $coin_name = $info_coin[1];
        $user_one_dollar = explode("\n", file_get_contents('data/user_one_dollar.txt'));
            if('0.499' > $Ayymnn){
                file_put_contents('data/user_one_dollar.txt', $id."\n", FILE_APPEND);
            }
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*👤︙مرحباً بك* [$first_name](tg://user?id=$id) 🖤.
☑️︙في بوت خدمات *$NameBotG* 🤖.

*🆔︙حسابك :* `$id` .
*💸︙رصيدك : $coin_after_coin $coin_name.*

🙋🏻︙يمكنك *التحكم بالبوت* عبر الأزرار في *الاسفل ⬇️.*",
 'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $start
            ])
        ]);
        return;
    }
if ($text == '/myorder') {
include('./sql_class.php');
$EngAymnOrde = $sql->sql_select_all('order_done', 'user' ,$id);
$AymnEngTlb = '';
foreach($EngAymnOrde as $EngAymnOrder){
$EngAymnAltlbat = $EngAymnOrder['order_id'];
$AymnEngTlb .= "*☑️ ⌯* `$EngAymnAltlbat` \n";
}
bot('sendMessage', [
'chat_id'=> $chat_id,
'text'=>"$AymnEngTlb",
'parse_mode'=>"MarkDown",
]);
}
    if($text && $get_jsons->{$id}->data == 'sendmoney'){
        if(!in_array($text, $exmembers)){
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*⛔ ⌯ عذراً ...* لايمكنك التحويل ،
*🚸 ⌯ الشخص المراد التحويل له* ليس مستخدم للبوت بعد...! *قم بدعوته اولاً ☑️.*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back2
                ])
            ]);
            return;
        }
        $jsons["$id"]["data"] = 'sendmoney2';
        $jsons["$id"]["for"] = $text;
        file_put_contents("data/data.json", json_encode($jsons));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*✅ ⌯ ممتاز ،*
👤 ⌯ العميل ⪼ [$text](tg://user?id=$text)
⬇️ ⌯ أرسل الان مبلغ الرصيد المراد تحويله.

⚠️ ⌯ ملاحظة : *الرصيد بعملة الدولار 💲.*",
'parse_mode'=>"MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back2
            ])
        ]);
    }
    if($text && $get_jsons->{$id}->data == 'sendmoney2'){
        if(isint($text)){
            $min = $config->selmin;
            $prec = $config->sel;
            if($text < $min){
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "
*⛔ ⌯ يجب أن يكون المبلغ أعلى من الحد الادنى للتحويل ،*
💸 ⌯ الحد الأدنى : *$min$*
☑️ ⌯ العمولة : *$prec%*
                    ",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back2
                    ])
                ]);
                return;
            }
            include('./sql_class.php');
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"*❌ ⌯ عذراً... ، حدث خطأ.*",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            $us = $sql->sql_select('users', 'user', $id);
            $coin = $us['coin'];
            if($text > $coin){
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*⛔ ⌯ رصيدك غير كافي لإتمام العملية ،*
☑️ ⌯ رصيدك *$coin$* , المبلغ الذي اخترته *$text$* ⚠️.",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back2
                    ])
                ]);
                return;
            }
            $jsons["$id"] = null;
            file_put_contents("data/data.json", json_encode($jsons));
            $return = $coin - $text;
            $sql->sql_edit('users', 'coin', $return, 'user', $id);
            $for = $get_jsons->{$id}->for;
            $us_to = $sql->sql_select('users', 'user', $for);
            $coin_to = $us_to['coin'];
            $precent = ($text / 100) * $prec;
            $after_precent = $text - $precent;
            $return_to = $coin_to + $after_precent;
            $sql->sql_edit('users', 'coin', $return_to, 'user', $for);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅ ⌯ تم تحويل الرصيد بنجاح.*

*👤 - من :* [$first_name](tg://user?id=$id)
*👤 - إلى : $for*

💸 ⁞ القيمة الكلية : *$text$*
💱 ⁞ العمولة : *$precent$*
💰 ⁞ المبلغ المحول : *$after_precent$*
☑️ ⁞ رصيدك الان : *$return$*",
               'parse_mode' => "MarkDown",
            ]);
            bot('sendMessage', [
                'chat_id' => $for,
                'text' => "*✅ ⌯ تم تحويل رصيد إليك من* [$first_name](tg://user?id=$id).

💸 ⁞ المبلغ المحول : *$after_precent$*
☑️ ⁞ رصيدك الان : *$return_to$*",
              'parse_mode' => "MarkDown",
            ]);
                bot('sendMessage', [
                    'chat_id' => $dev1,
                    'text' => "*✅ ⌯ عملية تحويل جديدة في البوت.*

*👤 - من :* [$first_name](tg://user?id=$id)
*👤 - إلى : $for*

💸 ⁞ القيمة الكلية : *$text$*
💱 ⁞ العمولة : *$precent$*
💰 ⁞ المبلغ المحول : *$after_precent$*

🧿 ⁞ رصيد المرسل قبل التحويل : *$coin$*
☑️ ⁞ رصيد المرسل الان : *$return$*

🧿 ⁞ رصيد المستلم قبل التحويل : *$coin_to$*
☑️ ⁞ رصيد المستلم الان : *$return_to$*",
                  'parse_mode' => "MarkDown",
                ]);
        }else{
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*⛔ ⌯ أرسل أرقاماً فقط ،*",
'parse_mode' => "MarkDown",     
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back2
                ])
            ]);
            return;
        }
    }


if ($text && $get_jsons->{$id}->data == 'link') {
    $is_u = substr($text, 0, 1);
    $is_user = false;
    if ($is_u[0] == '@') {
        $is_user = true;
    }

    // التحقق من صحة الرابط
    if (filter_var($text, FILTER_VALIDATE_URL) === FALSE && !$is_user) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*❌ ⌯ الرابط غير صحيح ،*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back_add
            ])
        ]);
        return;
    }

    // إضافة التحقق من http و https
    if (strpos($text, 'http://') === 0) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*❌ ⌯ الرابط غير مقبول. يجب أن يحتوي على https وليس http.*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back_add
            ])
        ]);
        return;
    }

    if (strpos($text, 'https://') !== 0) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*❌ ⌯ الرابط غير مقبول. يجب أن يحتوي على https وليس http.*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back_add
            ])
        ]);
        return;
    }
        include('./sql_class.php');
        $but = $sql->sql_select('order_waiting', 'link', $text);
        if($but['link'] == 'link'){
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*❌ ⌯ لايمكنك رشق نفس الرابط اكثر من مرة ،*
*☑️ ⌯ انتظر الى ان ينتهي طلبك الاول او قم برشق رابط آخر.*",
             'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back_add
                ])
            ]);
            return;
        }
        $jsons["$id"]["data"] = 'num';
        $jsons["$id"]["link"] = $text;
        file_put_contents("data/data.json", json_encode($jsons));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*⬇️ ⌯ أرسل العدد المطلوب.*",
           'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back_add
            ])
        ]);
    }
    if($text && $get_jsons->{$id}->data == 'num'){
        if(!isint($text)){
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*⛔ ⌯ أرسل أرقاماً فقط ،*
*☑️ ⌯ يجب ان تكون من مضاعفات العدد 10 / مثال : 10 - 50 - 100 - 150 - 500 - 1000 ...*",
'parse_mode'=>"MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back_add
                ])
            ]);
            return;
        }
        if(!is_multi_ten($text)){
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*☑️ ⌯ يجب ان يكون العدد من مضاعفات العدد 10 / مثال : 10 - 50 - 100 - 150 - 500 - 1000 ...*",
               'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back_add
                ])
            ]);
            return;
        }
        include('./sql_class.php');
        $sq = $sql->sql_select('users', 'user', $id);
        $coin = $sq['coin'];
        $serv = $get_jsons->{$id}->serv;
        $codeserv = $get_jsons->{$id}->codeserv;
        $sq22 = $sql->sql_select('serv', 'codeserv', $codeserv);
        $api = $sq22['api'];
        $name = $sq22['name'];
        $num = $sq22['num'];
        $prec = $sq22['precent'];
        $g = get_serv($api, $serv);
        if (!$g){
            $jsons["$id"] = null;
            file_put_contents("data/data.json", json_encode($jsons));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*⛔ ⌯ الخدمة لم تعد متاحة ،*
*☑️ ⌯ تم إرسال تنبيه الى الادارة لحذفها من القائمة ... قم بطلب خدمة اخرى.*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back_add
                ])
            ]);
            foreach($adminss as $one){
                bot('sendMessage', [
                    'chat_id' => $dev2,
                    'text' => "*⛔ ⌯ خدمة ما لم تعد متاحة ،*

🧿 - الخدمة : *$name*
🆔 - أيدي الخدمة : *$num*
🚀 - الموقع ( API ) : *$api*",
                   'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back_add
                    ])
                ]);
            }
            return;
        }

// قراءة بيانات الخصم من ملف JSON
$discounts_file = 'data/discounts.json';
$discounts_data = json_decode(file_get_contents($discounts_file), true);

// التحقق مما إذا كان العميل يستحق الخصم
$discount = 0; // نسبة الخصم الافتراضية (بدون خصم)
if (isset($discounts_data[$chat_id])) {
    $discount = $discounts_data[$chat_id]['discount']; // الحصول على نسبة الخصم الخاصة بالعميل
}

$sqsq = $sql->sql_select('users', 'user', $id);
$mycoin = $sqsq['mycoin'];
$info_coin = get_coin_info($mycoin);
$coin_name = $info_coin[1];

$rate = $g['rate'];
$price = (($rate / 100) * $prec) + $rate; // السعر لكل 1000

// تطبيق الخصم إذا كان موجودًا
$price_discount = $price - (($price * $discount) / 100); // تطبيق الخصم
$price2 = $price_discount * $info_coin[0]; // السعر لكل 1000 بعد الخصم

$price_one = $price_discount / 1000;
$price_order = $price_one * $text;
$price_order2 = ($price_one * $text) * $info_coin[0];
$coin2 = $coin * $info_coin[0];
$coin_after = $coin - $price_order;
$coin_after2 = ($coin - $price_order) * $info_coin[0];
$min = $g['min'];
$max = $g['max'];

if ($text < $min or $text > $max) {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🧿 - الخدمة : *$name*
💸 - سعر 1K عضو : *$price2 $coin_name*
☑️ - الحد الأدنى : *$min* , الحد الأعلى : *$max*

*⚠️ - يجب ان يكون العدد محصوراً بين الحد الأدنى والحد الأعلى.*",
          'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back_add
        ])
    ]);
    return;
}

// حساب السعر لكل 1 عضو بعد الخصم
$price_per_member = ($price_discount / 1000) * $info_coin[0]; // ضرب بسعر الصرف للعملة

// حساب المبلغ المطلوب لهذا العدد
$price_needed = $price_per_member * $text;

// تحويل الرصيد الحالي حسب العملة
$coin_converted = $coin * $info_coin[0];

// تحقق من الرصيد
if ($coin < $price_needed / $info_coin[0]) { // نتحقق بالدولار لأنه الرصيد الأساسي بالدولار
    $missing_amount = $price_needed - $coin_converted; // كم ناقصه بالعملة المحلية
    $max_members_affordable = floor($coin_converted / $price_per_member); // كم عدد تقريبي يقدر يطلبه

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "*⛔ ⌯ رصيدك غير كافي لهذا الطلب.*

💸 ⌯ المبلغ المطلوب: *$price_needed $coin_name*  
💳 ⌯ رصيدك الحالي: *$coin_converted $coin_name*

📌 ⌯ ينقصك: *$missing_amount $coin_name*

" . ($max_members_affordable >= 10 ? "☑️ ⌯ يمكنك طلب حتى *$max_members_affordable* عضو بناءً على رصيدك." : "⚠️ ⌯ رصيدك لا يكفي لطلب أدنى عدد ممكن (10 أعضاء)."),

        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back_add
        ])
    ]);
    return;
}

$jsons["$id"]["data"] = 'done';
$jsons["$id"]["num"] = $text;
$jsons["$id"]["api"] = $api;
$jsons["$id"]["price_order"] = $price_order;
$jsons["$id"]["price_k"] = $price_discount; // حفظ السعر بعد الخصم
file_put_contents("data/data.json", json_encode($jsons));

bot('sendMessage', [
    'chat_id' => $chat_id,
    'text' => "🛒 ⌯ الخدمة : *$name*
🗣️ ⌯ العدد المطلوب : *$text*
💸 ⌯ سعر الطلب : *$price_order2 $coin_name*

- *بمجرد موافقتك* سيتم تقديم *الطلب وخصم المبلغ* ، ولن تستطيع *إلغاء الطلب* في أسوأ الحالات ( *تأكد من الرابط جيداً* ) ⚠️.

*⤵️︙هل أنت موافق على تقديم هذا الطلب !؟*",
  'parse_mode' => "MarkDown",
    'reply_markup' => json_encode([
        'inline_keyboard' => $ok
    ])
]);
return;
}
    /*  
    * أوامر الأدمن
    */
if($text == '/aymn' && in_array($id, $adminsAymn)){
bot('sendMessage', [
'chat_id' => $chat_id,
'text'=> "*🤵🏻︙مرحباً عزيزي* [$first_name](tg://user?id=$id) ♥️.
*☑️︙اليك أوامر الادمنيه الخاصة بك من الإدارة ⬇️.*",
'parse_mode'=>"MarkDown",
'reply_markup'=> json_encode([
'inline_keyboard'=> $adminAymns
])
]);
} 
    if (in_array($id, $adminss) || in_array($id, $adminsAymn)) {
        $json = json_decode(file_get_contents('data/admin.json'), true);
        $get_json = json_decode(file_get_contents('data/admin.json'));
        file_put_contents($AdminData,"Empty");
        if ($text == 'عمك يوسف') {
            #$members = explode("\n", file_get_contents('data/members.txt'));
            #$countuser = count($members) - 1;
            require_once('apifiles/1.php');
            require_once('apifiles/2.php');
            require_once('apifiles/3.php');
            require_once('apifiles/4.php');
            require_once('apifiles/5.php');
            require_once('apifiles/7.php');
            require_once('apifiles/10.php');
            $api = new Api();
            $balance = json_decode(json_encode($api->balance()))->balance;
            $api1 = new Api2();
            $balance1 = json_decode(json_encode($api1->balance()))->balance;
            $api2 = new Api3();
            $balance2 = json_decode(json_encode($api2->balance()))->balance;
            $api3 = new Api4();
            $balance3 = json_decode(json_encode($api3->balance()))->balance;
            $api4 = new Api5();
            $balance4 = json_decode(json_encode($api4->balance()))->balance;
            $api6 = new Api7();
            $balance6 = json_decode(json_encode($api6->balance()))->balance;
            $api9 = new Api10();
            $balance9 = json_decode(json_encode($api9->balance()))->balance;
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*🙋🏻‍♂️ ⌯ أهلاً بك عزيزي المطور في لوحة التحكم الخاصة بك.*

*☑️︙رصيد الموردين :*
*🌐 ⌯ API 1 : $balance$*
*🌐 ⌯ API 2 : $balance1$*
*🌐 ⌯ API 3 : $balance2$*
*🌐 ⌯ API 4 : $balance3$*
*🌐 ⌯ API 5 : $balance4$*
*🌐 ⌯ API 7 : $balance6$*
*🌐 ⌯ API 10 : $balance9$*
*🏆 - عملة : [ USD 💲 ].*

*🤖︙لتشغيل البوت ⌯ /run ✅.*
*🤖︙لتعطيل البوت ⌯ /stop ⛔.*

*✔️︙تشغيل الاشتراك ⌯ /runchannel ✅.*
*✖️︙تعطيل الاشتراك ⌯ /stopchannel ⛔.*

*✔️︙لحظر عضو ⌯ /ban id ✅.*
*✖️︙لإلغاء حظر عضو ⌯ /unban id ⛔.*

*🧿︙جلب معلومات عضو ⌯ /get_user id*
*🧿︙جلب معلومات خدمة ⌯ /get_serv #id*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $admin_button
                ])
            ]);
            return;
        }
if($EngAymnsh7n[0] == "اضف"){
include('./sql_class.php');
$EngAymnM3lomat = $sql->sql_select('users', 'user', $EngAymnsh7n[1]);
$EngMoney = $EngAymnM3lomat['coin'];
$EngCharge = $EngAymnM3lomat['charge'];
$EngAl3mlh = $EngAymnM3lomat['mycoin'];
$EngAl3mlhinfo = get_info_coin($EngAl3mlh);
$EngAl3mlhName = $EngAl3mlhinfo[1];
$EngAymnSh7n2 = $EngAymnsh7n[2] / $EngAymnsh7n[3];
$EngVip = get_vip($EngCharge);
$EngVip2 = ($EngAymnSh7n2 / 100) * $EngVip;
$EngAldorafyVip = $EngVip2 + $EngAymnSh7n2;
$EngAldorafyAfterVip = $EngMoney + $EngAldorafyVip;
$EngAldorafyCharge = $EngCharge + $EngAymnSh7n2;
$EngAldorafyVipAfterCharge = get_vip($EngAldorafyCharge);
$EngAlmblghAlmsh7on = $EngAymnSh7n2 * $EngAl3mlhinfo[0];
$EngNsbhAlziadh = $EngVip2 * $EngAl3mlhinfo[0];
$EngAlrsedB3dAlziadh = $EngAldorafyAfterVip * $EngAl3mlhinfo[0];
$sql->sql_edit('users','coin',$EngAldorafyAfterVip,'user',$EngAymnsh7n[1]);
$sql->sql_edit('users','charge',$EngAldorafyCharge,'user',$EngAymnsh7n[1]);
            if(!in_array($EngAymnsh7n[1], $exmembers)){
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*⛔ ⌯ تعذر الإرسال ،*
*🪗 ⌯ العضو ليس موجود في قائمة المستخدمين.*",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back
                    ])
                ]);
                return;
            }
bot('sendMessage', [
'chat_id'=>$chat_id,
'text'=> "*✅︙تم إعادة شحن حساب العميل بنجاح...

💰 ⌯ الرصيد المشحون : *$EngAlmblghAlmsh7on
☑️ ⌯ رصيده الآن : *$EngAlrsedB3dAlziadh $EngAl3mlhName.*
               ",
'parse_mode'=>"MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
bot('sendMessage', [
'chat_id'=>$EngAymnsh7n[1],
'text'=> "
🔄 ⌯ تم إعادة شحن حسابك بـمـبـلـغ : *$EngAlmblghAlmsh7on $EngAl3mlhName.*
☑️ ⌯ رصيدك الآن : *$EngAlrsedB3dAlziadh $EngAl3mlhName.*
               ",
'parse_mode'=>"MarkDown",
]);
bot('sendMessage', [
                'chat_id' => $dev1,
                'text' => "*✅︙عملية شحن رصيد جديدة.*

*🧑🏻‍💻 ⌯ الادمن الذي شحن* : [$first_name](tg://user?id=$id).
👤 ⌯ المستلم : *$EngAymnsh7n[1]*
💰 ⌯ الرصيد المشحون : *$EngAymnSh7n2 💲.*
💸 ⌯ أصبح رصيده بعد الشحن : *$EngAldorafyAfterVip 💲.*
☑️ ⌯ ورصيد زيادة النسبة : *$EngVip2 💲.*",
                'parse_mode' => "MarkDown",
            ]);
if($EngVip != $EngAldorafyVipAfterCharge && $EngAldorafyVipAfterCharge != 0){
bot('sendMessage', [
                    'chat_id' => $EngAymnsh7n[1],
                    'text' => "*� ⌯ تم ترقية مستوى حسابك ،*
*✅ ⌯ أصبح مستوى حسابك VIP$EngAldorafyVipAfterCharge ، ستحصل الان على نسبة $EngAldorafyVipAfterCharge% عند كل عملية شحن 💸.*",
                    'parse_mode' => "MarkDown",
                ]);
            }
        }
        if($extext[0] == '/pro' && in_array($id, $adminss)){
            $del = str_replace($extext[1], '', $is_no);
            file_put_contents('data/is_no.txt', $del);
            file_put_contents('data/is_ok.txt', $extext[1]."\n", FILE_APPEND);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅︙تم بنجاح.",
            ]);
            bot('sendMessage', [
                'chat_id' => $extext[1],
                'text' => "☑️ ⌯ تم التحقق من حسابك
ارسل /start للمواصلة ✅.",
            ]);
            return;  
        }
        if($extext[0] == '/get_user' && in_array($id, $adminss)){
            include('./sql_class.php');
            $us = $sql->sql_select('users', 'user', $extext[1]);
            #coin,user,spent,charge
            $coin = $us['coin'];
            $charge = $us['charge'];
            $spent = $us['spent'];
            $fromuser = $us['fromuser'];
            $coinfromuser = $us['coinfromuser'];
            $vip = get_vip($charge);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅ ⌯ تم جلب معلومات المستخدم ⬇️.*
                
👤︙المستخدم : `".$extext[1]."`
💸︙رصيده الحالي : *$coin$*
💰︙رصيده المصروف : *$spent$*
🔄︙رصيده المشحون : *$charge$*
🔝︙مستوى حسابه : *VIP$vip*

🧿︙رصيده المجموع من رابطه : *$coinfromuser$*
♻️︙تمت دعوته الى البوت من قبل : *$fromuser*",
                 'parse_mode' => "MarkDown",
            ]);
            return;  
        }
        if($extext[0] == '/get_serv' && in_array($id, $adminss)){
            include('./sql_class.php');
            $us = $sql->sql_select('serv', 'codeserv', $extext[1]);
            $name = $us['name'];
            $code = $us['code'];
            $cap = $us['caption'];
            $num = $us['num'];
            $api = $us['api'];
            $prec = $us['precent'];
            $serv_but = $sql->sql_select('buttons', 'code', $code);
            $name_but = $serv_but['name'];
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*✅ ⌯ تم جلب معلومات الخدمة ⬇️.*

🧿︙اسم الخدمة : *$name*
🪗︙تابعة للقسم : *$name_but*
📄︙وصف الخدمة : *$cap*
🆔︙أيدي الخدمة : *$num*
🚀︙الموقع ( API ) : *$api*
💸︙نسبة الربح : *$prec%*",
'parse_mode'=>"MarkDown",
            ]);
            return;  
        }
        if($extext[0] == '/ban' && in_array($id, $adminss)){
            file_put_contents("data/ban.txt", $extext[1]."\n", FILE_APPEND);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم حظر العضو.*",
                'parse_mode'=>"MarkDown",
            ]);
            bot('sendMessage', [
                'chat_id' => $extext[1],
                'text' => "*🤖 ⌯ قامت الإدارة بحظر حسابك.!*
*☑️︙اذا كنت تعتقد ان هذا عن طريق الخطأ فقم بمراسلة الإدارة* @Y5_5C",
'parse_mode'=>"MarkDown",
            ]);
            return;  
        }
        if($extext[0] == '/unban' && in_array($id, $adminss)){
            $f = file_get_contents("data/ban.txt");
            $f = str_repeat($extext[1], '', $f);
            file_put_contents("data/ban.txt", $f);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم إلغاء حظر العضو.*",
                'parse_mode'=>"MarkDown",
            ]);
            bot('sendMessage', [
                'chat_id' => $extext[1],
                'text' => "*✅︙تهانينا ،*
*🎉︙تم إلغاء حظرك من البوت.*",
'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        if($text && $get_json->data == 'addsub' && in_array($id, $adminss)){
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            $json_config["channel"] = $text;
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*☑️︙تم تعيين قناة الاشتراك بنجاح ،*
*🪗︙القناة : $text .*",
'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        if($text == '/runchannel' && in_array($id, $adminss)){
            $json_config["runchannel"] = 'run';
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تشغيل الاشتراك.*",
                'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        if($text == '/stopchannel' && in_array($id, $adminss)){
            $json_config["runchannel"] = 'stop';
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تعطيل الاشتراك.*",
                'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        if($text == '/run' && in_array($id, $adminss)){
            $json_config["run"] = 'run';
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تشغيل البوت.*",
                'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        if($text == '/stop' && in_array($id, $adminss)){
            $json_config["run"] = 'stop';
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تعطيل البوت.*",
                'parse_mode'=>"MarkDown",
            ]);
            return;
        }
        /*
        * start
        */
        if ($text and $get_json->data == 'addstart' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            $json_config["start"] = $text;
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تعيين start.*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
return;
}
      /*
        * نقاط الدخول
        */
        if ($text and $get_json->data == 'addinvite' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            if(isint($text)){
                $json_config["invite"] = $text;
                file_put_contents("data/config.json", json_encode($json_config));
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*✅︙تم تنفيذ طلبك وتم تعيين نقاط الدخول.*",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back
                    ])
                ]);
            }
        }
        /*
        * الدليل
        */
        if ($text and $get_json->data == 'addhelp' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            $json_config["help"] = $text;
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم تعيين الشروط.*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * إضافة رصيد
        */
        if ($text and $get_json->data == 'addbalance' && in_array($id, $adminss)) {
            if(!in_array($text, $exmembers)){
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*⛔ ⌯ تعذر الإرسال ،*
*🪗 ⌯ العضو ليس موجود في قائمة المستخدمين.*",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back
                    ])
                ]);
                return;
            }
            $json["data"] = 'addbalance2';
            $json["id"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*✅︙أرسل مبلغ الرصيد المراد شحنه الى العضو.*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }


        if ($text and $get_json->data == 'addbalance2' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            include('./sql_class.php');
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"Failed to connect to MySQL: " . mysqli_connect_error(),
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            $us = $sql->sql_select('users', 'user', $get_json->id);
            $coin = $us['coin'];
            $charge = $us['charge'];
            $fromuser = $us['fromuser'];
            if ($fromuser != 'None' && $fromuser != null){
                $us_fromuser = $sql->sql_select('users', 'user', $fromuser);
                $coin_fromuser = $us_fromuser['coin'];
                $prec_from = ($text / 100) * 2;
                $all_coin_fromuser = $us_fromuser['coinfromuser'] + $prec_from;
                $coin_fromuser_after = $prec_from + $coin_fromuser;
                $sql->sql_edit('users', 'coin', $coin_fromuser_after, 'user', $fromuser);
                $sql->sql_edit('users', 'coinfromuser', $all_coin_fromuser, 'user', $fromuser);
                bot('sendMessage', [
                    'chat_id' => $fromuser,
                    'text' => "*☑️︙عضو من الذين دعوتهم للبوت قد شحن حسابه ،*
*🎉︙تم اضافة نسبة 2% من المبلغ الذي شحنه*

💸︙تم اضافة : *$prec_from$*
💰︙إجمالي رصيد الدعوة : *$all_coin_fromuser$*",
                    'parse_mode' => "MarkDown",
                ]);
            }
            $vip = get_vip($charge);
            $pr = ($text / 100) * $vip;
            $af_prec = $text + $pr;
            $return = $coin + $af_prec;
            $us = $sql->sql_select('users', 'user', $get_json->id);
$TH3AYMN = $us['mycoin'];
$TH4AYMN = get_coin_info($TH3AYMN);
$TH5AYMN = $TH4AYMN[1];
$ENGAYMNC = $return * $TH4AYMN[0];
$ENGAYMNX = $text * $TH4AYMN[0];
$ENGAYMNZ = $pr * $TH4AYMN[0];
            $after_charge = $charge + $text;
            $vip_after = get_vip($after_charge);
            $us = $sql->sql_edit('users', 'coin', $return, 'user', $get_json->id);
            $us = $sql->sql_edit('users', 'charge', $after_charge, 'user', $get_json->id);
            bot('sendMessage', [
                'chat_id' => $chat_id,
'text' => "*✅︙تم شحن الرصيد بنجاح.*

👤 ⌯ العميل : ".$get_json->id."
💰 ⌯ الرصيد المشحون : *$ENGAYMNX $TH5AYMN*
🏆 ⌯ مستوى حسابه : *VIP$vip*
🪗 ⌯ نسبة الزيادة : *$vip%*
💸 ⌯ مبلغ الزيادة : *$ENGAYMNZ $TH5AYMN*
☑️ ⌯ رصيده بعد الزيادة : *$ENGAYMNC $TH5AYMN*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
            bot('sendMessage', [
                'chat_id' => $get_json->id,
                'text' => "*✅︙ تم شحن رصيدك بنجاح.*
👤︙ بواسطة : [$first_name](tg://user?id=$id).
                
💸︙الرصيد المشحون بعملتك  : *$ENGAYMNX $TH5AYMN*
💸︙الرصيد المشحون بالدولار : *$text* 💲

🪗 ⌯ نسبة الزيادة : *$vip%*
💸 ⌯ مبلغ الزيادة بعملتك : *$ENGAYMNZ $TH5AYMN*
💸 ⌯ مبلغ الزيادة بالدولار : *$pr* 💲

☑️︙رصيدك الان بعملتك : *$ENGAYMNC $TH5AYMN*
☑️︙رصيدك الآن بالدولار : *$return* 💲",
                'parse_mode' => "MarkDown",
            ]);
            $gg = $get_json->id;
            bot('sendMessage', [
                'chat_id' => $dev1,
                'text' => "*✅︙عملية شحن رصيد جديدة.*

*🧑🏻‍💻 ⌯ الادمن الذي شحن* : [$first_name](tg://user?id=$id).
👤 ⌯ المستلم : *$gg*
💰 ⌯ الرصيد المشحون : *$text$*
💸 ⌯ أصبح رصيده بعد الشحن : *$return*
☑️ ⌯ ورصيده بعد زيادة النسبة : *$af_prec$*",
                'parse_mode' => "MarkDown",
            ]);
            $best_users = explode("\n", file_get_contents('data/best_users.txt'));
            if(!in_array($get_json->id, $best_users)){
                file_put_contents('data/best_users.txt', $get_json->id."\n", FILE_APPEND);
                bot('sendMessage', [
                    'chat_id' => $get_json->id,
                    'text' => "*🎉 ⌯ تهانيا عزيزي العميل ،*
*☑️ ⌯ تم ترقية حسابك وستحصل على زيادة بالنسبة عند شحن الرصيد.*",
                    'parse_mode' => "MarkDown",
                ]);
            }
            if($vip != $vip_after && $vip_after != 0){
                bot('sendMessage', [
                    'chat_id' => $get_json->id,
                    'text' => "*🪗 ⌯ تم ترقية مستوى حسابك ،*
*✅ ⌯ أصبح مستوى حسابك VIP$vip_after ، ستحصل الان على نسبة $vip_after% عند كل عملية شحن 💸.*",
                    'parse_mode' => "MarkDown",
                ]);
            }
            return;
        }
if($text && $get_jsons->{$id}->data == 'mystat'){
        $jsons["$id"]["data"] = 'mystat';
        file_put_contents("data/data.json", json_encode($jsons));
      include('./sql_class.php');
$EngAymntlb = $sql->sql_select('users', 'user', $id);
$EngAymntlbih = $sql->sql_select('order_done', 'order_id', $text);
$EngYousseftlbih = $sql->sql_select('order_waiting', 'order_id', $text);
$EngAymn3mlh = $EngAymntlb['mycoin'];
$EngAymn3mlh2 = get_coin_info($EngAymn3mlh);
$EngAymn3mlh3 = $EngAymn3mlh2[1];
$EngAymnPrice = $EngAymntlbih['price'];
$EngAymnTalb = $EngAymntlbih['user'];
$EngAymn3dd = $EngAymntlbih['num_order'];
$EngAymnType = $EngAymntlbih['type'];
$EngYoussefType = $EngAymntlbih['caption'];
$EngYoussefTlbih = $EngYousseftlbih ['caption'];
$EngAymnS3r = $EngAymnPrice * $EngAymn3mlh2[0];
if($EngAymnType == 'Completed'){
	$EngAymn7alh = " • مكتمل ✅.";
	}if($EngAymnType == 'Canceled'){
	$EngAymn7alh = " • ملغي ⛔.";
	}if($EngAymnType == 'Partial'){
	$EngAymn7alh = " • جزئي ☑️.";
	}if($EngAymnType == 'In progress'){
	     $EngAymn7alh = " • قيد التنفيذ ⏰";
	}
	if($EngAymnTalb != $id){
		bot('sendMessage', [
		'chat_id'=> $chat_id,
		'text'=> "*⛔ ⌯ هذا الطلب ليس لك.*",
		'parse_mode'=>"MarkDown",
		'reply_markup'=>json_encode([
		'inline_keyboard'=> $back2
		])
		]);
return;
		}
		bot('sendMessage', [
		'chat_id'=> $chat_id,
		'text'=>"*☑️︙تم جلب معلومات طلبك بنجاح.*
		
		🧿 ⌯ رقم الطلب : *$text*
		🎲 ⌯ حالة الطلب : *$EngAymn7alh*
		✅ ⌯ تفاصيل الطلب : \n\n$EngYoussefType",
		'parse_mode'=>"MarkDown",
		'reply_markup'=>json_encode([
		'inline_keyboard'=>[
		[['text'=>"⭐ • عمل تعويض للطلب.",'callback_data'=>"EngAymnT3oid|".$text]],
		]
		])
		]);
		}
        /*
        * حذف رصيد
        */
        if ($text and $get_json->data == 'delbalance' && in_array($id, $adminss)) {
            if(!in_array($text, $exmembers)){
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*⛔ ⌯ تعذر الخصم ،*
*🪗 ⌯ العضو ليس موجود في قائمة المستخدمين.*",
                    'parse_mode' => "MarkDown",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $back
                    ])
                ]);
                return;
            }
            $json["data"] = 'delbalance2';
            $json["id"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*✅︙أرسل مبلغ الرصيد المراد خصمه من العضو.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'delbalance2' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            include('./sql_class.php');
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"Failed to connect to MySQL: " . mysqli_connect_error(),
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            
            $us = $sql->sql_select('users', 'user', $get_json->id);
            $coin = $us['coin'];
            $return = $coin - $text;
            $us = $sql->sql_edit('users', 'coin', $return, 'user', $get_json->id);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تنفيذ طلبك وتم خصم الرصيد. *",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
            bot('sendMessage', [
                'chat_id' => $get_json->id,
                'text' => "*⛔︙تم الخصم من رصيدك.*
*👤 ⌯ بواسطة* : [$first_name](tg://user?id=$id).

💸 ⌯ الرصيد المخصوم : *$text$*
☑️ ⌯ رصيدك الان : *$return$*",
                'parse_mode' => "MarkDown",
            ]);
            $gg = $get_json->id;
            bot('sendMessage', [
                'chat_id' => $dev1,
                'text' => "*⛔︙عملية خصم رصيد جديدة.*

*🧑🏻‍💻 ⌯ الادمن الذي خصم* : [$first_name](tg://user?id=$id).
👤 ⌯ العضو الذي خُصِم عليه : *$gg*
💰 ⌯ الرصيد المخصوم : *$text$*
💸 ⌯ أصبح رصيده بعد الخصم : *$return*
",
                'parse_mode' => "MarkDown",
            ]);
        }
        /*
        * نسبة التحويل
        */
        if ($text and $get_json->data == 'sel' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            $json_config["sel"] = $text;
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تعيين نسبة تحويل الرصيد ،*
*🪗︙نسبة التحويل $text% .*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
            return;
        }
        /*
        * أدنى حد للتحويل
        */
        if ($text and $get_json->data == 'selmin' && in_array($id, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            $json_config["selmin"] = $text;
            file_put_contents("data/config.json", json_encode($json_config));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅︙تم تعيين الحد الادنى لتحويل الرصيد ،*
*🪗︙الحد الأدنى : $text% .*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
            return;
        }
            
        /*
        * إضافة قسم
        */

        if ($text and $get_json->data == 'addcoll') {
            $json["data"] = 'addcoll2';
            $json["name"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
    *☑️ ⌯ تم اضافة القسم ،*
🪗 ⌯ الإسم : *$text*

⬇️ ⌯ أرسل الان *وصف القسم.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'addcoll2') {
            $json["data"] = 'addcoll3';
            $json["caption"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*✅ ⌯ تم تعيين وصف القسم ،*
🪗 *⌯* الوصف : *$text*

⬇️ ⌯ إضغط */ok* لتأكيد الإضافة ☑️.",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }

        if ($text == '/ok' && $get_json->data == 'addcoll3') {
            $code = rand_text();
            include("./sql_class.php");
            $sql = new mysql_api_code($db);
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"Failed to connect to MySQL: " . mysqli_connect_error(),
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            $name = $get_json->name;
            $api = $get_json->api;
            $caption = $get_json->caption;
            $sql->sql_write('buttons(code,name,caption)', "VALUES('$code','$name','$caption')");
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*✅︙تم تنفيذ طلبك وتم إضافة القسم.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
            return;
        }
        if ($text == '/ok' && $get_json->data != 'addcoll2') {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
                *⛔ ⌯ خطأ ،*
*🪗 ⌯ البيانات ليست كافية لإتمام الإضافة.*
                ",
                'parse_mode' => "MarkDown",
            ]);
        }

        /*
        * إضافة قسم عادي
        */
        if ($text and $get_json->data == 'adddivi1') {
            $json["data"] = 'adddivi2';
            $json["name"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
                *☑️ ⌯ تم اضافة القسم ،*
🪗 ⌯ الإسم : *$text*

⬇️ ⌯ أرسل الان *وصف القسم.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }

        if ($text and $get_json->data == 'adddivi2') {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            include("./sql_class.php");
            $sql = new mysql_api_code($db);
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"Failed to connect to MySQL: " . mysqli_connect_error(),
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            $code = rand_text();
            $name = $get_json->name;
            $codedivi = $get_json->codedivi;
            $caption = $text;
            $sql->sql_write('divi(code,name,codedivi,caption)', "VALUES('$code','$name', '$codedivi', '$caption')");
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
                *✅︙تم تنفيذ طلبك وتم إضافة القسم.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }


        /*
        * إضافة خدمة
        */
        if ($text and $get_json->data == 'addserv1') {
            $json["data"] = 'addserv2';
            $json["name"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
                *☑️ ⌯ إسم الخدمة : $text ،*
*⬇️ ⌯ أرسل الان أيدي الخدمة في الموقع.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'addserv2') {
            $json["data"] = 'addserv3';
            $json["num"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*☑️︙أرسل رقم ال API الان.*
*🧿︙1 - 2 - 3*",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'addserv3') {
            $json["data"] = 'addserv4';
            $json["api"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
                *🪗︙أرسل الان وصف الخدمة.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'addserv4') {
            $json["data"] = 'addserv5';
            $json["caption"] = $text;
            file_put_contents("data/admin.json", json_encode($json));
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*💸︙أرسل الان نسبة الربح.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        if ($text and $get_json->data == 'addserv5') {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            include("./sql_class.php");
            $sql = new mysql_api_code($db);
            if (mysqli_connect_errno()) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' =>"Failed to connect to MySQL: " . mysqli_connect_error(),
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                return;
            }
            $codeserv = rand_text();
            $name = $get_json->name;
            $code = $get_json->code;
            $num = $get_json->num;
            $api = $get_json->api;
            $max = $get_json->max;
            $caption = $get_json->caption;
            $precent = $text;
            $sql->sql_write('serv(code,name,codeserv,num,api,caption,precent)', "VALUES('$code','$name', '$codeserv', '$num', '$api', '$caption','$precent')");
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
*✅︙تم تنفيذ طلبك وتم إضافة الخدمة بنجاح.*
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
    }
}

if ($data) {

    if(!in_array($id2, $adminss)){
        if (in_array($id2, $ex_is_no) or in_array($id2, $bans)) {
            bot('sendmessage', [
                'chat_id' => $chat_id2,
                'text' => "*⛔ ⌯ يبدو أنك محظور من إستخدام البوت ،*
⚠️ ⌯ إذا كنت تعتقد أنه تم حضرك من إستخدام البوت *عن طريق الخطأ* فقم بمراسلة الادارة : *$aymn ☑️.*",
                'parse_mode'=>"MarkDown",
            ]);
            return;
        } 
    }
    /*  
    * أوامر الأدمن
    */
    if (in_array($id2, $adminss) || in_array($id2, $adminsAymn)){
        $json = json_decode(file_get_contents('data/admin.json'), true);
        $get_json = json_decode(file_get_contents('data/admin.json'));

        /*
        * تعيين start
        */
        if($data == 'addstart'){
            $json["data"] = 'addstart';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال رسالة الستارت الان.*",
                'disable_web_page_preview' => true,
                'parse_mode'=>"MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * تعيين نقاط الدخول
        */
        if($data == 'addinvite'){
            $json["data"] = 'addinvite';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال نقاط الدخول الان.*",
                'disable_web_page_preview' => true,
                'parse_mode'=>"MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * تعيين الدليل
        */
        if($data == 'addhelp'){
            $json["data"] = 'addhelp';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال الشروط والخصوصية الان.*",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * تعيين قناة الاشتراك
        */
        if($data == 'addsub'){
            $json["data"] = 'addsub';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال معرف القناة الان.*",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
       /*
        * كشف العضو 
        */
        /*
        * نسبة التحويل
        */
        if($data == 'sel'){
            $json["data"] = 'sel';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال نسبة عمولة التحويل الان.*",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * الحد الأدنى
        */
        if($data == 'selmin'){
            $json["data"] = 'selmin';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال نسبة الحد الأدنى للتحويل الان.*",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * إضافة رصيد
        */
        if($data == 'addbalance'){
            $json["data"] = 'addbalance';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال أيدي المستخدم المراد إعادة تعبئة رصيده الان.*",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * حذف رصيد
        */
        if($data == 'delbalance'){
            $json["data"] = 'delbalance';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'parse_mode'=>"MarkDown",
                'text' => "*⬇️ ⌯ قم بإرسال أيدي المستخدم المراد الخصم من رصيده الان.*",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }

        /*
        * إضافة قسم رئيسي
        */
        if ($data == "addcoll") {
            $json["data"] = 'addcoll';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإرسال أسم القسم الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * إضافة قسم عادي
        */
        if ($data == "adddivi") {
            $json["data"] = 'adddivi';
            file_put_contents("data/admin.json", json_encode($json));
            include('./sql_class.php');
            $but = $sql->sql_readarray('buttons');
            $serv = [];
            foreach ($but as $button) {
                $code = $button['code'];
                $name = $button['name'];
                $serv[] = [['text' => $name, 'callback_data' => "codedivi|".$code]];
            }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإختيار القسم الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        
        /*
        * رجوع
        */
        if ($data == "back" && in_array($id2, $adminss)) {
            $json["data"] = null;
            file_put_contents("data/admin.json", json_encode($json));
            file_put_contents($AdminData,"Empty");
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*🙋🏻‍♂️ ⌯ أهلاً بك عزيزي المطور في لوحة التحكم الخاصة بك.*

*☑️︙رصيد الموردين :*
*💸 ⌯ مورد 1 : $balance$*
*💸 ⌯ مورد 2 : $balance1$*
*💸 ⌯ مورد 3 : $balance2$*
*🏆 - عملة : [ USD 💲 ].*

*🤖︙لتشغيل البوت ⌯ /run ✅.*
*🤖︙لتعطيل البوت ⌯ /stop ⛔.*

*✔️︙تشغيل الاشتراك ⌯ /runchannel ✅.*
*✖️︙تعطيل الاشتراك ⌯ /stopchannel ⛔.*

*✔️︙لحظر عضو ⌯ /ban id ✅.*
*✖️︙لإلغاء حظر عضو ⌯ /unban id ⛔.*

*🧿︙جلب معلومات عضو ⌯ /get_user id*
*🧿︙جلب معلومات خدمة ⌯ /get_serv #id*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $admin_button
                ])
            ]);
        }
if(in_array($id2, $adminsAymn) && $data == 'back'){
bot('editmessagetext', [
'chat_id'=>$chat_id2,
'message_id'=>$message_id2,
'text'=> "*🤵🏻︙مرحبا بك عزيزي* [$first_name](tg://user?id=$id2) ♥️.
*☑️︙إليك لوحة التحكم الخاصة بك من الإدارة ⬇️.*",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>$adminAymns
])
]);
}

        /*
        * إضافة خدمة
        */
        if ($data == "addserv") {
            include('./sql_class.php');
            $but = $sql->sql_readarray('buttons');
            $serv = [];
foreach($but as $butt){
                $code = $butt['code'];
                $name = $butt['name'];
                $serv[] = [['text' => $name, 'callback_data' =>"codedivi|".$code]];
         }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            $json["data"] = 'addserv';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإختيار القسم الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        if ($exdata[0] == 'codedivi' && $get_json->data == 'addserv') {
            include('./sql_class.php');
            $but = $sql->sql_select_all('divi','codedivi', $exdata[1]);
            $serv = [];
foreach($but as $butt){
                $code = $butt['code'];
                $name = $butt['name'];
                $serv[] = [['text' => $name, 'callback_data' =>"codeserv|".$code]];
         }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            $json["data"] = 'addservy';
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ قم بإختيار النوع الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        /*
        * اختيار قسم رئيسي لاإضافة قسم عادي
        */
        if($exdata[0] == 'codedivi' && $get_json->data == 'adddivi'){
            $json["data"] = 'adddivi1';
            $json["codedivi"] = $exdata[1];
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*☑️ ⌯ تم إختيار القسم الرئيسي ،*
*⬇️ ⌯ قم بأرسال اسم القسم العادي الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * اختيار قسم لإضافة الخدمة
        */
        if($exdata[0] == 'codeserv' && $get_json->data == 'addservy'){
            $json["data"] = 'addserv1';
            $json["code"] = $exdata[1];
            file_put_contents("data/admin.json", json_encode($json));
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*☑️ ⌯ تم إختيار القسم العادي ،*
*⬇️ ⌯ قم بارسال اسم الخدمه الان.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * حذف قسم رئيسي
        */
        if ($data == "delcoll") {
            include('./sql_class.php');
            $but = $sql->sql_readarray('buttons');
            $serv = [];
            foreach ($but as $button) {
                $code = $button['code'];
                $name = $button['name'];
                $serv[] = [['text' => $name, 'callback_data' => "delcollserv|".$code]];
            }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*🪗 ⌯ اختر القسم الرئيسي ليتم حذفه ،*
*⚠️ ⌯ عند حذف قسم رئيسي سيتم حذف جميع الخدمات التي يحتويها.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        if ($exdata[0] == 'delcollserv'){
            include('./sql_class.php');
            $sql->sql_del('buttons', 'code', $exdata[1]);
            $s = $sql->sql_select_all('divi', 'codedivi', $exdata[1]);
            $arr = [];
            foreach($s as $b ){
                $c = $b['code'];
                if(in_array($c, $arr)){
                    continue;
                }
                $sql->sql_del('serv', 'code', $c);
                $arr [] = $c;
            }
            $sql->sql_del('divi', 'codedivi', $exdata[1]);
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*✅︙تم تنفيذ طلبك وتم حذف القسم.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }

        /*
        * حذف قسم عادي
        */
        if ($data == "deldivi") {
            include('./sql_class.php');
            $but = $sql->sql_readarray('divi');
            $serv = [];
            foreach ($but as $button) {
                $code = $button['code'];
                $name = $button['name'];
                $serv[] = [['text' => $name, 'callback_data' => "deldiviserv|".$code]];
            }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*🪗 ⌯ اختر القسم العادي ليتم حذفه ،*
*⚠️ ⌯ عند حذف قسم سيتم حذف جميع الخدمات التي يحتويها.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        if ($exdata[0] == 'deldiviserv'){
            include('./sql_class.php');
            $sql->sql_del('divi', 'code', $exdata[1]);
            $sql->sql_del('serv', 'code', $exdata[1]);
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*✅︙تم تنفيذ طلبك وتم حذف القسم.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
        /*
        * حذف خدمة
        */
        if ($data == 'delserv'){
            include('./sql_class.php');
            $but = $sql->sql_readarray('divi');
            $serv = [];
            foreach ($but as $button) {
                $code = $button['code'];
                $name = $button['name'];
                $serv[] = [['text' => $name, 'callback_data' => "getserv|".$code]];
            }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ إختر القسم المراد حذف خدمه منه.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        if ($exdata[0] == 'getserv'){
            include('./sql_class.php');
            $but = $sql->sql_select_all('serv', 'code', $exdata[1]);
            $serv = [];
            foreach ($but as $ser) {
                $code = $ser['codeserv'];
                $name = $ser['name'];
                $serv[] = [['text' => $name, 'callback_data' => "delservfromcoll|".$code]];
            }
            $serv[] = [['text' => "إلغاء ورجوع", 'callback_data' => "back"]];
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*⬇️ ⌯ إختر الخدمة المراد حذفها.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $serv
                ])
            ]);
        }
        if ($exdata[0] == 'delservfromcoll'){
            include('./sql_class.php');
            #$sql->sql_del('buttons', 'code', $exdata[1]);
            $sql->sql_del('serv', 'codeserv', $exdata[1]);
            bot('editmessagetext', [
                'chat_id' => $chat_id2,
                'message_id' => $message_id2,
                'text' => "*✅︙تم تنفيذ طلبك وتم حذف الخدمة.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back
                ])
            ]);
        }
    
    }
    /*  
    * أوامر الأعضاء
    */
    if($data == 'changecoin'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*🧑🏻‍💼︙مرحباً عزيزي* : [$first_name](tg://user?id=$id2) 🖤.
            
☑️︙أنت *الآن* في قسم *[ 🪙 ⪼ تغيير العملة ]* ،

- في حال *لم تجد* عملة *بلدك* بالأسفل *ننصح* بإختيار عملة *[ الروبل ₽ ]* أو *[ الدولار 💲 ]*

*⤵️︙إختر الان العملة المراد تعيينها عملة حسابك 👇🏻.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $changecoin
            ])
        ]);
    }
    if($exdata[0] == 'selectcoin'){
    include('./sql_class.php');
    
    // التحقق من الاتصال بقاعدة البيانات
    if (mysqli_connect_errno()) {
        return;
    }
    
    // تحديث نوع العملة في قاعدة البيانات للمستخدم
    $sql->sql_edit('users', 'mycoin', $exdata[1], 'user', $id2);
    
    // دالة للحصول على اسم العملة بناءً على رمز العملة
    function get_currency_name($currencyCode) {
        $currencyNames = [
            'usd'   => 'الدولار الأمريكي 💲.',
            's'     => 'الريال السعودي 🇸🇦.',
            'y'     => 'الريال اليمني القديم 🇾🇪.',
            'd'     => 'الدينار العراقي 🇮🇶',
            'Aymn'  => 'عملة تايجر سبيد ♠️',
            'j'     => 'الجنيه المصري 🇪🇬',
            'r'     => 'درهم إماراتي 🇦🇪',
            'g'     => 'الريال القطري 🇶🇦',
            'o'     => 'الريال اليمني الجديد 🇾🇪',
            'saba'  => 'وحدات سبأفون',
            'ruble' => 'الروبل الروسي 🤖'
        ];

        // التحقق مما إذا كانت العملة موجودة في القائمة
        return $currencyNames[$currencyCode] ?? 'عملة غير معروفة';
    }

    // الحصول على اسم العملة من الدالة
    $name_coin = get_currency_name($exdata[1]);

    // إرسال رسالة إلى المستخدم لتأكيد اختيار العملة
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "
*✅︙تم إختيار نوع العملة بنجاح.*

*🌪️︙العملة : $name_coin*

-
",
        'parse_mode' => "MarkDown",
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => $back2
        ])
    ]);
}

if($data == 'damfni'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*☑️︙يمكنك مشاهدة التعليمات والشروحات من خلال الضغط على الازرار بالأسفل.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $damfni
            ])
        ]);
    }
    
    if($data == 'buymoney'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*☑️︙اهلاً بك عزيزي في قسم شحن الرصيد 💵.*
            
- بالنسبة *لإسعار* الشحن ، *لايوجد* أسعار *محددة* ، فمثلاً قمت *بتحويل* 5$ عبر *محفظة* إلكترونية ، *سيتم* شحن *حسابك* بـ 5$ *بدون خصم* أي *ضرائب* ، وعند *تغيير العملة* ستتغير حسب *مصارفة الدولار* ✅.

- للشحن *إضغط* على *[ 💸 ⪼ أسعار الشحن وطرق الدفع ]* ثم قم *بالتحويل* على *الطريقة* التي *تناسبك* ثم *إضغط* على
*[ ✅ ⪼ إرسال صورة الإيصال ]* وأرسل *الصورة* لعملية *التحويل.*
",
            'parse_mode' => "MarkDown",
   'disable_web_page_preview' => true,    
            'reply_markup' => json_encode([
                'inline_keyboard' => $YoussefBin
            ])
        ]);
    }

if($data == 'Aymnfree'){

                    include_once('./sql_class.php');
                    if (mysqli_connect_errno()) {
                        return;
                    }
                    $jsonsstart["$id"] = null;
                    file_put_contents("data/cache.json", json_encode($jsonsstart));
                    $us = $sql->sql_select('users', 'user', $get_s);
                    $coin = $us['coin'];
                    $invite = $config->invite;
                    $return = $coin + $invite;
                    $us = $sql->sql_edit('users', 'coin', $return, 'user', $get_s);
$all = count($exmembers);
        $best_userss = explode("\n", file_get_contents('data/best_users.txt'));
        $coin_users = $sql->sql_readarray('users');
        $coin_all = 0;
        $coin_spent = 0;
        foreach($coin_users as $coins){
            $coin = $coins['coin'];
            $spent = $coins['spent'];
            $user = $coins['user'];
            $charge = $coins['charge'];
            $coinfromuser = $coins['coinfromuser'];
            if($id2 == $user){
                $us_coin = $coin;
                $us_spent = $spent;
                $us_charge = $charge;
                $coin_from_user = $coinfromuser;
            }
            $coin_all += $coin;
            $coin_spent += $spent;
        }
        $vip = get_vip($us_charge);
        $done = $sql->sql_readarray_count('order_done');
        $waiting = $sql->sql_readarray_count('order_waiting');
        $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
        $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
        $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
        $all_order = $done + $waiting;

        $order_user = $sql->sql_select_all('order_done', 'user', $id2);
        $us_done = 0;
        $us_cans = 0;
        $us_part = 0;
        foreach($order_user as $od_us){
            if($od_us['type'] == 'Completed'){
                $us_done += 1;
            }
            if($od_us['type'] == 'Canceled'){
                $us_cans += 1;
            }
            if($od_us['type'] == 'Partial'){
                $us_part += 1;
            }
        }
        $us_all = $us_done + $us_cans + $us_part;

        $sqsq = $sql->sql_select('users', 'user', $id2);
        $mycoin = $sqsq['mycoin'];
        $info_coin = get_coin_info($mycoin);
        $coin_name = $info_coin[1];
        $us_coin2 = $us_coin * $info_coin[0];
        $us_spent2 = $us_spent * $info_coin[0];
        $us_charge2 = $us_charge * $info_coin[0];
        $coin_from_user2 = $coin_from_user * $info_coin[0];
$EnGaYmN = $invite * $info_coin[0];
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "
*☑️︙يمكنك الحصول على رصيد مجاني* من خلال مشاركة رابط دعوتك مع اصدقائك او في قنواتك ومجموعاتك.

*💵︙كل شخص ينضم من خلال رابط دعوتك* سوف تحصل على *$EnGaYmN $coin_name مجاناً.*

*🔗 ⌯ رابطك الخاص : $link_invite$id2 ✅.*

*🪗︙الرصيد الذي جمعته من دعوه الاشخاص : $coin_from_user2 $coin_name 👤.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $ENGAymn
])
        ]);
    }
if($data == 'Aymn3mk'){
bot('sendMessage', [
'chat_id'=> $chat_id2,
'text'=> "*🤖 ⌯ بوت $NameBotG للرشق.*

*☑️ ⌯ البوت الاضخم عربياً* في تقديم خدمات الرشق لكافة *مواقع وتطبيقات السوشيال ميديا 🛎️.*

* 🚀 • سرعة ، 🚀 • جودة ، 🚀 • ضمان ، 🚀 • أسعار مناسبة ، 🚀 • مسابقات أسبوعية ، 🚀 • فريق دعم متخصص على مدار 24 ساعة لمساعدتك.*

📢 ⌯ لماذا لاتزال هنا ..!؟
*✅ ⌯ أستكشف البوت الان : $link_invite$id2 🤖.*",
'parse_mode'=> "MarkDown",
            'disable_web_page_preview' => true,
]);
return;
}
    if($data == 'mystat'){
        $jsons["$id2"]["data"] = 'mystat';
        file_put_contents("data/data.json", json_encode($jsons));
bot('editmessagetext', [
'chat_id'=>$chat_id2,
'message_id'=>$message_id2,
'text'=>"*⬇️ ⌯ قم بإرسال أيدي الطلب المراد كشفه *",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=> $back2
])
]);
}
    if($data == 'myorder'){
        $jsons["$id2"]["data"] = 'myorder';
        file_put_contents("data/data.json", json_encode($jsons));
bot('editmessagetext', [
'chat_id'=>$chat_id2,
'message_id'=>$message_id2,
'text'=>"*⬇️ ⌯ قم بإرسال رابط الرشق الذي في الطلب.*",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=> $back2
])
]);
}
    if($run == 'stop' && !in_array($id2, $adminss) && !in_array($id2, $adminsAymn)){
        bot('answerCallbackQuery',[
            'callback_query_id'=>$update->callback_query->id,
            'text'=>"🤖 ⌯ البوت تحت الصيانة حالياً.", 
            'parse_mode'=>"MarkDown",
            'show_alert'=>true,
            'cache_time'=> 20
        ]);
        return;
    }
    if($data == 'back2'){
    $jsons["$id2"] = null;
    file_put_contents("data/data.json", json_encode($jsons));
    include('./sql_class.php');
    
    // جلب بيانات المستخدم
    $sq = $sql->sql_select('users', 'user', $id2);
    $coin = $sq['coin'];
    $mycoin = $sq['mycoin']; // جلب قيمة العملة من قاعدة البيانات
    
    // دالة للحصول على اسم العملة
    function get_currency_name($currencyCode) {
        $currencyNames = [
            'usd'   => 'الدولار الأمريكي 💲.',
            's'     => 'الريال السعودي 🇸🇦.',
            'y'     => 'الريال اليمني القديم 🇾🇪.',
            'd'     => 'الدينار العراقي 🇮🇶.',
            'Aymn'  => 'عملة تايجر سبيد ♠️',
            'j'     => 'الجنيه المصري 🇪🇬',
            'r'     => 'درهم إماراتي 🇦🇪',
            'g'     => 'الريال القطري 🇶🇦',
            'o'     => 'الريال اليمني الجديد 🇾🇪.',
            'saba'  => 'وحدات سبأفون 📱.',
            'ruble' => 'الروبل الروسي 🤖'
        ];

        // التحقق مما إذا كانت العملة موجودة في القائمة
        return $currencyNames[$currencyCode] ?? 'عملة غير معروفة';
    }

    // الحصول على معلومات العملة
    $info_coin = get_coin_info($mycoin);
    $coin_after_coin = $info_coin[0] * $coin;
    $coin_name = $info_coin[1]; // متغير يحمل قيمة العملة الأصلية
    $name_coin = get_currency_name($mycoin); // المتغير الجديد الذي يحمل اسم العملة
    
    // حسابات العملات الأخرى
    $coin_users = $sql->sql_readarray('users');
    $coin_all = 0;
    $coin_spent = 0;
    foreach($coin_users as $coins){
        $coin = $coins['coin'];
        $spent = $coins['spent'];
        $user = $coins['user'];
        $charge = $coins['charge'];
        $coinfromuser = $coins['coinfromuser'];
        if($id2 == $user){
            $us_coin = $coin;
            $us_spent = $spent;
            $us_charge = $charge;
            $coin_from_user = $coinfromuser;
        }
        $coin_all += $coin;
        $coin_spent += $spent;
    }

    // حسابات الطلبات
    $vip = get_vip($us_charge);
    $done = $sql->sql_readarray_count('order_done');
    $waiting = $sql->sql_readarray_count('order_waiting');
    $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
    $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
    $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
    $all_order = $waiting + $done;

    $order_user = $sql->sql_select_all('order_done', 'user', $id2);
    $ENGAYMNN = $sql->sql_select_all('order_waiting', 'user', $id2);
    $us_done = 0;
    $us_cans = 0;
    $us_part = 0;
    $us_wait = 0;
    foreach($order_user as $od_us){
        if($od_us['type'] == 'Completed'){
            $us_done += 1;
        }
        if($od_us['type'] == 'Canceled'){
            $us_cans+= 1;
        }
        if($od_us['type'] == 'Partial'){
            $us_part += 1;
        }
    }
    foreach($ENGAYMNN as $VSSSQ){
        if($VSSSQ['user'] == $id2){
            $us_wait += 1;
        }
    }
    $us_all = $us_done + $us_cans + $us_part + $us_wait;

    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1]; // لا يزال يتم استخدام المتغير coin_name
    $name_coin = get_currency_name($mycoin); // المتغير الجديد الذي يحمل اسم العملة
    $us_coin2 = $us_coin * $info_coin[0];
    $us_spent2 = $us_spent * $info_coin[0];
    $us_charge2 = $us_charge * $info_coin[0];
    $coin_all_Aymn = $coin_all * $info_coin[0];
    $coin_spent_Aymn = $coin_spent * $info_coin[0];
    $coin_from_user2 = $coin_from_user * $info_coin[0];
    
    // إرسال الرسالة إلى المستخدم
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "*👤︙مرحباً بك مجدداً* [$first_name](tg://user?id=$id) 🖤.

*⤵️︙إليك* تفاصيل *حسابك* في بوت *$NameBotG* 🤖.

*🪗︙مستوى حسابك : VIP$vip*
*☑️︙حسابك :*`$id2`.
*💳︙رصيدك : $coin_after_coin $coin_name*
*🌪️︙العملة: $name_coin*

🙋🏻︙يمكنك *التحكم بالبوت* عبر الأزرار في *الاسفل ⬇️.*",
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $start
        ])
    ]);
    unlink($file);
}
    if($data == 'help'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => $config->help,
'parse_mode'=>"MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back2
            ])
        ]);
    }
    /**
     * تحويل نقاط
     */
if($data == 'i3dadatAymn'){
bot('editmessagetext', [
'chat_id'=> $chat_id2,
'message_id'=> $message_id2,
'text'=> "*🤵🏻︙عزيزي* [$first_name](tg://user?id=$id2) 🖤.

☑️︙أنت *الآن* في *قسم* *[ ⚙️ ⪼ الإعدادات ]* ،
⤵️︙يمكنك *التحكم* في *عدة* أمور من *الأسفل* *👇🏻.*

-",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=> $AYMN1TOP
])
]);
}
    if($data =='sendmoney'){
        $jsons["$id2"]["data"] = 'sendmoney';
        file_put_contents("data/data.json", json_encode($jsons));
        $min = $config->selmin;
        $prec = $config->sel;
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*🧑🏻‍💼︙مرحباً عزيزي* : [$first_name](tg://user?id=$id2) 🖤.

☑️︙أنت *الآن* في قسم *[ 🔄 ⪼ تحويل الرصيد ]* ،

🚨︙ملاحظات :
1 - *🚸 ⌯ يجب أن يكون الشخص المراد التحويل اليه مشتركاً في البوت 🤖.*

2 - *عمولة* التحويل هي : *$prec%*
3 - *أدنى* حد للتحويل : [*$min$*]
4 - عند *تحويل* الرصيد *إرسل المبلغ* المراد *تحويله* بعملة *الدولار 💸.*

*✅︙أرسل الان أيدي الشخص المراد التحويل إليه.*",
           'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back2
            ])
        ]);
    }
            if($data == 'section'){
            bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*🧑🏻‍💼︙مرحباً عزيزي* : [$first_name](tg://user?id=$id2) 🖤.

- إليك *أوامر كشف* الرشق *[ الإلغاء - التعويض - حالة الطلب ]* 👇🏻.

1️⃣ ⌯ للتعويض : أرسل *[ تعويض + رمز العملية ]* 
- مثال : *تعويض 123456789*

2️⃣ ⌯ لإلغاء الطلب : أرسل *[ الغاء + رمز العملية ]* 
- مثال : * الغاء 123456789*

3️⃣ ⌯ لكشف حالة الطلب : أرسل *[ رمز العملية فقط]* 
- مثال :  *123456789*

*⚠️ ⌯ بعض الخدمات و الطلبات قد لاتدعم التعويض أو الإلغاء ، إن ظهر لك خطأ أثناء التعويض أو الإلغاء ، رأسلنا 👇🏻.*

*🔚 ⌯ في حال تريد الحصول على مساعدة ، قم بمراسلة الإدارة : @Y5_5C ✔️.*",
           'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back2
            ])
        ]);
    }
    if($data == 'webaymn'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*🔗 ⌯ مرحباً بك في قسم ربط خدمات البوت بلوحتك على منصة جوجل أو بوتك في التليجرام*

*🤖︙بوت $NameBotG* هو البوت الوحيد في *العالم العربي* الذي قام بتقديم *هذه الخدمة.*

☑️︙قم بالدخول إلى *موقعنا على منصة جوجل* ثم إضغط على *API ثم إتبع التعليمات.*

*🌐 ⌯ الموقع : tigerspeed.store 🪗.*

*🧑🏻‍💼 ⌯ قم بأخذ المساعدة من المطورين ⚜️.*",
'parse_mode'=>"MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $TigerSpeed
            ])
        ]);
    }
    if($data == 'myaccount'){
        $back_add = [
            [['text' => "🔙 ⌯ رجوع.", 'callback_data' => "back2"]],
        ];
        $all = count($exmembers);
        $best_userss = explode("\n", file_get_contents('data/best_users.txt'));
        if(in_array($id2, $best_userss)){
            $me = "💎 ⌯ العضوية : مميز.";
        }else{
            $me = "💎 ⌯ العضوية : عادي.";
        }
        $best_users = count($best_userss) ?? 0;
        include('./sql_class.php');
        if (mysqli_connect_errno()) {
            return;
        }
        $coin_users = $sql->sql_readarray('users');
        $coin_all = 0;
        $coin_spent = 0;
        foreach($coin_users as $coins){
            $coin = $coins['coin'];
            $spent = $coins['spent'];
            $user = $coins['user'];
            $charge = $coins['charge'];
            if($id2 == $user){
                $us_coin = $coin;
                $us_spent = $spent;
                $us_charge = $charge;
            }
            $coin_all += $coin;
            $coin_spent += $spent;
        }
        $vip = get_vip($us_charge);
        $done = $sql->sql_readarray_count('order_done');
        $waiting = $sql->sql_readarray_count('order_waiting');
        $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
        $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
        $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
        $all_order = $waiting + $done;

        $order_user = $sql->sql_select_all('order_done', 'user', $id2);
        $us_done = 0;
        $us_cans = 0;
        $us_part = 0;
        
        foreach($order_user as $od_us){
            if($od_us['type'] == 'Completed'){
                $us_done += 1;
            }
            if($od_us['type'] == 'Canceled'){
                $us_cans += 1;
            }
            if($od_us['type'] == 'Partial'){
                $us_part += 1;
            }
        }

        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*🧑🏻‍💼︙مرحباً عزيزي* : [$first_name](tg://user?id=$id2) 🖤.

*📊︙هذه هي كافة احصائيات مستخدمين بوت $NameBotG 🤖.*

👥 ⌯ عدد أعضاء البوت : *$all*
💵 ⌯ الرصيد المتبقي : *$coin_all$*
💰 ⌯ الرصيد المستهلك : *$coin_spent$*
☑️ ⌯ عدد الطلبات الكلية : *$all_order*
✅ ⌯ عدد الطلبات المكتملة : *$order_done*
⛔ ⌯ عدد الطلبات الملغية : *$order_Canceled*
✔️ ⌯ الطلبات المكتملة جزئياً : *$order_Partial*
⏰ ⌯ الطلبات الجاري تنفيذها : *$waiting*
📞 ⌯ عدد أرقامك المكتملة : *$YoussefDone*

*📆 - تم بدء نظام الحوسبة من يوم : $DataTimeG*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $back_add
            ])
        ]);
    }
   if($data == 'my'){
    $all = count($exmembers);
    $best_userss = explode("\n", file_get_contents('data/best_users.txt'));
    if(in_array($id2, $best_userss)){
        $me = "مميز 🏅";
    } else {
        $me = "عادي 🥈";
    }
    $best_users = count($best_userss) ?? 0;
    include('./sql_class.php');
    if (mysqli_connect_errno()) {
        return;
    }
    $coin_users = $sql->sql_readarray('users');
    $coin_all = 0;
    $coin_spent = 0;
    foreach($coin_users as $coins){
        $coin = $coins['coin'];
        $spent = $coins['spent'];
        $user = $coins['user'];
        $charge = $coins['charge'];
        $coinfromuser = $coins['coinfromuser'];
        if($id2 == $user){
            $us_coin = $coin;
            $us_spent = $spent;
            $us_charge = $charge;
            $coin_from_user = $coinfromuser;
        }
        $coin_all += $coin;
        $coin_spent += $spent;
    }
    $vip = get_vip($us_charge);
    $done = $sql->sql_readarray_count('order_done');
    $waiting = $sql->sql_readarray_count('order_waiting');
    $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
    $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
    $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
    $all_order = $waiting + $done;

    $order_user = $sql->sql_select_all('order_done', 'user', $id2);
    $ENGAYMNN = $sql->sql_select_all('order_waiting', 'user', $id2);
    $us_done = 0;
    $us_cans = 0;
    $us_part = 0;
    $us_wait = 0;
    foreach($order_user as $od_us){
        if($od_us['type'] == 'Completed'){
            $us_done += 1;
        }
        if($od_us['type'] == 'Canceled'){
            $us_cans+= 1;
        }
        if($od_us['type'] == 'Partial'){
            $us_part += 1;
        }
    }
    foreach($ENGAYMNN as $VSSSQ){
        if($VSSSQ['user'] == $id2){
            $us_wait += 1;
        }
    }
    $us_all = $us_done + $us_cans + $us_part + $us_wait;

    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];
    $us_coin2 = $us_coin * $info_coin[0];
    $us_spent2 = $us_spent * $info_coin[0];
    $us_charge2 = $us_charge * $info_coin[0];
    $coin_all_Aymn = $coin_all * $info_coin[0];
    $coin_spent_Aymn = $coin_spent * $info_coin[0];
    $coin_from_user2 = $coin_from_user * $info_coin[0];
    $done = $sql->sql_count('order_done', 'user', $id2);
    $EngYoussefDone = $sql->sql_count('number_done', 'user', $id2);
    $ordersYoussef = file_get_contents('data/order.txt');
$exorders = explode("\n", $ordersYoussef);

$all_orders = count($exorders);
    $EngYoussefdone = $EngYoussefDone;
    $YoussefTime = '2024-07-13';
    
    $message = "*🤵🏻︙عزيزي* [$first_name](tg://user?id=$id2) ,\n*🚀︙إليك تفاصيل حسابك وتفاصيل جميع مستخدمين البوت بالأسفل 👇🏻.*\n\n";
    $message .= "*⬇️ ⌯ إحصائيات حسابك :* \n\n";
    $message .= "💎 ⌯ العضوية : *$me*\n";
    $message .= "☑️ ⌯ إجمالي رصيدك المشحون : *$us_charge2 $coin_name*\n";
    $message .= "💸 ⌯ رصيدك الحالي : *$us_coin2 $coin_name*\n";
    $message .= "💰 ⌯ رصيدك المصروف : *$us_spent2 $coin_name*\n";
    $message .= "🛍️ ⌯ طلباتك : *$us_all*\n";
    $message .= "🪗 ⌯ مستوى حسابك : *VIP $vip*\n\n";
    $message .= "*⬇️︙إحصائيات جميع العملاء:* 👇🏻.\n\n";
    $message .= "💸 ⌯ الرصيد : *$coin_all_Aymn $coin_name*\n";
    $message .= "💰 ⌯ الصرفيات : *$coin_spent_Aymn $coin_name*\n";
    $message .= "🛍️ ⌯ عدد الطلبات : *$all_orders*\n";
    $message .= "👤 ⌯ عدد العملاء : *$all*\n";
    $message .= "⏰ ⌯ بدأ نظام الحوسبة بتاريخ : *$YoussefTime*\n";

    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => $message,
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $kashf

            ])
        ]);
    }
    if ($data == 'done' && $get_jsons->{$id2}->data == 'done'){
        $jsons["$id2"] = null;
        file_put_contents("data/data.json", json_encode($jsons));
        $best_users = explode("\n", file_get_contents('data/best_users.txt'));
        $user_one_dollar = explode("\n", file_get_contents('data/user_one_dollar.txt'));
        if(in_array($id2, $user_one_dollar)){
            // bot('answerCallbackQuery',[
//     'callback_query_id'=>$update->callback_query->id,
//     'text'=>"⛔ ⌯ لايمكنك الرشق حالياً ،
// ☑️ ⌯ يجب ان تكون قد قمت بتعبئة رصيدك 0.5$.", 
//     'show_alert'=>true,
//     'cache_time'=> 20,
//     'parse_mode'=>"MarkDown"
// ]);
// return;
        }
        bot('answerCallbackQuery',[
            'callback_query_id'=>$update->callback_query->id,
            'text'=>"✅︙تم تأكيد طلبك ...!", 
            'show_alert'=>false,
            'cache_time'=> 20,
            'parse_mode'=>"MarkDown"
        ]);
        $serv = $get_jsons->{$id2}->serv;
        $codeserv = $get_jsons->{$id2}->codeserv;
        $num_order  = $get_jsons->{$id2}->num;
        $price_order = $get_jsons->{$id2}->price_order;
        $price_k = $get_jsons->{$id2}->price_k;
        $link = $get_jsons->{$id2}->link;
        include('./sql_class.php');
        if (mysqli_connect_errno()) {
            bot('sendMessage', [
                'chat_id' => $chat_id2,
                'text' =>"
*⛔ ⌯ حدث خطأ أثناء مراجعة الطلب وتم الالغاء ،*
*☑️ ⌯ حاول مرة أخرى بعد قليل من الوقت.*
                ",
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
            ]);
            return;
        }
        $sq = $sql->sql_select('users', 'user', $id2);
        $sq22 = $sql->sql_select('serv', 'codeserv', $codeserv);
        $apis = $sq22['api'];
        $name = $sq22['name'];
        $num = $sq22['num'];
$code = $sq22['code'];
        $coin = $sq['coin'];
        $spent = $sq['spent'] + $price_order;
        $coin_after = $coin - $price_order;

$serv_aymn = $sql->sql_select('divi', 'code', $code);
$name_aymn = $serv_aymn['codedivi'];
$AymnTop = $serv_aymn['name'];

$serv_aymna = $sql->sql_select('buttons', 'code', $name_aymn);
$name_aymna = $serv_aymna['name'];

        $sqsq = $sql->sql_select('users', 'user', $id2);
        $mycoin = $sqsq['mycoin'];
        $info_coin = get_coin_info($mycoin);
        $coin_name = $info_coin[1];

        $price_k2 = $price_k * $info_coin[0];
        $price_order2 = $price_order * $info_coin[0];
        $coin2 = $coin * $info_coin[0];
        $coin_after2 = $coin_after * $info_coin[0];
        include_once('apifiles/'.$apis.".php");
        if ($apis == '1'){
            $api = new Api();
        }
        if ($apis == '2'){
            $api = new Api2();
        }
        if ($apis == '3'){
            $api = new Api3();
        }
        if ($apis == '4'){
            $api = new Api4();
        }
        if ($apis == '5'){
            $api = new Api5();
        }
        if ($apis == '7'){
            $api = new Api7();
        }
        if ($apis == '9'){
            $api = new Api9();
        }
        if ($apis == '10'){
            $api = new Api10();
        }
        if ($apis == '11'){
            $api = new Api11();
        }
        if ($apis == '12'){
            $api = new Api12();
        }
        #$api = new Api();
        $balance = json_decode(json_encode($api->balance()))->balance;
        $order = $api->order(array('service' => $num, 'link' => $link, 'quantity' => $num_order));
        $order_js = json_decode(json_encode($order));
        $order_id = $order_js->order;
        if($order_js->error){
            $error = $order->error;
            bot('sendMessage', [
                'chat_id' => $chat_id2,
                'text' =>"*❌︙حدث خطأ أثناء تقديم طلب الرشق.*
*🧑🏻‍💻 ︙سيتم مراجعة الخطأ من قبل الإدارة بأسرع وقت ممكن.*
-",
               'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
               ]);
                bot('sendMessage', [
                    'chat_id' => $dev2,
                    'text' =>"*⛔︙خطأ جديد في إحدى الخدمات.*

🆔 ⌯ أيدي الخدمة : *$num*
🧿 ⌯ إسم الخدمة : *$name*
🚀 ⌯ الموقع ( API ) : *$apis*
⚠️ ⌯ تقرير الخطأ : *$error*",
                   'parse_mode' => "MarkDown",
                   'disable_web_page_preview' => true,
                ]);
            return;
        }{
        $sql->sql_edit('users', 'coin', $coin_after, 'user', $id2);
        $sql->sql_edit('users', 'spent', $spent, 'user', $id2);

        $mm = $sql->sql_readarray_count('order_waiting') + $sql->sql_readarray_count('order_done');
$Aymmmm = $mm + 1;
$Aymmm  = $mm + 1;
$Aymm  = $mm + 1;
$Aym  = $mm + 1;
// قراءة المحتويات من ملف order.txt
// بعد تأكيد الطلب، نخزن رقم الطلب في ملف order.txt
file_put_contents('data/order.txt', $order_id . "\n", FILE_APPEND);
$ordersYoussef = file_get_contents('data/order.txt');

// تقسيم المحتويات إلى أسطر باستخدام explode
$order_lines = explode("\n", $ordersYoussef);

// حساب عدد الطلبات (مع التأكد من تجاهل الأسطر الفارغة)
$total_orders = count(array_filter($order_lines));

// عرض عدد الطلبات
echo "عدد الطلبات هو: " . $total_orders;
$ordersYoussef = file_get_contents('data/order.txt');
$exorders = explode("\n", $ordersYoussef);

$all_orders = count($exorders);
        #$order_id = '1000';
bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id'=> $message_id2,
            'text' =>"*🚀 ⌯ تم إضافة الطلب بنجاح.*
",
            'parse_mode'=>"MarkDown",
        ]);
        $tlb = "*✅︙عملية رشق جديدة.*";
$EngAldorafy = strlen($link) - 12;
$EngAymn = substr($link,0,$EngAldorafy);
$EngA = '••••••••••••';
$EngAymnAldorafi = $EngAymn.$EngA;
$Three = strlen($id2) - 5;
$Aaymn = substr($id2,0,$Three);
$Aaaymn = '•••••';
$EngAymnnn = $Aaymn.$Aaaymn;
$capAymn = "

👤 ⌯ العميل : [$first_name](tg://user?id=$id2).
🧿 ⌯ رقم الطلب : *$all_orders*
🆔 ⌯ رمز العملية : `$order_id`.
🛒 ⌯ الخدمة : *$name*
🎬 ⌯ القسم : *$name_aymna*
🗣️ ⌯ العدد المطلوب : *$num_order*
💸 ⌯ السعر : *$price_order2 $coin_name*
🔗 ⌯ الرابط : *$link*
";
        $cap = "

🎬︙القسم : *$name_aymna* 
🚀︙النوع : *$AymnTop*

🧿︙رقم الطلب : *$all_orders*
🛒︙الخدمة : *$name*
🗣️︙العدد المطلوب : *$num_order*
💸︙سـعـر الـطـلـب : *$price_order2 $coin_name* ( *$price_order* 💲 ).
🔗︙الرابط : *$EngAymnAldorafi*

🆔︙العميل : *$EngAymnnn*
";
$cap2 = "
*✅ ⌯ تم طلب الرشق بنجاح.*

🛒 ⌯ الخدمة : *$name*
🧿 ⌯ رقم الطلب : *$all_orders*
🆔 ⌯ رمز العملية : `$order_id`.
👥 ⌯ العدد المطلوب : *$num_order*
💲 ⌯ سعر الطلب : *$price_order2 $coin_name* ( *$price_order* 💲 ).
🔗 ⌯️ الرابط : *$link*


*- سيتم إشعارك تلقائياً في حال حدوث أي تغيير في الطلب.*
";
        $cap_for_admin = "
*✅︙عملية رشق جديدة.*

👤 ⌯ العضو : [$first_name](tg://user?id=$id2).
🌐 ⌯ أيديه : `$id2`
🧿 ⌯ إسم الخدمة : *$name*
🆔 ⌯ رمز العملية : `$order_id`.

💰 ⌯ سعر 1K عضو : *$price_k$*
💰 ⌯ سعر 1K عضو : *$price_k2 $coin_name*

🗣️ ⌯ العدد المطلوب : *$num_order*

💸 ⌯ سعر الطلب : *$price_order$*
💸 ⌯ سعر الطلب : *$price_order2 $coin_name*

🆔 ⌯ أيدي الخدمة : *$num*

💲 ⌯ رصيد العضو قبل الطلب : *$coin$*
💲 ⌯ رصيد العضو قبل الطلب : *$coin2 $coin_name*

☑️ ⌯ رصيد العضو بعد الطلب : *$coin_after$*
☑️ ⌯ رصيد العضو بعد الطلب : *$coin_after2 $coin_name*

💎 ⌯ - رصيدك بموقع *$apis : $balance$*
🔗 ⌯ الرابط : *$link*
";
        $stut = '*📢︙حالة الطلب : جارِ التنفيذ... ☑️.*';
        $sql->sql_write('order_waiting(user,caption,ms_user,ms_channel,order_id,api,price,num_order,link)', "VALUES('$id2','$capAymn','$f_user', '$f_chat','$order_id','$apis','$price_order','$num_order','$link')");
bot('sendMessage', [
            'chat_id' => $chat_id2,
            'text' => $cap2,
            'parse_mode'=>"MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
[['text'=>"🚀︙إعادة الطلب من هذه الخدمة 🤖.",'callback_data'=>"EngAymn7dmh|".$num."|".$codeserv]],
]
            ])
        ]);
        $f_user = $for_user->result->message_id;
   bot('sendMessage', [
            'chat_id' => $IDCH,
            'text' => $tlb.$cap,
            'parse_mode'=>'MarkDown',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
[['text'=>"🚀︙الطلب من هذه الخدمة 🤖.",'callback_data'=>"EngAymn7dmh|".$num."|".$codeserv]],
]
            ])
        ]);
        $f_chat = $for_chat->result->message_id;
            bot('sendMessage', [
                'chat_id' => $dev2,
                'text' => $cap_for_admin."".$stut,
                'parse_mode'=>"MarkDown",
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode([
                'inline_keyboard' => [
[['text'=>"🧑🏻‍💼︙عرض تفاصيل العميل.",'url'=>"tg://user?id=$id2"]],
]
               ])
            ]);
        }}
if ($exdata[0] == 'EngAymn7dmh') {
    $jsons["$id2"]["data"] = 'link';
    $jsons["$id2"]["serv"] = $exdata[1];
    $jsons["$id2"]["codeserv"] = $exdata[2];
    file_put_contents("data/data.json", json_encode($jsons));
    include('./sql_class.php');
    
    $sq = $sql->sql_select('serv', 'codeserv', $exdata[2]);
    $cap = $sq['caption'];
    $prec_c = $sq['precent'];
    $num = $sq['num'];
    $apis = $sq['api'];

    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];

    $g = get_serv($apis, $num);
    $rate = $g['rate'];
    
    // حساب السعر الأساسي
    $price = ((($rate / 100) * $prec_c) + $rate) * $info_coin[0];
    
    // قراءة بيانات الخصم من ملف JSON
    $discount_data = json_decode(file_get_contents("data/discounts.json"), true);
    
    // التحقق إذا كان العميل مستحقًا للخصم
    if (isset($discount_data[$id2])) {
        $discount_rate = $discount_data[$id2]; // نسبة الخصم
        // تطبيق الخصم على السعر
        $price = $price - ($price * ($discount_rate / 100));
    }

    $min = shortNumber($g['min']);
    $max = shortNumber($g['max']);
    
    // رسالة إظهار السعر بعد الخصم
    $ms = "
💸 ⌯ الـسـعـر لكل 1k عضو : *$price $coin_name*
➖ ⌯ الحد الأدنى للطلب : *$min*
➕ ⌯ الحد الاعلى للطلب : *$max*

*🌐︙قم الان بإرسال الرابط المراد رشقه.*
    ";
    
    // إرسال الرسالة للعملاء
    bot('sendMessage', [
        'chat_id' => $id2,
        'text' => $cap . "\n\n" . $ms,
        'parse_mode' => "MarkDown",
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => $back_add
        ])
    ]);

    // تأكيد العملية في الـ Callback
    bot('answerCallbackQuery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => "🤖 ⌯ قام الروبوت بإختيار الخدمة لكِ...",
        'show_alert' => true,
        'cache_time' => 20
    ]);
}
    
    if ($data == 'done' && $get_jsons->{$id2}->data != 'done'){
    /*
        bot('answerCallbackQuery',[
            'callback_query_id'=>$update->callback_query->id,
            'text'=>"😔 ⌯ لايتوفر خدمات في هذا القسم ،
🪗 ⌯ قم بتجربه قسم آخر او عد مجدداً بعد دقائق.", 
            'show_alert'=>true,
            'cache_time'=> 20
        ]);
        return;
        */
    }
    if($data == 'addusers'){
        $jsons["$id2"] = null;
        file_put_contents("data/data.json", json_encode($jsons));
        include('./sql_class.php');
        $but = $sql->sql_readarray('buttons');
        $serv = [];
        foreach ($but as $button) {
            $code = $button['code'];
            $name = $button['name'];
$Aymn = $button['caption'];
            $serv[] = [['text' => $name, 'callback_data' => "selcetdivi|".$code]];
        }
        $serv[] = [['text' => "🔙︙رجوع.", 'callback_data' => "back2"]];
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*👤︙مرحباً عزيزي* [$first_name](tg://user?id=$id).
            
*☑️︙أنت* الآن *في* *[🚀 ⪼ قسم الرشق.]*
*⤵️︙إختر*التطبيق المراد *رشقه* من *الأسفل* 👇🏻.

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $serv
            ])
        ]);
    }
    if($exdata[0] == 'selcetdivi'){
        include('./sql_class.php');
        $but = $sql->sql_select_all('divi', 'codedivi', $exdata[1]);
        $EngAldorafy = $sql->sql_readarray('buttons');
        $serv = [];
foreach ($EngAldorafy as $ENGAymn) {
            $Aymn = $ENGAymn['caption'];
                        $serv_aymna = $sql->sql_select('buttons', 'code', $name_aymn);
$name_aymna = $serv_aymna['name'];
}
        $serv_aymn = $sql->sql_select('divi', 'code', $code);
        $name_aymn = $serv_aymn['codedivi'];
        $AymnTop = $serv_aymn['name'];
        foreach ($but as $button) {
            $code = $button['code'];
            $name = $button['name'];
            $serv_aymn = $sql->sql_select('divi', 'code', $code);
$name_aymn = $serv_aymn['codedivi'];
$AymnTop = $serv_aymn['name'];

$serv_aymna = $sql->sql_select('buttons', 'code', $name_aymn);
$name_aymna = $serv_aymna['name'];
            $serv[] = [['text' => $name, 'callback_data' => "selcetcoll|".$code]];
        }
        $serv[] = [['text' => "🔙 ⌯ رجوع.", 'callback_data' => "addusers"]];
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "
*✅ ⌯ تم إختيار التطبيق بنجاح.*
🎬 ⌯ التطبيق : *$name_aymna*

*🚀 ⌯ إختر ماتريده الآن من الأسفل 👇🏻.*

-
",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $serv
            ])
        ]);
    }

// قراءة ملف الخصومات من json
$discount_data = json_decode(file_get_contents('discounts.json'), true);

if ($exdata[0] == 'selcetcoll') {
    $Aaa = rand(1, 4);
    $Yyy = strlen($Aaa) + 1;
    $hhhhhhh = '';
    for ($i = 0; $i < $Yyy; $i++) {
        $hhhhhhh .= '.';
    }
    $myaymn = $hhhhhhh;
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "*✅︙يتم تحميل الخدمات*".$myaymn,
        'parse_mode' => "MarkDown",
    ]);

    include('./sql_class.php');
    $but = $sql->sql_select_all('serv', 'code', $exdata[1]);
    $qq = $sql->sql_select('divi', 'code', $exdata[1]); // القسم الأساسي

    // ترتيب الخدمات حسب service_id رقمياً
    usort($but, function($a, $b) {
        return intval($a['service_id']) <=> intval($b['service_id']);
    });

    // استخراج نوع الرشق من القسم فقط مرة واحدة
    $AymnTop = $qq['name'];
    $name_aymn = $qq['codedivi'];

    $sq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];

    $serv_aymna = $sql->sql_select('buttons', 'code', $name_aymn);
    $name_aymna = $serv_aymna['name'];

    $cap = $qq['caption'];
    $serv = [];
    $serv[] = [['text' => "🎬︙سعر الخدمة ⪼ لكل 1k .", 'callback_data' => "no"]];

    foreach ($but as $ser) {
        $code = $ser['codeserv'];
        $name = $ser['name'];
        $num = $ser['num'];
        $apis = $ser['api'];
        $prec_c = $ser['precent'];
        $service_id = $ser['service_id'];
        $g = get_serv($apis, $num);

        if (!$g) {
            continue;
        }

        $rate = $g['rate'];
        $price = ((($rate / 100) * $prec_c) + $rate) * $info_coin[0];

        $discount_rate = 0;
        if (isset($discount_data[$id2])) {
            $discount_rate = $discount_data[$id2];
            $price = $price - ($price * ($discount_rate / 100));
        }

        $serv[] = [
            ['text' => "$service_id - $name ⪼ $price $coin_name", 'callback_data' => "selcetserv|$num|$code"],
        ];

        $g = '';
    }

    $serv[] = [['text' => "🔙 ⌯ رجوع.", 'callback_data' => "addusers"]];
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "*✅︙تم إختيار نوع الرشق بنجاح.*
        
🚀︙النوع : *$AymnTop*
🎬︙لتطبيق : *$name_aymna*

*⤵️︙إختر الخدمة المناسبة لك من الأسفل :*

*⚠️ ⌯ ملاحظة :
الف عضو = 1K عضو ،
يجب قراءة وصف الخدمة قبل الطلب منها ،
نخلي مسؤوليتنا ( عدا المذكور في الوصف ) عن أي مشاكل قد تحدث.*",
        'parse_mode' => "MarkDown",
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => $serv
        ])
    ]);
}

if($exdata[0] == 'selcetserv'){
    $jsons["$id2"]["data"] = 'link';
    $jsons["$id2"]["serv"] = $exdata[1];
    $jsons["$id2"]["codeserv"] = $exdata[2];
    file_put_contents("data/data.json", json_encode($jsons));
    
    include('./sql_class.php');
    $sq = $sql->sql_select('serv', 'codeserv', $exdata[2]);
    $cap = $sq['caption'];
    $prec_c = $sq['precent'];
    $num = $sq['num'];
    $apis = $sq['api'];
    $name = $sq['name'];
    
    // الحصول على بيانات العميل
    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];

    // الحصول على سعر الخدمة
    $g = get_serv($apis, $num);
    $rate = $g['rate'];

    // حساب السعر الأصلي
    $price = ((($rate / 100) * $prec_c) + $rate) * $info_coin[0];

    // تحميل خصومات العملاء من ملف JSON
    $discounts = json_decode(file_get_contents("data/discounts.json"), true);

    // التحقق من وجود خصم للعميل
    $discount = 0;
    if (isset($discounts[$id2])) {
        $discount = $discounts[$id2]; // نسبة الخصم من ملف JSON
    }

    // حساب السعر بعد الخصم (إذا كان هناك خصم)
    if ($discount > 0) {
        $price = $price * (1 - $discount / 100); // تطبيق الخصم على السعر
    }

    $min = shortNumber($g['min']);
    $max = shortNumber($g['max']);

    // رسالة المعلومات مع السعر النهائي بعد تطبيق الخصم (إذا كان موجودًا)
    $ms = "☑️︙أسم الخدمة : *$name*\n
*⤵️︙وصف الخدمة :*\n
*$cap*

*⤵️︙معلومات الخدمة :*

💸 ⌯ الـسـعـر لكل 1k عضو : *$price $coin_name*
👇🏻 ⌯ الحد الأدنى للطلب : *$min*
☝🏻 ⌯ الحد الاعلى للطلب : *$max*

*🌐︙قم الان بإرسال الرابط المراد رشقه.*
    ";
    
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => $ms,
        'parse_mode' => "MarkDown",
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => $back_add
        ])
    ]);
}
}

if($data == 'accounty'){
    bot('editmessagetext', [
    'chat_id' => $chat_id2,
'message_id' => $message_id2,
'text' => "*⏰ ⌯ انتظر قليلاً من فضلك ..*
*↩️ ⌯ يتم الحساب ...*

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
]); 
    $all = count($exmembers);
    $best_userss = explode("\n", file_get_contents('data/best_users.txt'));
        $username = $message->from->username;
    if(in_array($id2, $best_userss)){
        $me = "مميز 🏅";
    } else {
        $me = "عادي 🥈";
    }
    $best_users = count($best_userss) ?? 0;
    include('./sql_class.php');
    if (mysqli_connect_errno()) {
        return;
    }
    $coin_users = $sql->sql_readarray('users');
    $coin_all = 0;
    $coin_spent = 0;
    foreach($coin_users as $coins){
        $coin = $coins['coin'];
        $spent = $coins['spent'];
        $user = $coins['user'];
        $charge = $coins['charge'];
        $coinfromuser = $coins['coinfromuser'];
        if($id2 == $user){
            $us_coin = $coin;
            $us_spent = $spent;
            $us_charge = $charge;
            $coin_from_user = $coinfromuser;
        }
        $coin_all += $coin;
        $coin_spent += $spent;
    }
    $vip = get_vip($us_charge);
    $done = $sql->sql_readarray_count('order_done');
    $waiting = $sql->sql_readarray_count('order_waiting');
    $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
    $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
    $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
    $all_order = $waiting + $done;

    $order_user = $sql->sql_select_all('order_done', 'user', $id2);
    $ENGAYMNN = $sql->sql_select_all('order_waiting', 'user', $id2);
    $us_done = 0;
    $us_cans = 0;
    $us_part = 0;
    $us_wait = 0;
    foreach($order_user as $od_us){
        if($od_us['type'] == 'Completed'){
            $us_done += 1;
        }
        if($od_us['type'] == 'Canceled'){
            $us_cans+= 1;
        }
        if($od_us['type'] == 'Partial'){
            $us_part += 1;
        }
    }
    foreach($ENGAYMNN as $VSSSQ){
        if($VSSSQ['user'] == $id2){
            $us_wait += 1;
        }
    }
    $us_all = $us_done + $us_cans + $us_part + $us_wait;

    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];
    $us_coin2 = $us_coin * $info_coin[0];
    $us_spent2 = $us_spent * $info_coin[0];
    $us_charge2 = $us_charge * $info_coin[0];
    $coin_all_Aymn = $coin_all * $info_coin[0];
    $coin_spent_Aymn = $coin_spent * $info_coin[0];
    $coin_from_user2 = $coin_from_user * $info_coin[0];
    $done = $sql->sql_count('order_done', 'user', $id2);
    $EngYoussefDone = $sql->sql_count('number_done', 'user', $id2);
    $EngYoussefdone = $EngYoussefDone;
    $YoussefTime = '2024-07-13';
    
    $message .= "*✅︙كافة إحصائيات حسابك في البوت* 👇🏻 :\n\n";
    $message .= "👤 ⌯ الأسم : [$first_name](tg://user?id=$id2)\n";
    $message .= "🆔 ⌯ الأيدي : `$id2`\n";
    $message .= "🌀 ⌯ اليوزر : $username2 \n";
    $message .= "💳 ⌯ المشحون : *$us_charge2 $coin_name*\n";
    $message .= "💸 ⌯ رصيدك  : *$us_coin2 $coin_name*\n";
    $message .= "💰 ⌯ صرفياتك : *$us_spent2 $coin_name*\n\n";
    $message .= "➕ ⌯ مرات الرشق : *$us_all مره*";
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => $message,
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $kashf

            ])
        ]);
    }
     if($data == 'BotAccount'){
    bot('editmessagetext', [
    'chat_id' => $chat_id2,
'message_id' => $message_id2,
'text' => "*⏰ ⌯ انتظر قليلاً من فضلك ..*
*↩️ ⌯ يتم الحساب ...*

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
]); 
    $all = count($exmembers);
    $best_userss = explode("\n", file_get_contents('data/best_users.txt'));
    if(in_array($id2, $best_userss)){
        $me = "مميز 🏅";
    } else {
        $me = "عادي 🥈";
    }
    $best_users = count($best_userss) ?? 0;
    include('./sql_class.php');
    if (mysqli_connect_errno()) {
        return;
    }
    $coin_users = $sql->sql_readarray('users');
    $coin_all = 0;
    $coin_spent = 0;
    foreach($coin_users as $coins){
        $coin = $coins['coin'];
        $spent = $coins['spent'];
        $user = $coins['user'];
        $charge = $coins['charge'];
        $coinfromuser = $coins['coinfromuser'];
        if($id2 == $user){
            $us_coin = $coin;
            $us_spent = $spent;
            $us_charge = $charge;
            $coin_from_user = $coinfromuser;
        }
        $coin_all += $coin;
        $coin_spent += $spent;
    }
    $vip = get_vip($us_charge);
    $done = $sql->sql_readarray_count('order_done');
    $waiting = $sql->sql_readarray_count('order_waiting');
    $order_done = count($sql->sql_select_all('order_done', 'type', 'Completed'));
    $order_Canceled = count($sql->sql_select_all('order_done', 'type', 'Canceled')) ?? 0;
    $order_Partial = count($sql->sql_select_all('order_done', 'type', 'Partial')) ?? 0;
    $all_order = $waiting + $done;

    $order_user = $sql->sql_select_all('order_done', 'user', $id2);
    $ENGAYMNN = $sql->sql_select_all('order_waiting', 'user', $id2);
    $us_done = 0;
    $us_cans = 0;
    $us_part = 0;
    $us_wait = 0;
    foreach($order_user as $od_us){
        if($od_us['type'] == 'Completed'){
            $us_done += 1;
        }
        if($od_us['type'] == 'Canceled'){
            $us_cans+= 1;
        }
        if($od_us['type'] == 'Partial'){
            $us_part += 1;
        }
    }
    foreach($ENGAYMNN as $VSSSQ){
        if($VSSSQ['user'] == $id2){
            $us_wait += 1;
        }
    }
    $us_all = $us_done + $us_cans + $us_part + $us_wait;

    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];
    $us_coin2 = $us_coin * $info_coin[0];
    $us_spent2 = $us_spent * $info_coin[0];
    $us_charge2 = $us_charge * $info_coin[0];
    $coin_all_Aymn = $coin_all * $info_coin[0];
    $coin_spent_Aymn = $coin_spent * $info_coin[0];
    $coin_from_user2 = $coin_from_user * $info_coin[0];
    $done = $sql->sql_count('order_done', 'user', $id2);
    $EngYoussefDone = $sql->sql_count('number_done', 'user', $id2);
    $ordersYoussef = file_get_contents('data/order.txt');
$exorders = explode("\n", $ordersYoussef);

$all_orders = count($exorders);
    $EngYoussefdone = $EngYoussefDone;
    $YoussefTime = '2024-07-13';
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "🧑🏻‍💼︙مرحباً بك عزيزي [$first_name](tg://user?id=$id2)

⤵️ • هذه هي كافة إحصائيات العملاء في البوت 🤖 :

💸 ⌯ الرصيد : $coin_all_Aymn $coin_name*
💰 ⌯ الصرفيات : $coin_spent_Aymn $coin_name*
💳 ⌯ المشحون : *$coin_all_Aymn $coin_name*

➕ ⌯ مرات الرشق : *$all_orders مره*
👤 ⌯ عدد العملاء : *$all عميل*
⏰ ⌯ بدء نظام الحوسبة بتاريخ : *$YoussefTime*.
-",
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back2

            ])
        ]);
    }
// عند الضغط على الزر "كشف الطلبات"
if ($data == 'CheckOrders') {
    // إرسال رسالة تطلب من العميل إرسال رمز العملية
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "*☑️︙كشف الطلبات ، أرسل رمز العملية الخاص بالطلب للتأكد منه 👇🏻.*",
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back2 // زر العودة إذا كان لديك
        ])
    ]);
    
    // الآن ننتظر إدخال من العميل عبر الرسالة العادية
    // نفترض هنا أن البوت في وضع الاستقبال للرسائل عبر Webhook أو getUpdates
}
/*
// ثم عندما يرسل العميل الرمز (يتم التقاطه من خلال getUpdates أو Webhook)
if (isset($text) && !empty($text)) {

    // التحقق من أن الرسالة تحتوي على أرقام فقط وطولها بين 8 إلى 10 أرقام
    if (preg_match('/^\d{8,10}$/', $text)) {
        // هنا نأخذ الرمز الذي أرسله العميل
        $order_id = $text;

        // تعريف روابط APIs
        $api_urls = [
            "https://tigerspeed.store/api/v2?action=status&order=$order_id&key=egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3",
            "https://bulkmedya.org/api/v2?action=status&order=$order_id&key=ecbf5cec79658204f546f4d286438ea6",
            "https://thelordofthepanels.com/api/v2?action=status&order=$order_id&key=f5304249c2ec8b1ea1782916c5cb7292",
            "https://smmstone.com/api/v2?action=status&order=$order_id&key=54a424b603072c613d6de5996e6faf34",
            // يمكنك إضافة المزيد من الروابط هنا
        ];

        $order_details = null; // تعريف متغير لتخزين تفاصيل الطلب

        // التحقق من رمز العملية عبر جميع الروابط
        foreach ($api_urls as $api_url) {
            $api_response = file_get_contents($api_url);
            $order_details = json_decode($api_response, true);

            // إذا تم العثور على تفاصيل الطلب، نخرج من الحلقة
            if (isset($order_details['status'])) {
                break;
            }
        }

        // التحقق من وجود الاستجابة الصحيحة
        if (isset($order_details['status'])) {
            $charge = $order_details['charge'];
            $start_count = $order_details['start_count'];
            $status = $order_details['status'];
            $remains = $order_details['remains'];
            $currency = $order_details['currency'];

            // تعريب الحالة (status)
            $translated_status = ""; // تعريف متغير لتخزين الحالة المعربة
            switch ($status) {
                case "Completed":
                    $translated_status = "مكتمل ✅";
                    break;
                case "In progress":
                    $translated_status = "قيد التنفيذ 🚀";
                    break;
                case "Canceled":
                    $translated_status = "ملغي 🔴";
                    break;
                case "Partial":
                    $translated_status = "مكتمل جزئي ✔️";
                    break;
                case "Processing":
                    $translated_status = "🔄︙قيد المُعالجة ...";
                    break;
                case "Pending":
                    $translated_status = "⏳︙في الإنتظار ...";
                    break;
                default:
                    $translated_status = $status; // إذا كانت الحالة غير معروفة، نستخدم القيمة الأصلية
            }
$keyboard = [];

if ($status === "Completed") {
    // لا شيء، كلا الزرين مخفيين
} elseif ($status === "Partial") {
    // فقط زر الإلغاء
    $keyboard[] = [['text' => "إلغاء الطلب", 'callback_data' => "cancel|".$order_id]];
} elseif ($status === "In progress") {
    // فقط زر الإلغاء
    $keyboard[] = [['text' => "إلغاء الطلب", 'callback_data' => "cancel|".$order_id]];
} else {
    // إظهار الزرين في الحالات الأخرى مثل Pending أو Processing
    $keyboard[] = [
        ['text' => "طلب تعويض", 'callback_data' => "refill|".$order_id],
        ['text' => "إلغاء الطلب", 'callback_data' => "cancel|".$order_id]
    ];
}

        // إرسال تفاصيل الطلب مع الأزرار  
        bot('sendMessage', [  
            'chat_id' => $chat_id,  
            'text' => "✅︙تفاصيل العملية رقم : *$text* 👇🏻\n\n" .  
                      "👥 ⌯ *العدد عند البدء*: $start_count\n" .  
                      "♻️ ⌯ *الحالة*: *$translated_status*\n" . // استخدام الحالة المعربة  
                      "👥 ⌯ *المتبقي*: $remains\n\n" .  
                      "⚠️ ⌯ *إحفظ رمز العملية الخاص بالطلب بسرية تامه ، ولاترسله لأي شخص ماعدا الإدارة 🤫!*",  
            'parse_mode' => "MarkDown",  
            'disable_web_page_preview' => true,  
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),  
        ]);  
    } else {  
        // إذا كان هناك خطأ في الحصول على تفاصيل الطلب  
        bot('sendMessage', [  
            'chat_id' => $chat_id,  
            'text' => "*⚠️︙عذرًا، لم نتمكن من جلب تفاصيل الطلب. تأكد من رمز العملية وحاول مرة أخرى.*",  
            'parse_mode' => "MarkDown",  
            'disable_web_page_preview' => true,  
        ]); 
        return;
    }  
}
}
*/
if (isset($text) && !empty($text)) {

    // التحقق من أن الرسالة تحتوي على كلمة "تعويض" تليها رقم
    if (preg_match('/^تعويض (\d{8,10})$/', $text, $matches)) {
        // هنا نأخذ الرمز الذي أرسله العميل بعد كلمة "تعويض"
        $order_id = $matches[1];

        // التحقق مما إذا كان رمز العملية موجودًا في ملف Dorefill.txt
        $dorefill_file_path = 'data/Dorefill.txt';
        if (file_exists($dorefill_file_path)) {
            $dorefill_orders = file($dorefill_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (in_array($order_id, $dorefill_orders)) {
                // إرسال رسالة نجاح للعميل
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "✅︙تم طلب التعويض بنجاح للعملية رقم : *$order_id* 👇🏻\n\n" .
                              "*⏰ ⌯ قد يستغرق من 0-24 ساعة للتعويض 🚀.*\n\n" .
                              "⚠️ ⌯ *إحفظ رمز العملية الخاص بالطلب بسرية تامه ، ولاترسله لأي شخص ماعدا الإدارة 🤫!*",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);

                // إرسال رسالة نجاح للأدمن
                bot('sendMessage', [
                    'chat_id' => $dev, // معرف الأدمن
                    'text' => "✅︙تم طلب التعويض بنجاح للعملية رقم : *$order_id* للعميل `$chat_id`.",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);

                // تخزين وقت طلب التعويض في ملف JSON
                $file_path = 'data/refill_requests.json';
                if (!file_exists($file_path)) {
                    file_put_contents($file_path, json_encode([]));
                }
                $data = json_decode(file_get_contents($file_path), true);
                $data[$order_id] = time();
                file_put_contents($file_path, json_encode($data));

                exit; // إنهاء الكود لأن العملية تمت بنجاح
            }
        }

        // تحديد ملف JSON لتخزين أوقات طلب التعويض
        $file_path = 'data/refill_requests.json';
        if (!file_exists($file_path)) {
            file_put_contents($file_path, json_encode([]));
        }

        // قراءة بيانات ملف JSON
        $data = json_decode(file_get_contents($file_path), true);

        // التحقق إذا كان هناك طلب سابق للتعويض
        if (isset($data[$order_id])) {
            $last_request_time = $data[$order_id];
            $current_time = time();
            $time_difference = $current_time - $last_request_time;
            $remaining_time = 86400 - $time_difference; // 86400 ثانية تعادل 24 ساعة

            if ($time_difference < 86400) {
                // حساب الوقت المتبقي بالساعات والدقائق والثواني
                $hours_left = floor($remaining_time / 3600);
                $minutes_left = floor(($remaining_time % 3600) / 60);
                $seconds_left = $remaining_time % 60;

                // رسالة خطأ تخبر المستخدم بالوقت المتبقي
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "⚠️︙لا يمكنك طلب تعويض آخر للعملية رقم: *$order_id* الآن.\n\n" .
                              "⏳⌯ الوقت المتبقي لإعادة طلب التعويض: *$hours_left* ساعة و *$minutes_left* دقيقة و *$seconds_left* ثانية ⏱.",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);
                exit; // إنهاء الكود هنا لمنع محاولة طلب التعويض مرة أخرى
            }
        }

        // التحقق مما إذا كان الطلب مدرجًا في Norefill.txt
        $norefill_file_path = 'data/Norefill.txt';
        if (file_exists($norefill_file_path)) {
            $norefill_orders = file($norefill_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (in_array($order_id, $norefill_orders)) {
                // إرسال رسالة خطأ للعميل
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "*⚠️︙العملية رقم : *$order_id* لا تدعم التعويض حالياً .",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);

                // إرسال رسالة فشل للأدمن
                bot('sendMessage', [
                    'chat_id' => $dev, // معرف الأدمن
                    'text' => "⚠️︙فشل طلب التعويض للعملية رقم : *$order_id* للعميل `$chat_id` - الطلب لا يدعم التعويض.",
                    'parse_mode' => "MarkDown",
                    'disable_web_page_preview' => true,
                ]);

                exit; // إنهاء الكود إذا كان الطلب لا يدعم التعويض
            }
        }

        // تعريف روابط APIs
        $api_urls = [
            "https://tigerspeed.store/api/v2?action=refill&order=$order_id&key=egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3",
            "https://bulkmedya.org/api/v2?action=refill&order=$order_id&key=ecbf5cec79658204f546f4d286438ea6",
            "https://thelordofthepanels.com/api/v2?action=refill&order=$order_id&key=f5304249c2ec8b1ea1782916c5cb7292",
            "https://smmstone.com/api/v2?action=refill&order=$order_id&key=54a424b603072c613d6de5996e6faf34",
        ];

        $order_details = null; // تعريف متغير لتخزين تفاصيل الطلب

        // التحقق من رمز العملية عبر جميع الروابط
        foreach ($api_urls as $api_url) {
            $api_response = file_get_contents($api_url);
            $order_details = json_decode($api_response, true);

            // طباعة الاستجابة لتشخيص المشكلة
            error_log("API Response: " . print_r($order_details, true));

            // إذا تم العثور على تفاصيل الطلب، نخرج من الحلقة
            if (isset($order_details['refill'])) {
                break;
            }
        }

        // التحقق من وجود استجابة التعويض
        if (isset($order_details['refill']) && $order_details['refill'] > 0) {
            // تم طلب التعويض بنجاح
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅︙تم طلب التعويض بنجاح للعملية رقم : *$order_id* 👇🏻\n\n" .
                          "*⏰ ⌯ تستغرق عملية التعويض من 0-24 ساعة 🚀.*\n\n" .
                          "⚠️ ⌯ *إحفظ رمز العملية الخاص بالطلب بسرية تامه ، ولاترسله لأي شخص ماعدا الإدارة 🤫!*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
            ]);

            // إرسال رسالة نجاح للأدمن
            bot('sendMessage', [
                'chat_id' => $dev, // معرف الأدمن
                'text' => "✅︙تم طلب التعويض بنجاح للعملية رقم : *$order_id* للعميل `$chat_id`.",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
            ]);

            // تخزين وقت طلب التعويض في ملف JSON
            $data[$order_id] = time();
            file_put_contents($file_path, json_encode($data));
        } else {
            // إذا كان هناك خطأ في طلب التعويض أو كانت القيمة غير صالحة
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "*⚠️︙تعذر طلب التعويض للعملية رقم:* `$order_id`.\n\n" .
                          "*⚠️ ⌯ قد تكون الخدمة أو الطلب لايدعم التعويض بالوقت الحالي.*\n" .
                          "*☑️ ⌯ للتأكد يرجى مراسلة الإدارة : @Y5_5C ✔️.*",
                'parse_mode' => "MarkDown",
                'disable_web_page_preview' => true,
            ]);

            // إرسال رسالة فشل للأدمن
            bot('sendMessage', [
                'chat_id' => $dev, // معرف الأدمن
                'text' => "⚠️︙فشل طلب التعويض للعملية رقم : *$order_id* للعميل `$chat_id` - تعذر طلب التعويض.",
                'parse_mode' =>"MarkDown",
                'disable_web_page_preview' => true,
            ]);
        }
    }
}
if (preg_match('/^الغاء (\d{8,10})$/', $text, $matches)) {
    $order_id = $matches[1];

    // التحقق من ملف nocancel.txt للتأكد أن الطلب لا يدعم الإلغاء
    if (file_exists('data/nocancel.txt') && in_array($order_id, file('data/nocancel.txt', FILE_IGNORE_NEW_LINES))) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*⚠️ ⌯ العملية رقم : `$order_id` لا تدعم الإلغاء حالياً.*

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        
        // إرسال إشعار إلى الإدمن
        bot('sendMessage', [
            'chat_id' => $dev,
            'text' => "⚠️⌯ تم محاولة إلغاء العملية رقم: `$order_id` ولكنها لا تدعم الإلغاء.

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        exit;
    }

    // التحقق إذا كان هناك طلب إلغاء مسبق
    $cancel_file = 'data/cancel_requests.json';
    if (!file_exists($cancel_file)) {
        file_put_contents($cancel_file, json_encode([]));
    }
    $cancel_data = json_decode(file_get_contents($cancel_file), true);

    if (isset($cancel_data[$order_id])) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⚠️ ⌯ لقد أرسلت طلب إلغاء مسبقاً للعملية رقم $order_id. يرجى الانتظار حتى يتم معالجة الإلغاء.

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);

        // إشعار الإدمن بأن طلب إلغاء مكرر تم إرساله
        bot('sendMessage', [
            'chat_id' => $dev,
            'text' => "⚠️⌯ تم محاولة إلغاء العملية رقم: `$order_id`، ولكن تم تقديم طلب مسبق.

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        exit;
    }

    $api_urls = [
        "https://tigerspeed.store/api/v2?action=cancel&order=$order_id&key=egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3",
        "https://bulkmedya.org/api/v2?action=cancel&order=$order_id&key=ecbf5cec79658204f546f4d286438ea6",
        "https://thelordofthepanels.com/api/v2?action=cancel&order=$order_id&key=f5304249c2ec8b1ea1782916c5cb7292",
        "https://smmstone.com/api/v2?action=cancel&order=$order_id&key=54a424b603072c613d6de5996e6faf34",
    ];

    $order_details = null;

    foreach ($api_urls as $api_url) {
        $api_response = file_get_contents($api_url);
        $order_details = json_decode($api_response, true);

        if (isset($order_details['cancel'])) {
            break;
        }
    }

    if (isset($order_details['cancel']) && $order_details['cancel'] > 0) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*✅︙تم طلب الإلغاء بنجاح للعملية رقم* : `$order_id` \n\n" .
                      "*⏰ ⌯ تستغرق عملية الإلغاء من 0-24 ساعة.*\n\n" .
                      "⚠️ ⌯ *إحفظ رمز العملية الخاص بالطلب بسرية تامه ، ولاترسله لأي شخص ماعدا الإدارة 🤫!*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);

        // إشعار الإدمن بنجاح طلب الإلغاء
        bot('sendMessage', [
            'chat_id' => $dev,
            'text' => "*✅︙تم إرسال طلب إلغاء بنجاح.*
            
👤 ⌯ المستخدم : [$first_name](tg://user?id=$id)
🆔 ⌯ رمز العملية : `$order_id`.
♻️ ⌯ حالة العملية : *ناجحة ✅.*

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);

        // تخزين طلب الإلغاء
        $cancel_data[$order_id] = time();
        file_put_contents($cancel_file, json_encode($cancel_data));
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*⚠️︙عذراً العملية رقم: `$order_id` ، لاتدعم الإلغاء.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);

        // إشعار الإدمن بفشل طلب الإلغاء
        bot('sendMessage', [
            'chat_id' => $dev,
            'text' => "*⚠️︙فشل طلب إلغاء العملية رقم: `$order_id`، لأنها لا تدعم الإلغاء.*

-",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
    }
}
if (in_array($id, $adminss)) {

    // وظيفة لحذف رمز العملية من ملف معين
    function remove_order_from_file($file_path, $order_id) {
        $file_contents = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $updated_contents = array_diff($file_contents, [$order_id]); // إزالة رمز العملية
        file_put_contents($file_path, implode(PHP_EOL, $updated_contents) . PHP_EOL); // إعادة الكتابة للملف
    }

    // أمر /dorefill
    if (preg_match('/^\/dorefill (\d{8,10})$/', $text, $matches)) {
        $order_id = $matches[1];
        $file_path = 'data/Dorefill.txt';

        // إضافة رمز العملية إلى ملف Dorefill
        file_put_contents($file_path, $order_id . PHP_EOL, FILE_APPEND);

        // حذف رمز العملية من ملف Norefill إذا كان موجودًا
        remove_order_from_file('data/Norefill.txt', $order_id);

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅︙تم إضافة العملية رقم : `$order_id` إلى قائمة Dorefill وتم حذفها من قائمة Norefill إن وجدت.",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        exit;
    }

    // أمر /norefill
    if (preg_match('/^\/norefill (\d{8,10})$/', $text, $matches)) {
        $order_id = $matches[1];
        $file_path = 'data/Norefill.txt';

        // إضافة رمز العملية إلى ملف Norefill
        file_put_contents($file_path, $order_id . PHP_EOL, FILE_APPEND);

        // حذف رمز العملية من ملف Dorefill إذا كان موجودًا
        remove_order_from_file('data/Dorefill.txt', $order_id);

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅︙تم إضافة العملية رقم : `$order_id` إلى قائمة Norefill وتم حذفها من قائمة Dorefill إن وجدت.",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        exit;
    }

    // أمر /nocancel
    if (preg_match('/^\/nocancel (\d{8,10})$/', $text, $matches)) {
        $order_id = $matches[1];
        $file_path = 'data/nocancel.txt';

        // إضافة رمز العملية إلى ملف NoCancel
        file_put_contents($file_path, $order_id . PHP_EOL, FILE_APPEND);
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅︙تم إضافة العملية رقم : `$order_id` إلى قائمة NoCancel بنجاح.",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
        exit;
    }
}
if (in_array($id, $adminss) || in_array($id, $adminsAymn)) {
    if ($text == 'الأوامر') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*🙋🏻 ⌯ مرحباً بك مطوري يوسف ، اليك لوحة أوامر كشف الرشق بالأسفل 👇🏻.*
    
*♻️︙إضافة دعم تعويض ⌯ /dorefill  order_id ✅.*
*♻️︙إضافة عدم تعويض ⌯ /norefill  order_id ⛔*
*✖️︙إضافة عدم دعم الإلغاء ⌯ /nocancel  order_id ⛔.*

-",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $back2
            ])
        ]);
        return;
    }
}
if ($data == "tsweet"){
bot('editmessagetext', [
'chat_id' => $chat_id2,
'message_id' => $message_id2,
'text' => "*👤︙مرحباً بك عزيزي* [$first_name](tg://user?id=id2) 🖤.

☑️︙في قسم *[ 🗳️ ⪼ تصويتات تليجرام ]* .

*- يرجى إختيار الخدمة التي تريدها من الأسفل 👇🏻.*",
        'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $tsweet
        ])
     ]);
   return;
}
if (preg_match('/^\/dis([1-5]) (\d+)$/', $text, $matches)) {
    $discount_percentage = $matches[1]; // نسبة الخصم من الأمر (1%, 2%, 3%, 4%, 5%)
    $client_id = $matches[2]; // معرف العميل

    // قراءة ملف الخصومات
    $discounts = json_decode(file_get_contents("data/discounts.json"), true);

    // تطبيق نسبة الخصم على العميل
    $discounts[$client_id] = $discount_percentage;
    
    // حفظ الخصم في ملف JSON
    file_put_contents("data/discounts.json", json_encode($discounts));

    // رسالة تأكيد تطبيق الخصم
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅︙تم تطبيق خصم بقيمة $discount_percentage% للعميل `$client_id`",
                'parse_mode' => "MarkDown",
    ]);

    // إرسال رسالة للعميل بخصوص تطبيق الخصم لجميع الخدمات
    bot('sendMessage', [
        'chat_id' => $client_id,
        'text' => "*✅︙تم إضافة خصم لجميع الخدمات.*
👤 ⌯ بواسطة : [$first_name](tg://user?id=$id)

💰 ⌯ قيمة الخصم : *$discount_percentage%*
*🎉 ⌯ لقد أصبحت عميلاً مميزاً في البوت 🔥.*

*- سيتم تطبيق الخصم تلقائياً عند تأكيد الطلب أو أختيار أي خدمة 🔥.*",
        'parse_mode' => "MarkDown",
    ]);
}
include ('Login.php');
?>