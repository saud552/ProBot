<?php
// مسار ملف JSON الذي يحتوي على العملاء الذين يحصلون على الخصم
$discount_file = 'data/discount_clients.json'; // تأكد من أن هذا المسار صحيح

date_default_timezone_set('Asia/Baghdad');
$year = date('Y');
$month = date('n');
$day = date('j');
$date = "$year/$month/$day م";
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$id = $message->from->id;
$chat_id = $message->chat->id;
$text = $message->text;
$user = $message->from->username;
$name = $message->from->first_name;

$sales = json_decode(file_get_contents('sales.json'),1);
if(isset($update->callback_query)){
  $chat_id = $update->callback_query->message->chat->id;
  $message_id = $update->callback_query->message->message_id;
  $data     = $update->callback_query->data;
$name = $message->from->first_name;
$user = $update->callback_query->from->username;
$sales = json_decode(file_get_contents('sales.json'),true);
$buttons = json_decode(file_get_contents('button.json'),true);
}
function save($array){
    file_put_contents('sales.json', json_encode($array));
}
$city=array("afghanistan","albania","algeria","angola","antiguaandbarbuda","argentina","armenia","australia","austria","azerbaijan","bahrain","bangladesh","barbados","belarus","belgium","benin","bhutane","bih","bolivia","botswana","brazil","bulgaria","burkinafaso","burundi","cambodia","cameroon","canada","caymanislands","chad","china","colombia","congo","costarica","croatia","cyprus","czech","djibouti","dominicana","easttimor","ecuador","egypt","england","equatorialguinea","estonia","ethiopia","finland","france","frenchguiana","gabon","gambia","georgia","germany","ghana","guadeloupe","guatemala","guinea","guineabissau","guyana","haiti","honduras","hungary","india","indonesia","iran","iraq","ireland","israel","italy","ivorycoast","jamaica","jordan","kazakhstan","kenya","kuwait","laos","latvia","lesotho","liberia","libya","lithuania","luxembourg","macau","madagascar","malawi","malaysia","maldives","mali","mauritania","mauritius","mexico","moldova","mongolia","montenegro","morocco","mozambique","myanmar","namibia","nepal","netherlands","newzealand","nicaragua","nigeria","norway","oman","pakistan","panama","papuanewguinea","paraguay","peru","philippines","poland","portugal","puertorico","qatar","reunion","romania","russia","rwanda","saintkittsandnevis","saintlucia","saintvincentandgrenadines","salvador","saudiarabia","senegal","serbia","sierraleone","slovakia","slovenia","somalia","southafrica","spain","srilanka","sudan","suriname","swaziland","sweden","switzerland","syria","taiwan","tajikistan","tanzania","thailand","tit","togo","tunisia","turkey","turkmenistan","uae","uganda","ukraine","uruguay","usa","uzbekistan","venezuela","vietnam","yemen","zambia","zimbabwe");
$cities="
{ `yemen`}  =    🇾🇪| اليمن  
  { `afghanistan `}  =  🇦🇫| أفغانستان 
  { `albania `}  =  🇦🇱| ألبانيا 
  { `algeria `}  =  🇩🇿| الجزائر   
  { `angola `}  =  🇦🇴| أنغولا   
  { `antiguaandbarbuda `}  =  🇦🇬| انتيغوا وباربودا   
  { `argentina `}  =  🇦🇷| الأرجنتين   
  { `armenia `}  =  🇦🇲| أرمينيا   
  { `australia `}  =  🇦🇺| أستراليا  
  { `austria `}  =  🇦🇹| النمسا 
  { `azerbaijan `}  =  🇦🇿| أذربيجان
  { `bahrain `}  =  🇧🇭| البحرين 
  { `bangladesh `}  =  🇧🇩| بنغلادش 
  { `barbados `}  =  🇧🇧| باربادوس   
  { `belarus `}  =  🇧🇾| بيلاروسيا 
  { `belgium `}  =  🇧🇪| بلجيكا 
  { `benin `}  =  🇧🇯| بنين 
  { `bhutane `}  =  🇧🇹| بوتان 
  { `bih `}  =  🇧🇦| البوسنة والهرسك 
  { `bolivia `}  =  🇧🇴| بوليفيا   
  { `botswana `}  =  🇧🇼| بوتسوانا  
  { `brazil `}  =  🇧🇷| البرازيل   
  { `bulgaria `}  =  🇧🇬| بلغاريا  
  { `burkinafaso `}  =  🇧🇫| بوركينا فاسو   
  { `burundi `}  =  🇧🇮| بوروندي 
  { `cambodia `}  =  🇰🇭| كمبوديا   
  { `cameroon `}  =  🇨🇲| الكاميرون  
  { `canada `}  =  🇨🇦| كندا   
  { `chad `}  =  🇹🇩| تشاد  
  { `china `}  =  🇨🇳| الصين   
  { `colombia `}  =  🇨🇴| كولومبيا  
  { `congo `}  =  🇨🇩| الكونغو  
  { `costarica `}  =  🇨🇷| كوستا ريكا   
  { `croatia `}  =  🇭🇷| كرواتيا 
  { `cyprus `}  =  🇨🇾| قبرص   
  { `czech `}  =  🇨🇿| التشيك   
  { `djibouti `}  =  🇩🇯| جيبوتي   
  { `dominicana `}  =  🇩🇲| دومينيكا  
  { `easttimor `}  =  🇹🇱| تيمور 
  { `ecuador `}  =  🇪🇨| الإكوادور 
  { `egypt `}  =  🇪🇬| مصر 
  { `england `}  =  🇬🇧| انجلترا  
  { `equatorialguinea `}  =  🇬🇶| غينيا الاستوائية  
  { `estonia `}  =  🇪🇪| إستونيا   
  { `ethiopia `}  =  🇪🇹| إثيوبيا  
  { `finland `}  =  🇫🇮| فنلندا  
  { `frenchguiana `}  =  🇬🇫| غويانا الفرنسية   
  { `gabon `}  =  🇬🇦| الغابون 
  { `gambia `}  =  🇬🇲| غامبيا   
  { `georgia `}  =  🇬🇪| جورجيا   
  { `germany `}  =  🇩🇪| ألمانيا  
  { `ghana `}  =  🇬🇭| غانا   
  { `guadeloupe `}  =  🇬🇵| غوادلوب 
  { `guatemala `}  =  🇬🇹| غواتيمالا   
  { `guinea `}  =  🇬🇳| غينيا  
  { `guineabissau `}  =  🇬🇼| غينيا بيساو  
  { `guyana `}  =  🇬🇫| غويانا  
  { `haiti `}  =  🇭🇹| هايتي  
  { `honduras `}  =  🇭🇳| هندوراس 🇭🇳
  { `hungary `}  =  🇭🇺| هنغاريا   
  { `india `}  =  🇮🇳| الهند   
  { `indonesia `}  =  🇮🇩| إندونيسيا   
  { `iraq `}  =  🇮🇶| العراق  
  { `ireland `}  =  🇮🇪| ايرلندا   
  { `italy `}  =  🇮🇹| ايطاليا   
  { `mongolia `}  =  🇲🇳| منغوليا   
  { `montenegro `}  =  🇲🇪| الجبل الأسود   
  { `jordan `}  =  🇯🇴| الأردن   
  { `kazakhstan `}  =  🇰🇿| كازاخستان  
  { `kenya `}  =  🇰🇪| كينيا  
  { `kuwait `}  =  🇰🇼| الكويت 
  { `latvia `}  =  🇱🇻| لاتفيا   
  { `liberia `}  =  🇱🇷| ليبيريا  
  { `libya `}  =  🇱🇾| ليبيا  
  { `luxembourg `}  =  🇱🇺| لوكسمبورغ   
  { `macau `}  =  🇲🇴| ماكاو  
  { `madagascar `}  =  🇲🇬| مدغشقر  
  { `malaysia `}  =  🇲🇾| ماليزيا  
  { `maldives `}  =  🇲🇻| جزر المالديف 
  { `mauritania `}  =  🇲🇷| موريتانيا  
  { `mexico `}  =  🇲🇽| المكسيك 
  { `morocco `}  =  🇲🇦| المغرب   
  { `nepal `}  =  🇳🇵| نيبال   
  { `newzealand `}  =  🇳🇿| نيوزيلاندا   
  { `nigeria `}  =  🇳🇬| نيجيريا   
  { `oman `}  =  🇴🇲| عمان   
  { `pakistan `}  =  🇵🇰| باكستان   
  { `paraguay `}  =  🇵🇾| باراغواي   
  { `poland `}  =  🇵🇱| بولندا  
  { `portugal `}  =  🇵🇹| البرتغال   
  { `qatar `}  =  🇶🇦| قطر  
  { `russia `}  =  🇷🇺| روسيا  
  { `saudiarabia `}  =  🇸🇦| السعودية  
  { `serbia `}  =  🇷🇸| صربيا   
  { `somalia `}  =  🇸🇴|الصومال   
  { `spain `}  =  🇪🇸| اسبانيا   
  { `sudan `}  =  🇸🇩| السودان   
  { `syria `}  =  🇸🇾| سوريا   
  { `tunisia `}  =  |🇹🇳 تونس   
  { `turkey `}  =  |🇹🇷 تركيا  
  { `uae `}  =  🇦🇪| الامارات   
  { `usa `}  =  🇺🇸| الولايات المتحدة 
";
$admin = "6506780205";//ايديك
$tokensim="53fcfe77d93a46069411445823538e51";//توكن الموقع
$ch = file_get_contents("channel.txt");
$rssed = filter_var(file_get_contents("http://api1.5sim.biz/stubs/handler_api.php?api_key=$tokensim&action=getBalance"), FILTER_SANITIZE_NUMBER_INT);
$me = bot('getme',['bot'])->result->username;
$sales = json_decode(file_get_contents('sales.json'),1);
if($data == "pointsfile"){
$user = (file_get_contents("sales.json"));
file_put_contents("backup.json",$user);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
▪ تم عمل نسخة احتياطية بنجاح",
]);
bot("sendDocument",[
"chat_id"=>$admin,
"document"=>new CURLFILE("backup.json")
]);
}

// التحقق من وجود الملف وصلاحيات الكتابة
if (file_exists($discount_file)) {
    if (is_writable($discount_file)) {
        // تحميل البيانات من الملف
        $json_data = json_decode(file_get_contents($discount_file), true);

        // التحقق من الخصم للعميل بناءً على chat_id
        if (isset($json_data[$chat_id])) {
            $discount = $json_data[$chat_id]['discount']; // قيمة الخصم
            $price = $price - ($price * ($discount / 100)); // تطبيق الخصم
            echo "✅ تم تطبيق خصم بقيمة $discount%";
        } else {
            echo "❌ لا يوجد خصم لهذا العميل.";
        }
    } else {
        echo "⚠️ لا يمكن الكتابة إلى الملف. تحقق من الأذونات!";
    }
} else {
    echo "⚠️ الملف غير موجود. تحقق من المسار!";
}
$TG_KEY = '370bfd0a-a35b-4929-a4d1-0d420b5bef26'; # خذ المفتاح من الرابط https://tg-accounts.com/API
$API_NUMBER = "https://tg-accounts.com/API/v1/number?token=$TG_KEY&"; # لا تلمس شي
$arab = ["YE", "SY", "IQ", "EG", "SA", "AE", "JO", "LB", "DZ", "MA", "TN", "LY", "SD", "MR", "KM", "DJ", "SO", "SS", "KW", "BH", "QA", "OM"];
$europ = ['AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EU', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD', 'MC', 'ME', 'NL', 'MK', 'NO', 'PL', 'PT', 'RO', 'SM', 'RS', 'SK', 'ES', 'SI', 'SE', 'CH', 'UA', 'GB'];
$json_country = json_decode(file_get_contents('data/country.json'), true);
$get_country_name = json_decode(file_get_contents('data/country.json'));
$MyYoussef = [
[['text' => "🪗︙تسجيل خروج للخادم", 'callback_data' => "LogOut|".$number]],
];
$tg_buttons = [
    [['text' => "🌐︙شراء رقم عربي.", 'callback_data' => "NewNumberr|ar"]],
    [['text' => "🌐︙شراء رقم أوروبي.", 'callback_data' => "NewNumberr|er"]], 
    [['text' => "🌐︙دول أخرى", 'callback_data' => "NewNumberr|ot"]],
        [['text' => "🌐︙تحت التجربة ⚠️.", 'callback_data' => "buy"]],
    [['text' => "🔙︙إلغاء ورجوع", 'callback_data' => "back2"]],
];
$backYoussef = [
[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "backstart"]],
];
$Youssef = [ 
    [['text' => "🚀 ⪼ بدء الإستخدام.", 'callback_data' => "home"]],
    [['text' => "🔏 ⪼ الشروط و الخصوصية.", 'callback_data' => "help2"]],
    [['text'=> "💬 ⪼ التواصل المباشر.", 'callback_data'=> "super"]],
];
// ملف لتخزين آخر وقت إرسال رسالة /start لكل مستخدم
$start_timing_file = 'data/ban_timing.json';
$start_timings = json_decode(file_get_contents($start_timing_file), true);
if (!$start_timings) {
    $start_timings = [];
}

$current_time = time(); // الوقت الحالي بالثواني
$start_message_delay = 5; // مدة الانتظار بين الأوامر (5 ثواني)

if ($text == '/start') {
    // تحقق من آخر وقت تم فيه إرسال رسالة /start لهذا المستخدم
    if (!isset($start_timings[$chat_id]) || ($current_time - $start_timings[$chat_id] >= $start_message_delay)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*👤︙مرحباً بك* [$first_name](tg://user?id=$id) 🖤.

🤖︙بوت *اوربيتكسا* - *$NameBotG* 🤖. هو بوت *مختص* *بتقديم* الخدمات *الرائجة* في مواقع *التواصل الإجتماعي* ⭐.

*🚀 ⌯ اضغط على زر بدء الإستخدام للدخول. 👇🏻.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => $Youssef
            ])
        ]);
        // تحديث وقت آخر إرسال لهذا المستخدم
        $start_timings[$chat_id] = $current_time;
        // حفظ التحديثات في الملف
        file_put_contents($start_timing_file, json_encode($start_timings));
    } else {
        // إرسال تنبيه للمستخدم بأنه تجاوز مدة استقبال الأمر
        bot('answerCallbackQuery', [
            'callback_query_id' => $callback_query_id,
            'text' => "⌛ يجب الانتظار لمدة 5 ثواني قبل إعادة استخدام الأمر.",
            'show_alert' => true
        ]);
    }
    return;
}

if($data == 'home'){
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
            'usd'   => 'الدولار الأمريكي 💲',
            's'     => 'الريال السعودي 🇸🇦',
            'y'     => 'الريال اليمني القديم 🇾🇪',
            'd'     => 'آسيا 🇮🇶',
            'Aymn'  => 'Speed ♠️',
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
*🌪️︙العملة: $name_coin.*

🙋🏻︙يمكنك *التحكم بالبوت* عبر الأزرار في *الاسفل ⬇️.*",
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $start
        ])
    ]);
}
            if($data == 'backstart'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*👤︙مرحباً بك* [$first_name](tg://user?id=$id) 🖤.

🤖︙بوت *اوربــبتكسا* - *$NameBotG* 🤖. هو بوت *مختص* *بتقديم* الخدمات *الرائجة* في مواقع *التواصل الإجتماعي* ⭐.

*🚀 ⌯ اضغط على زر بدء الإستخدام للدخول. 👇🏻.*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $Youssef
            ])
        ]);
    }
    if($data == 'help2'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => $config->help,
'parse_mode'=>"MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $backYoussef
            ])
        ]);
    }
// إعداد معلومات البوت
        if($data == 'tgYoussef'){
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "
*👤︙مرحباً بك* [$first_name](tg://user?id=$id) 🖤.

🪗︙أنت *الآن* في *[📱 ⪼ قسم الأرقام.]*
🌐︙قم *بإختيار* القسم الذي *تريده* من *الأسفل* ⬇️.

-
            ",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $tg_buttons
            ])
        ]);
    }
    
if($exdata[0] == 'NewNumber'){
        $my_choice = $exdata[1];
        $all_countries  = json_decode(file_get_contents($API_NUMBER.'action=services'));
        if($all_countries->ok){
            bot('answerCallbackQuery',[
                'callback_query_id'=>$update->callback_query->id,
                'text'=>"✅ ⌯ يتم جلب الدول المتوفرة..", 
                'show_alert'=>true,
                'cache_time'=> 10
            ]);
$all_countries_array = $all_countries->data;
$buttons_c = [];
$double = [];
        $description = '';
        
          include ('sql_class.php');
        $sqsq = $sql->sql_select('users', 'user', $id2);
        $mycoin = $sqsq['mycoin'];
        $info_coin = get_coin_info($mycoin);
        $coin_name = $info_coin[1];
        $coin_rate = $info_coin[0];
        
        // تحديد وصف القسم بناءً على $my_choice
        if ($my_choice == 'ar') {
            $description = "🌐︙الدول العربية.";
            $filter = $arab;
        } elseif ($my_choice == 'er') {
            $description = "🌐︙الدول الأوروبية.";
            $filter = $europ;
        } elseif ($my_choice == 'ot') {
            $description = "🌐︙جميع الدول.";
            $filter = array_diff(array_keys($all_countries_array), array_merge($arab, $europ));
        }
foreach($all_countries_array as $key => $value){
/*
    if($my_choice == 'ar'){
        if(!in_array($key, $arab)){
            continue;
        }
    }
    if($my_choice == 'er'){
        if(!in_array($key, $europ)){
            continue;
        }
    }
    if($my_choice == 'ot'){
        if(in_array($key, $europ) or in_array($key, $arab)){
            continue;
        }
    }
*/
    // حساب السعر مع تضمين نسبة الربح وتحويل العملة
                $rate = $value->price;
                $prec_c = $config->Profit;
                $price = ((($rate / 100) * $prec_c) + $rate);
    $cty = $value->ar." ".$value->flag;
    $json_country[$key] = $cty;
    
    $double[] = ['text' => "$cty ⏎ $price$", 'callback_data' => "GetNumber|$key|$price"];
    if(count($double) == 2){
        $buttons_c[] = $double;
        $double = [];
    }

// الكود المتبقي لإرسال الأزرار أو معالجتها
                if(count($buttons_c) > 48){
                    bot('sendMessage', [
                        'chat_id' => $chat_id2,
                        'text' => "✅︙بعد الضغط على الدولة سوف يتم شراء رقم لك مباشرة ولايمكنك التراجع مهما حصل 📱.
🔏︙الآن قم بإختيار الدولة المراد شراء رقم لها من الأسفل ♠️.",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => $buttons_c,$back2
                        ])
                    ]);
                    $buttons_c = [];
                }
            }
            if(count($buttons_c) != 0){
                bot('sendMessage', [
                    'chat_id' => $chat_id2,
                    'text' => "✅︙بعد الضغط على الدولة سوف يتم شراء رقم لك مباشرة ولايمكنك التراجع مهما حصل 📱.
🔏︙الآن قم بإختيار الدولة المراد شراء رقم لها من الأسفل ♠️.",
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $buttons_c,$back2
                    ])
                ]);
            }
            file_put_contents("data/country.json", json_encode($json_country));

        }else{
            bot('answerCallbackQuery',[
                'callback_query_id'=>$update->callback_query->id,
                'text'=>"🤖 ⌯ حصل خطأ، حاول مجددا بعد قليل.!", 
                'show_alert'=>true,
                'cache_time'=> 10
            ]);
        }

    }
    if($exdata[0] == 'GetNumber'){
        $country = $exdata[1];
        $price = $exdata[2];
        include('./sql_class.php');
        if (mysqli_connect_errno()) {
            return;
        }
        $sq = $sql->sql_select('users', 'user', $id2);
        $coin = $sq['coin'];
        $spent = $sq['spent'];
        if($coin < $price){
            bot('sendmessage',[
                'chat_id' => $chat_id2,
                'text' => "
*❌︙رصيدك غير كافي للشراء..*
*☑️︙قم بإعادة شحن حسابك!!*", 
'parse_mode' => "MarkDown",
            ]);
            return;
        }
        $mm = $sql->sql_readarray_count('order_waiting') + $sql->sql_readarray_count('order_done');
$Aymmmm = $mm + 1;
$Aymmm  = $mm + 1;
$Aymm  = $mm + 1;
$Aym  = $mm + 1;
        bot('answerCallbackQuery',[
            'callback_query_id'=>$update->callback_query->id,
            'text'=>"✅︙يتم شراء الرقم لك..", 
            'show_alert'=>true,
            'cache_time'=> 10
        ]);
        $coin_after = $coin - $price;
        $spent_after = $spent + $price;
        $sql->sql_edit('users', 'coin', $coin_after, 'user', $id2);
        #$buy_number = json_decode(file_get_contents($API_NUMBER.'action=number&service='.$country));
        $buy_number = json_decode(file_get_contents($API_NUMBER.'action=number&service='.$country));
        if($buy_number->ok){
            $new_number = $buy_number->data->number;
            $name_country = $get_country_name->{$country};
            $YoTlb = "*✅︙عملية شراء رقم جديدة.*";
            $cap = "
🎬︙التطبيق : *تيليجرام.*
*🧿︙رقم الطلب* :  *$Aymmm*

*☎️︙الرقم* : `$new_number`
*🌐︙الدولة : $name_country*.
*💸︙السعر* : $price $coin_name

*🔗︙يرجى تسجيل الرقم في تيليجرام ، بعد التسجيل ، إضغط على ( ✅︙طلب الكود  ) للحصول على كود التفعيل*.";

            $cap_for_ch = "
🎬︙التطبيق : *تيليجرام.*
🧿︙رقم الطلب : *$Aymmm*.

*📱︙الرقم* : ".substr_replace($new_number, '××××', -4)."
*🌐︙الدولة : $name_country*.
*💸︙السعر: $price $coin_name*

*🆔︙العميل : *".substr_replace($id2, '×××', -3)."
";

bot('editmessagetext', [
    'chat_id' => $chat_id2,
    'message_id' => $message_id2,
    'text' => $YoTlb.$cap,
    'parse_mode' => "MarkDown",
    'reply_markup' => json_encode([
        'inline_keyboard' => [
            [['text' => "✅︙طلب الكود", 'callback_data' => "GetCode|".$new_number]],
        ]
    ])
]);

bot('sendmessage', [
    'chat_id' => $dev,
    'text' => "
*✅︙عملية شراء رقم جديدة*.".$cap."

*🆔︙الزبون : `$id2`*.

💲︙رصيده قبل الشراء : $coin $coin_name
💸︙رصيده بعد الشراء : $coin_after $coin_name
💳︙رصيده المصروف : $spent_after $coin_name
    ",
    'parse_mode' => "MarkDown",
    'reply_markup' => json_encode([
        'inline_keyboard' => $my_bot
    ])
]);

bot('sendmessage', [
    'chat_id' => $IDCH,
    'text' => $YoTlb.$cap_for_ch,
    'parse_mode' => "MarkDown",
    'reply_markup' => json_encode([
        'inline_keyboard' => $my_bot
    ])
]);

            $spent_after = $spent + $price;
            $sql->sql_write('number_done(user,type,caption)', "VALUES('$id2','telegram','$cap')");
            $sql->sql_edit('users', 'spent', $spent_after, 'user', $id2);
        }else{
            $sql->sql_edit('users', 'coin', $coin, 'user', $id2);
                $cty = $value->ar." ".$value->flag;
    $json_country[$key] = $cty;
            bot('sendmessage',[
                'chat_id' => $chat_id2,
                'text' => "*⚠️︙عذراً حدث خطأ أثناء الشراء وتم إلغاء العملية نظراً لعدم كفاية الرصيد في المزود،*
*☑️︙تم إرسال إبلاغ للإدارة لإعادة التعبئة.*", 
                'parse_mode' => "MarkDown",
            ]);
                bot('sendMessage', [
                    'chat_id' => $dev2,
                    'text' =>"*⛔︙خطأ جديد في قسم [📱 ⪼ الأرقام].*
🌐︙الدولة : *$cty*.
⚠️ ⌯ تقرير الخطأ : *رصيدك غير كافي يرجى إعادة الشحن.*

*☑️︙تم إبلاغ المستخدم بإعادة التعبئة بأسرع وقت ممكن.*",
                   'parse_mode' => "MarkDown",
                   'disable_web_page_preview' => true,
                ]);
            return;
        }

    }


    if($exdata[0] == 'GetCode'){
        bot('answerCallbackQuery',[
            'callback_query_id'=>$update->callback_query->id,
            'text'=>'✅︙يتم الحصول على الكود، انتظر قليلا..', 
            'show_alert'=>true,
            'cache_time'=> 2
        ]);
        $number = $exdata[1];
        $get_code = json_decode(file_get_contents($API_NUMBER.'action=getCode&nmbr='.$number));
$price = $value->price + (($value->price / 100) * $config->Profit);
    $cty = $value->ar." ".$value->flag;
    $json_country[$key] = $cty;
        if($get_code->ok){
            $code = $get_code->data->number->code;
            $pass = $get_code->data->number->password;
            bot('sendmessage', [
                'chat_id' => $chat_id2,
                'text' => "
*✅︙تم وصول الكود بنجاح 🤧🖤.

*📞 ⌯ 𝑵𝑼𝑴𝑩𝑬𝑹* : `$number`
*💬 ⌯ 𝑪𝑶𝑫𝑬 : `$code`
                ",
                'parse_mode' => "MarkDown",
                'reply_markup' => json_encode([
                    'inline_keyboard' => $MyYoussef
                                    ])
            ]);
            

        }else{
            bot('answerCallbackQuery',[
                'callback_query_id'=>$update->callback_query->id,
                'text'=>"🤖︙لم يصل الكود بعد.. انتظر قليلا ثم حاول مجدداً ❌.", 
                'show_alert'=>true,
                'cache_time'=> 10
            ]);
        }

    }

    if($exdata[0] == 'LogOut'){
        $number = $exdata[1];
        $logout = json_decode(file_get_contents($API_NUMBER.'action=logout&nmbr='.$number));
        if($logout->ok){
            bot('answerCallbackQuery',[
                'callback_query_id'=>$update->callback_query->id,
                'text'=>"✅︙تم بنجاح..", 
                'show_alert'=>true,
                'cache_time'=> 10
            ]);
        }else{
            bot('answerCallbackQuery',[
                'callback_query_id'=>$update->callback_query->id,
                'text'=>"❌︙فشل، قد يعود السبب لتسجيل الخروج مسبقا", 
                'show_alert'=>true,
                'cache_time'=> 10
            ]);
        }

    }
    if ($error == "You have active order with this link. Please wait until order being completed.") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "*❌︙يبدوا أن هناك طلب نشط بنفس الرابط ، إنتظر حتى ينتهي طلبك الأول.*",
            'parse_mode' => "MarkDown",
            'disable_web_page_preview' => true,
        ]);
    }
    include ('Youssef.php');
?>