<?php
#=========={الدعم}==========#
/*
$message_id           = $update->message->message_id;
$text = $update->message->text;
$chat_id = $update->message->chat->id;
$admin = 6506780205;// Your Id  ايديك 😘
if(isset($update->callback_query)){
    $data = $update->callback_query->data;
    $chat_id = $update->callback_query->message->chat->id;
    $message_id = $update->callback_query->message->message_id;
    $first = $update->callback_query->message->chat->first_name;
    $user = $update->callback_query->message->chat->username;
    $tc = $update->callback_query->message->chat->type;
}
$re = $update->message->reply_to_message;
$re_id = $update->message->reply_to_message->from->id;
$photo = $message->photo;
$video = $message->video;
$sticker = $message->sticker;
$file = $message->document;
$audio = $message->audio;
$voice = $message->voice;
$caption = $message->caption;
$photo_id = $message->photo[0]->file_id;
$video_id = $message->video->file_id;
$sticker_id = $message->sticker->file_id;
$file_id = $message->document->file_id;
$music_id = $message->audio->file_id;
$voice_id = $message->voice->file_id;
$video_note = $message->video_note;
$video_note_id = $video_note->file_id;
$tc = $message->chat->type;
$PHPXX = json_decode(file_get_contents("PHPXX.json"), true);

// زر بدء التواصل المباشر
if($data == "super"){
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "
*🙋🏻︙أهلاً وسهلاً بك، عزيزي* : [$first](tg://user?id=$id)

*☑️︙أنت الآن في تواصل مباشر مع إدارة البوت،*
⚠️︙كل ما تكتبه هنا سيصل إلى الإدارة فوراً دون أي وسيط 🧑🏻‍💻.

*🚫︙يرجى الامتناع عن إرسال أي إساءة، فاحترامك لنا دليل على احترامك لنفسك.*

⤵️︙تفضل بكتابة رسالتك، وسيتم الرد عليك خلال 0-5 ساعات.
",
        'parse_mode' => "MarkDown",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "🚫 ⪼ إلغاء التواصل.", 'callback_data' => "back2"]]
            ]
        ])
    ]);
    file_put_contents("data/id/$id/twas.txt", "tw");
    file_put_contents("PHPXX.json", json_encode($PHPXX));
}

// إلغاء التواصل من جهة العميل
if($text == "/start" and $twas == 'tw'){
    unlink("data/id/$id/step.txt");
    unlink("data/id/$id/twas.txt");
    exit;
}

// إلغاء التواصل باستخدام زر إلغاء من العميل
if($data == "back2" and $twas == 'tw'){
    unlink("data/id/$id/step.txt");
    unlink("data/id/$id/twas.txt");
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅︙تم إغلاق التذكرة و التواصل بنجاح.",
        'parse_mode' => "MarkDown"
    ]);
    exit;
}

// إرسال الرسالة للإدارة
if($text != "/start" and $chat_id != $admin and $message and $tc == "private" and $twas == 'tw'){
    $mes = bot('forwardMessage', [
        'chat_id' => $admin,
        'from_chat_id' => $id,
        'message_id' => $message_id,
    ]);
    $send = $mes->result->message_id;
    $PHPXX['tws'][$send]['User'] = $id;
    $PHPXX['tws'][$send]['Message'] = $message_id;
    file_put_contents("PHPXX.json", json_encode($PHPXX));
    bot('sendMessage', [
        'chat_id' => $id,
        'text' => "
*☑️︙تم إرسال رسالتك للإدارة بنجاح.!
⏰︙يرجى الإنتظار ريثما يتم الرد عليك 👍🏻.*
",
        'parse_mode' => "MarkDown",
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "🚫 ⪼ إلغاء التواصل.", 'callback_data' => "back2"]]
            ]
        ])
    ]);
}

// الرد من الإدارة إلى العميل
if($chat_id == $admin and $PHPXX['tws'][$message->reply_to_message->message_id] != null and isset($update->message->reply_to_message)){
    $messageid = $PHPXX['tws'][$message->reply_to_message->message_id]['Message'];
    $Alkhaledi = $PHPXX['tws'][$message->reply_to_message->message_id]['User'];
    $Tesaa = "";
    if($text){
        bot('sendMessage', [
            'chat_id' => $Alkhaledi,
            'text' => "$Tesaa$text",
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($photo) {
        bot('sendPhoto', [
            'chat_id' => $Alkhaledi,
            'photo' => $photo_id,
            'caption' => $Tesaa . $caption,
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($video) {
        bot('sendVideo', [
            'chat_id' => $Alkhaledi,
            'video' => $video_id,
            'caption' => $Tesaa . $caption,
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($video_note) {
        bot('sendVideoNote', [
            'chat_id' => $Alkhaledi,
            'video_note' => $video_note_id,
        ]);
    } elseif($sticker) {
        bot('sendSticker', [
            'chat_id' => $Alkhaledi,
            'sticker' => $sticker_id,
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($file) {
        bot('sendDocument', [
            'chat_id' => $Alkhaledi,
            'document' => $file_id,
            'caption' => $Tesaa . $caption,
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($music) {
        bot('sendAudio', [
            'chat_id' => $Alkhaledi,
            'audio' => $music_id,
            'caption' => $Tesaa . $caption,
            'reply_to_message_id' => $messageid,
        ]);
    } elseif($voice) {
        bot('sendVoice', [
            'chat_id' => $Alkhaledi,
            'voice' => $voice_id,
            'caption' => $Tesaa . $caption,
            'reply_to_message_id' => $messageid,
        ]);
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "
*☑️ ⪼ تم الرد على المستخدم بنجاح 🔥.*
",
        'parse_mode' => "MarkDown",
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "☑️ ⪼ العميل 👤.", 'url' => "tg://openmessage?user_id=$Alkhaledi"]],
                [['text' => "🚫 ⪼ إلغاء التواصل.", 'callback_data' => "close_ticket"]],
            ]
        ])
    ]);
}

// زر إغلاق التذكرة من جهة الإدارة
if ($data == "close_ticket") {
    $Alkhaledi = $PHPXX['tws'][$message_id]['User'];
    unset($PHPXX['tws'][$message_id]);
    file_put_contents("PHPXX.json", json_encode($PHPXX));

    // إرسال رسالة إغلاق الدردشة للعميل
    bot('sendMessage', [
        'chat_id' => $Alkhaledi,
        'text' => "⚠️︙تم إغلاق الدردشة. إذا كنت بحاجة إلى مزيد من الدعم، يرجى فتح تذكرة جديدة.",
        'parse_mode' => "MarkDown"
    ]);
    file_put_contents("PHPXX.json", json_encode($PHPXX));
    unlink("data/id/$Alkhaledi/step.txt");
    unlink("data/id/$Alkhaledi/twas.txt");
    // إرسال رسالة إغلاق التذكرة للإدارة
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅︙تم إغلاق التذكرة بنجاح.",
        'parse_mode' => "MarkDown"
    ]);
    exit;
}
*/

$vote_file = "vote.json";

// تحميل بيانات التصويت
$vote_data = file_exists($vote_file) ? json_decode(file_get_contents($vote_file), true) : [];

// تحقق من أن التصويت لا يزال مستمراً
#===============[ إلغاء العملية في حالة /start أو back2 ]================#
if (($vote_data['step'] ?? '') != 'done' && ($data == "/start" || $text == "/start" || $data == "back2")) {
    if (file_exists($vote_file)) {
        unlink($vote_file);
    }
    $vote_data = []; // ضروري نفضيه عشان ما يستخدم القيم القديمة
    return;
}
#===============[ زر اختيار خدمة التصويت ]================#
if ($data == "vote") {
    $vote_data = [
        'step' => 'waiting_for_vote_link',
        'link' => '',
        'count' => 0,
        'choice' => '',
    ];
    file_put_contents($vote_file, json_encode($vote_data));
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "✅︙تم إختيار الخدمة بنجاح.

🛒 ⌯ الخدمة : *تصويتات تليجرام الأسرع 🔥.*

*⤵️ ⌯ وصف الخدمة :*
*- البدء : 0-5 دقائق 🚀.
- السرعة 10k باليوم 🔥.*
*- الضمان : لايوجد ⚠️.
- النزول : لايوجد نهائياً ⚠️.*
*- الرابط : رابط التصويت العام.*

*⤵️ ⌯ معلومات الخدمة :*
💸 ⌯ سعر 1k عضو = *$0.50*
👇🏻 ⌯ الحد الأدنى : *10*.
👆🏻 ⌯ الحد الأعلى : *10k*.

*🔗 أرسل الآن رابط التصويت العام.*",
        'parse_mode' => "Markdown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back2
        ])
    ]);
    return;
}

#===============[ استقبال رابط التصويت ]================#
if (($vote_data['step'] ?? '') == 'waiting_for_vote_link' && $text) {
    if (filter_var($text, FILTER_VALIDATE_URL)) {
        $vote_data['link'] = $text;
        $vote_data['step'] = 'waiting_for_vote_count';
        file_put_contents($vote_file, json_encode($vote_data));

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👥︙أرسل العدد المطلوب.",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]]]
            ])
        ]);
        return;
    } 
}

if (($vote_data['step'] ?? '') == 'waiting_for_vote_count' && $text !== '') {
    // التحقق من أن النص رقم صحيح (بما في ذلك الصفر)
    if (is_numeric($text) && $text >= 0) {
        $vote_data['count'] = (int)$text;  // تحويل النص إلى عدد صحيح
        $vote_data['step'] = 'waiting_for_vote_choice';
        file_put_contents($vote_file, json_encode($vote_data));

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🔘︙أرسل رقم الإختيار مثال : *0-1-2-3*",
            'parse_mode' => "Markdown",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]]]
            ])
        ]);
    }
    return;
}

#===============[ استقبال رقم الإختيار ]================#
if (($vote_data['step'] ?? '') == 'waiting_for_vote_choice' && $text) {
    if (is_numeric($text)) {
        $vote_data['choice'] = (int)$text;
        $vote_data['step'] = 'done';
        file_put_contents($vote_file, json_encode($vote_data));

        $count = $vote_data['count'];
        $choice = $vote_data['choice'];
        $link = $vote_data['link'];
        $price = number_format(($count / 1000) * 0.5, 3, '.', '');

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🛒 ⌯ الخدمة : *تصويتات تليجرام الأسرع 🔥.*

👥 ⌯ العدد المطلوب : *$count*
📢 ⌯ رقم الإختيار : *$choice*
🔗 ⌯ الرابط : *$link*
💸 ⌯ السعر : *$price \$*

- بمجرد موافقتك ، *سيتم تقديم* الطلب *وخصم المبلغ* *ولايمكننا إلغاء* الطلب *تحت أي ظرف* ، لذلك *( تأكد من الرابط جيداً ) ⚠️.*",
            'parse_mode' => "Markdown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $taked
            ])
        ]);
    } 
    return;
}

#===============[ زر الرجوع ]================#
if ($data == "back2") {
    if (file_exists($vote_file)) {
        unlink($vote_file);
    }
    return;
}

$commentvote_file = "commentvote.json";

// تحميل بيانات التصويت
$commentvote_data = file_exists($commentvote_file) ? json_decode(file_get_contents($commentvote_file), true) : [];

// إلغاء العملية في حالة /start أو back2
if (($commentvote_data['step'] ?? '') != 'done' && ($data == "/start" || $text == "/start" || $data == "back2")) {
    if (file_exists($commentvote_file)) {
        unlink($commentvote_file);
    }
    $commentvote_data = [];
    return;
}

// زر اختيار خدمة التصويت
if ($data == "comment") {
    $commentvote_data = [
        'step' => 'waiting_for_comment_link',
        'link' => '',
        'count' => 0,
        'username' => ''
    ];
    file_put_contents($commentvote_file, json_encode($commentvote_data));
    bot('editmessagetext', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "✅︙تم إختيار الخدمة بنجاح.

🛒 ⌯ الخدمة : *لايكات تعليقات إنستجرام 🔥.*

*⤵️ ⌯ وصف الخدمة :*
*- البدء : 0-5 دقائق 🚀.
- السرعة 10k باليوم 🔥.*
*- الضمان : لايوجد ⚠️.
- النزول : لايوجد نهائياً ⚠️.*
*- الرابط : الفيديو أو الصورة العامة*

*⤵️ ⌯ معلومات الخدمة :*
💸 ⌯ سعر 1k عضو = *$0.50*
👇🏻 ⌯ الحد الأدنى : *10*.
👆🏻 ⌯ الحد الأعلى : *10k*.

*🔗 أرسل الآن رابط التصويت العام.*",
        'parse_mode' => "Markdown",
        'reply_markup' => json_encode([
            'inline_keyboard' => $back2
        ])
    ]);
    return;
}

// استقبال رابط التصويت
if (($commentvote_data['step'] ?? '') == 'waiting_for_comment_link' && $text) {
    if (filter_var($text, FILTER_VALIDATE_URL)) {
        $commentvote_data['link'] = $text;
        $commentvote_data['step'] = 'waiting_for_count';
        file_put_contents($commentvote_file, json_encode($commentvote_data));

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👥︙أرسل العدد المطلوب.",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]]]
            ])
        ]);
        return;
    } 
}

// استقبال عدد الأصوات
if (($commentvote_data['step'] ?? '') == 'waiting_for_count' && $text) {
    if (is_numeric($text) && $text > 0) {
        $commentvote_data['count'] = (int)$text;
        $commentvote_data['step'] = 'waiting_for_username';
        file_put_contents($commentvote_file, json_encode($commentvote_data));

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🔘︙أرسل يوزر صاحب التعليق مثال : `@username`",
            'parse_mode' => "Markdown",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[['text' => "🔙 ⪼ رجوع.", 'callback_data' => "back2"]]]
            ])
        ]);
        return;
    }
}

// استقبال يوزر صاحب التعليق
if (($commentvote_data['step'] ?? '') == 'waiting_for_username' && $text) {
    if (preg_match('/^@[\w\d_]{5,}$/', $text)) {
        $commentvote_data['username'] = $text;
        $commentvote_data['step'] = 'done';
        file_put_contents($commentvote_file, json_encode($commentvote_data));

        $count = $commentvote_data['count'];
        $username = $commentvote_data['username'];
        $link = $commentvote_data['link'];
        $price = round(($count / 1000) * 0.5, 2);

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "🛒 ⌯ الخدمة : *لايكات تعليقات إنستجرام 🔥.*

👥 ⌯ العدد المطلوب : *$count*
👤 ⌯ يوزر صاحب التعليق : *$username*
🔗 ⌯ الرابط : *$link*
💸 ⌯ السعر : *$price \$*

- بمجرد موافقتك ، *سيتم تقديم* الطلب *وخصم المبلغ* *ولايمكننا إلغاء* الطلب *تحت أي ظرف* ، لذلك *( تأكد من الرابط جيداً ) ⚠️.*",
            'parse_mode' => "Markdown",
            'reply_markup' => json_encode([
                'inline_keyboard' => $takedcom
            ])
        ]);
    } 
    return;
}

// زر الرجوع
if ($data == "back2") {
    if (file_exists($commentvote_file)) {
        unlink($commentvote_file);
    }
    return;
}