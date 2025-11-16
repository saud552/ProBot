<?php
// كود صغير لإنشاء الملفات والمجلدات إذا لم تكن موجودة

if ($data == "done2") {
    include ('sql_class.php');
    
    // استعلام بيانات العميل من قاعدة البيانات
    $sqsq = $sql->sql_select('users', 'user', $id2);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];

    // قراءة بيانات التصويت من ملف JSON بالهيكل الجديد
    $VotingData = json_decode(file_get_contents("vote.json"), true);

    // التحقق من وجود بيانات التصويت
    if (isset($VotingData['step']) && $VotingData['step'] == "done") {
        // استخدام بيانات التصويت من الـ JSON مباشرة
        $vote_count = $VotingData['count'];
        $choice = $VotingData['choice'];
        $link = $VotingData['link'];

        // حساب السعر بناءً على عدد الأصوات
        $base_price_per_1000_votes = 0.50;
$price = floatval(number_format(($vote_count / 1000) * $base_price_per_1000_votes, 3, '.', ''));

        // التحقق من الرصيد
        $sq = $sql->sql_select('users', 'user', $id2);
        $coin = $sq['coin'];
        $spent = $sq['spent'];
        
        if($coin < $price){
            bot('sendmessage', [
                'chat_id' => $chat_id2,
                'text' => "*🚫︙رصيدك غير كافي للطلب، قم بإعادة شحن حسابك.*", 
                'parse_mode' => "MarkDown"
            ]);
            return;
        }

        if ($price <= 0) {
            bot('sendmessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ حدث خطأ في حساب السعر، يرجى المحاولة لاحقاً.",
                'parse_mode' => "MarkDown"
            ]);
            return;
        }

        // تحديث الرصيد والمصروفات
        $coin_after = $coin - $price;
        $spent_after = $spent + $price;
        $sql->sql_edit('users', 'coin', $coin_after, 'user', $id2);
        $sql->sql_edit('users', 'spent', $spent_after, 'user', $id2);

        // حفظ رقم الطلب
        file_put_contents('data/order.txt', $id2 . "\n", FILE_APPEND);
        $ordersYoussef = file_get_contents('data/order.txt');
        $order_lines = explode("\n", $ordersYoussef);
        $total_orders = count(array_filter($order_lines));

        // إرسال الطلب للموقع
        file_get_contents("https://thelordofthepanels.com/api/v2?key=0bc1295f3100a0385ee8ea4bf9a7edd0&action=add&service=581&link=$link&quantity=$vote_count&answer_number=$choice");

        // إخفاء جزء من البيانات للخصوصية
        $EngAldorafy = strlen($link) - 12;
        $EngAymn = substr($link, 0, $EngAldorafy);
        $EngA = '••••••••••••';
        $EngAymnAldorafi = $EngAymn . $EngA;
        $Three = strlen($id2) - 5;
        $Aaymn = substr($id2, 0, $Three);
        $Aaaymn = '•••••';
        $EngAymnnn = $Aaymn . $Aaaymn;

        // إرسال إشعار للعميل
        bot('editmessagetext', [
            'chat_id' => $chat_id2,
            'message_id' => $message_id2,
            'text' => "*✅︙تم طلب التصويت بنجاح.*

🛒 ⌯ الخدمة : *تصويتات تليجرام الأسرع 🔥.*
🧿 ⌯ رقم الطلب : *$total_orders*
👥 ⌯ العدد المطلوب : *$vote_count*
📢 ⌯ رقم الإختيار : *$choice*
🔗 ⌯ الرابط : *$link*
💸 ⌯ سعر الطلب : *$price $*

*- سيتم اشعارك تلقائياً في حال إكتمال الطلب.*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "🤖︙الطلب من هذه الخدمة.", 'callback_data' => "no_response"]],
                ]
            ])
        ]);

        // إشعار المسؤول أو القناة
        bot('sendmessage', [
            'chat_id' => $IDCH,
            'text' => "*✅︙عملية تصويت جديدة.*

🛒 ⌯ الخدمة : *تصويتات تليجرام الأسرع 🔥.*
🧿 ⌯ رقم الطلب : *$total_orders*
👥 ⌯ العدد المطلوب : *$vote_count*
📢 ⌯ رقم الإختيار : *$choice*
🔗 ⌯ الرابط : *$EngAymnAldorafi*
💸 ⌯ سعر الطلب : *$price $*

🆔 ⌯ العميل : *$EngAymnnn*",
            'parse_mode' => "MarkDown",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "🤖︙الطلب من هذه الخدمة.", 'callback_data' => "no_response"]],
                ]
            ])
        ]);
    } else {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "⚠️ لم يتم العثور على بيانات التصويت لهذا العميل.",
            'parse_mode' => "MarkDown"
        ]);
    }
}
// تضمين ملف CryptoCloudSDK
require_once 'PHP-CC-SDK.php'; 

// مفتاح API الخاص بك من Cryptocloud
$apiKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTkRZeU5URT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiJlZDU5ZTc2ZTAyY2Q3MjkwNzE0MjhjNjM4NWIwNWU2NWM1MWIyN2VkMTBkZjM3M2EwMDk1Y2E2ZDM0NWM4OTZhIiwiZXhwIjo4ODE0MTgyMzk2M30._Tm-hjYJv7v9cwPP0aE14cuLnAYFXT0kFBvz8Uqij8k"; // استبدل هذا بمفتاحك الخاص من Cryptocloud
$shop_id = "MLzGKDP3FOPwbN9P"; // معرّف المتجر
$cryptoCloud = new CryptoCloudSDK($apiKey);

// هنا نقوم بفحص الرسائل الواردة من البوت
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// وظيفة لقراءة بيانات الفواتير من ملف JSON
function readInvoices() {
    $filePath = 'data/invoice.json';
    if (!file_exists($filePath)) {
        file_put_contents($filePath, '[]'); // إنشاء ملف جديد إذا لم يكن موجودًا
    }
    $data = file_get_contents($filePath);
    return json_decode($data, true);
}

// وظيفة لحفظ الفاتورة في ملف JSON
function saveInvoice($invoiceData) {
    $filePath = 'data/invoice.json';
    $invoices = readInvoices();
    $invoices[] = $invoiceData; // إضافة الفاتورة الجديدة
    file_put_contents($filePath, json_encode($invoices, JSON_PRETTY_PRINT));
}

// وظيفة لتحديث حالة الفاتورة في ملف JSON
function updateInvoiceStatus($invoiceId, $status) {
    $filePath = 'data/invoice.json';
    $invoices = readInvoices();
    foreach ($invoices as &$invoice) {
        if ($invoice['invoice_id'] === $invoiceId) {
            $invoice['status'] = $status; // تحديث حالة الفاتورة
            break;
        }
    }
    file_put_contents($filePath, json_encode($invoices, JSON_PRETTY_PRINT));
}

// وظيفة للتحقق من وجود الفاتورة في ملف JSON
function getInvoice($invoiceId) {
    $invoices = readInvoices();
    foreach ($invoices as $invoice) {
        if ($invoice['invoice_id'] === $invoiceId) {
            return $invoice; // إرجاع بيانات الفاتورة
        }
    }
    return null; // إذا لم يتم العثور على الفاتورة
}
function saveUserState($chatId) {
    $dir = "data/invoice/$chatId";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
    }
    file_put_contents("$dir/awaiting_amount.txt", "awaiting_amount"); // إنشاء الملف
}

function isUserAwaitingAmount($chatId) {
    $file = "data/invoice/$chatId/awaiting_amount.txt";
    return file_exists($file); // التحقق من وجود الملف
}

function resetUserState($chatId) {
    $file = "data/invoice/$chatId/awaiting_amount.txt";
    if (file_exists($file)) {
        unlink($file); // حذف الملف
    }
}
if (isset($update['callback_query'])) {
    $callbackData = $update['callback_query']['data'];
    $chatId = $update['callback_query']['message']['chat']['id'];
    $messageId = $update['callback_query']['message']['message_id'];

    // التحقق إذا كان الزر هو USDT
    if ($callbackData == 'USDT') {
        if (!isUserAwaitingAmount($chatId)) {
            // إرسال رسالة لطلب المبلغ
            bot('EditMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "*👤︙أهلاً بك عزيزي* [$first_name](tg://user?id=$chatId)

☑️︙يمكنك *شحن رصيدك بالبوت* بواسطة *العملات الرقمية* بجميع *أنواعها [ USDT, LTC, TON ]* إلخ...

⚠️︙ملاحظة : توجد عمولة *إيداع تطبق عند دفع الفاتورة* بقيمة *$1.4* للعملات : *[ USDT, ETH, TRX, BTC, USDC ]* 

*ويجب دفع* المبلغ *المطلوب مع العمولة* دون أي *نقصان* أو *زيادة* والإ *لن يتم إضافة* أي رصيد .

*- ارسل الآن المبلغ المراد شحنه بعملة الدولار ، أرقام حصراً*.",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $back2 // تأكد من تعريف $back2
                ])
            ]);
            saveUserState($chatId);

            // حفظ الرسالة في ملف
            $filePath = "data/invoice/$chatId/awaiting_amount.txt";
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }
            file_put_contents($filePath, "💰 *الرجاء إدخال المبلغ المراد شحنه بالـ USDT*، مثال : 10");
        } else {
            // إذا كان المستخدم في مرحلة إدخال المبلغ بالفعل
            bot('answerCallbackQuery', [
                'callback_query_id' => $update['callback_query']['id'],
                'text' => '❌ أنت بالفعل في مرحلة إدخال المبلغ.',
                'show_alert' => true
            ]);
        }
    }
}
if (isset($update['message']['text'])) {
    $messageText = $update['message']['text'];
    $chatId = $update['message']['chat']['id'];

    // التحقق إذا كان المستخدم في مرحلة إدخال المبلغ
    if (isUserAwaitingAmount($chatId)) {
        if (is_numeric($messageText)) {
            $amount = floatval($messageText); // المبلغ المراد دفعه

            // إنشاء بيانات الفاتورة مع تضمين معرّف المتجر (shop_id)
            $invoiceData = [
                "amount" => $amount,  // المبلغ المدخل من المستخدم
                "currency" => "USD",  // العملة (يمكنك تغييرها حسب المتاح)
                "order_id" => uniqid(), // رقم فريد للطلب
                "description" => "Payment for order", // وصف الفاتورة
                "success_url" => "https://yourwebsite.com/success", // رابط عند نجاح الدفع
                "fail_url" => "https://yourwebsite.com/fail", // رابط عند فشل الدفع
                "shop_id" => $shop_id // إضافة معرّف المتجر
            ];

            try {
                // محاولة إنشاء الفاتورة
                $response = $cryptoCloud->createInvoice($invoiceData);
                // تسجيل الاستجابة بالكامل لتحليل الأخطاء
                logError("Response from createInvoice: " . print_r($response, true));

                if (isset($response['result']['link'])) {
                    // إذا تم إنشاء الفاتورة بنجاح
                    $invoiceUrl = $response['result']['link'];
                    $invoiceId = isset($response['result']['uuid']) ? $response['result']['uuid'] : 'غير متوفر'; // استخدام uuid كرقم الفاتورة

                    // تسجيل الفاتورة في ملف JSON
                    saveInvoice([
                        'invoice_id' => $invoiceId,
                        'chat_id' => $chatId,
                        'amount_usd' => $amount,
                        'status' => 'pending',
                        'invoiceUrl' => $invoiceUrl,
                    ]);

                    // إرسال الرسالة مع الأزرار إلى المستخدم
                    bot('sendMessage', [
                        'chat_id' => $chatId,
                        'text' => "*فاتورة شحن بقيمة {$amount} $*\n\n" .
                                  "**رقم الفاتورة**: `{$invoiceId}`\n" .
                                  "**حالة الدفع**: ⏳ قيد الانتظار\n\n" .
                                  "بعد *الدفع الناجح* ، إضغط *على* زر *[ 🔄 تحديث ]* ، للتحقق من *حالة الدفع* وشحن *الرصيد*.\n\n" .
                                  
                                  "توجد عمولة تبلغ *$1.4* للعملات *[ USDT, ETH, BTC, USDC ]* 

*[ LTC, TON ] العمولة $0.02 فقط.*

*ويجب دفع المبلع المطلوب في صفحة الدفع كما هو بدون أي نقصان* و الا لن *يتم شحن الرصيد* 

بسبب حالة *الدفع الجزئية* ، في حال حدث ذلك ، يرجى *الضغط* على زر *[ 📞 الدعم الفني ]*
",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => '🚀 ⪼ ادفع الآن', 'url' => $invoiceUrl],
                                    ['text' => '🔄 ⪼ تحديث', 'callback_data' => 'check:' . $invoiceId]
                                ],
                                [
                                    ['text' => '📞 ⪼ الدعم الفني', 'url' => 'https://t.me/SupNorthBot']
                                ]
                            ]
                        ])
                    ]);

                    // إرسال رسالة إلى الأدمن
                    bot('sendMessage', [
                        'chat_id' => $dev,
                        'text' => "*تم إنشاء فاتورة جديدة*\n\n" .
                                  "*المستخدم*: `{$chatId}`\n" .
                                  "*أسمه :* [$first_name](tg://user?id=$chatId) \n" .
                                  "**رقم الفاتورة**: `{$invoiceId}`\n" .
                                  "**المبلغ**: {$amount} $",
                        'parse_mode' => 'Markdown'
                    ]);

                    // إعادة تعيين حالة المستخدم بعد نجاح العملية
                    resetUserState($chatId);
                    unlink($file);
                } else {
                    // في حال وجود خطأ، طباعة رسالة الخطأ من الاستجابة
                    $errorMessage = isset($response['error']) ? $response['error'] : 'حدث خطأ غير معروف';
                    sendMessage($chatId, "عذرًا، حدث خطأ أثناء إنشاء الفاتورة: " . $errorMessage);
                }
            } catch (Exception $e) {
                // التعامل مع الأخطاء وإرسال رسالة توضيح
                logError("Exception caught: " . $e->getMessage());
                sendMessage($chatId, "حدث خطأ: " . $e->getMessage());
            }
        } else {
            // إذا لم يُدخل المستخدم مبلغًا صحيحًا
            sendMessage($chatId, "الرجاء إدخال مبلغ صحيح.");
        }
    }
}

// وظيفة لإرسال رسالة إلى المستخدم
function sendMessage($chatId, $message) {
    $telegramApiUrl = "https://api.telegram.org/bot6506070670:AAH1l2LcMb1BQRK5R959HzjJn7p_s72k7_I/sendMessage"; // استبدل بـ API Key للبوت الخاص بك
    $postData = [
        'chat_id' => $chatId,
        'text' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegramApiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// وظيفة لتسجيل الأخطاء في ملف
function logError($message) {
    $logFile = "error_log.txt";
    $timestamp = date("Y-m-d H:i:s");
    $logMessage = "[" . $timestamp . "] " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// وظيفة للتحقق من حالة الدفع
function checkPaymentStatus($invoiceId) {
    try {
        // استدعاء الـ SDK للتحقق من حالة الدفع باستخدام getInvoiceInfo
        $cryptoCloud = new CryptoCloudSDK("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTkRZeU5URT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiJlZDU5ZTc2ZTAyY2Q3MjkwNzE0MjhjNjM4NWIwNWU2NWM1MWIyN2VkMTBkZjM3M2EwMDk1Y2E2ZDM0NWM4OTZhIiwiZXhwIjo4ODE0MTgyMzk2M30._Tm-hjYJv7v9cwPP0aE14cuLnAYFXT0kFBvz8Uqij8k");  // استبدل "YOUR_API_KEY" بمفتاح API الخاص بك
        $response = $cryptoCloud->getInvoiceInfo([$invoiceId]); // تمرير الـ invoiceId في المصفوفة

        // سجّل الاستجابة لعرضها في ملف الأخطاء أو الطباعة
        logError('Response for invoice ' . $invoiceId . ': ' . json_encode($response));

        // تحقق إذا كانت الاستجابة تحتوي على أخطاء
        if (isset($response['detail'])) {
            logError('API Error: ' . $response['detail']);
            return ['status' => 'Error', 'message' => $response['detail']];
        }

        // التحقق من أن الاستجابة ليست فارغة وأن البيانات داخل المصفوفة صحيحة
        if (empty($response) || !isset($response['result']) || !is_array($response['result'])) {
            logError('Empty or incorrect response for invoice ' . $invoiceId);
            return ['status' => 'Error', 'message' => 'استجابة غير صحيحة، يرجى المحاولة مرة أخرى.'];
        }

// الوصول إلى بيانات الفاتورة
$invoiceResult = $response['result'][0];  // أول عنصر في المصفوفة result

// استخراج الحقول المهمة
$status = strtolower($invoiceResult['status'] ?? '');
$invoiceStatus = strtolower($invoiceResult['invoice_status'] ?? '');
$amountUsd = $invoiceResult['amount_usd'] ?? 0; // المبلغ المطلوب
$amountPaidUsd = $invoiceResult['amount_paid_usd'] ?? 0; // المبلغ المدفوع فعليًا

// منطق تحديد الحالة الفعلية
if ($status === 'paid' || $invoiceStatus === 'success') {
    return ['status' => 'Paid', 'amount_usd' => $amountUsd, 'amount_paid_usd' => $amountPaidUsd];
} elseif ($status === 'partial' || $invoiceStatus === 'partial') {
    return ['status' => 'partial', 'amount_usd' => $amountUsd, 'amount_paid_usd' => $amountPaidUsd];
} elseif ($status === 'pending' || $invoiceStatus === 'pending') {
    return ['status' => 'Pending', 'amount_usd' => $amountUsd, 'amount_paid_usd' => $amountPaidUsd];
} else {
    return ['status' => 'Failed', 'amount_usd' => $amountUsd, 'amount_paid_usd' => $amountPaidUsd];
}
    } catch (Exception $e) {
        // تسجيل الأخطاء في حالة حدوث مشكلة
        logError('Error while checking payment status for invoice ' . $invoiceId . ': ' . $e->getMessage());
        return ['status' => 'Error', 'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage()];
    }
}

// التحقق من وجود callback_query
if (isset($update['callback_query'])) {
    $callbackData = $update['callback_query']['data'];
    $chatId = $update['callback_query']['message']['chat']['id'];
    $messageId = $update['callback_query']['message']['message_id'];

// التحقق إذا كان الزر هو تحديث
if (strpos($callbackData, 'check:') === 0) {
    $invoiceId = substr($callbackData, 6); // استخراج رقم الفاتورة من callback_data

    // التحقق من حالة الفاتورة في ملف JSON
    $invoiceInfo = getInvoice($invoiceId);
    if (!$invoiceInfo) {
        // إذا لم يتم العثور على الفاتورة
        bot('answerCallbackQuery', [
            'callback_query_id' => $update['callback_query']['id'],
            'text' => '❌ الفاتورة غير موجودة.',
            'show_alert' => true
        ]);
        return;
    }

    // إذا كانت الفاتورة مدفوعة مسبقًا
    if ($invoiceInfo['status'] === 'paid') {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update['callback_query']['id'],
            'text' => '⚠️ تم شحن الرصيد مسبقًا لهذه الفاتورة.',
            'show_alert' => true
        ]);
        return;
    }

// استدعاء API للتحقق من حالة الدفع
$paymentStatus = checkPaymentStatus($invoiceId); // دالة للتحقق من حالة الدفع

// نعرّف المتغير هذا مرة وحده لتجنب الخطأ في حالة Pending أو Failed
$amountPaidUsd = $paymentStatus['amount_paid_usd'] ?? 0;

if ($paymentStatus['status'] == 'Paid' || $paymentStatus['status'] == 'partial') {
    // إذا كان الدفع ناجحًا أو جزئيًا
    $statusText = ($paymentStatus['status'] == 'Paid') ? "ناجحة ✅." : "جزئية ⚠️";
    $amountUsd = $amountPaidUsd; // نشحن المبلغ المدفوع فقط

    // تحديث قاعدة البيانات
    include('sql_class.php');
    $sqsq = $sql->sql_select('users', 'user', $chatId);
    $mycoin = $sqsq['mycoin'];
    $info_coin = get_coin_info($mycoin);
    $coin_name = $info_coin[1];

    $balanceBefore = $sqsq['coin'];
    $balanceAfter = $balanceBefore + $amountUsd;

    // تحديث الرصيد في قاعدة البيانات
    $sql->sql_edit('users', 'coin', $balanceAfter, 'user', $chatId);

    // تحديث الرسالة للعميل
    bot('editMessageText', [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => "*فاتورة شحن بقيمة {$amountUsd} \$*\n\n" .
                  "*رقم الفاتورة*: `{$invoiceId}`\n" .
                  "*حالة الدفع*: {$statusText}\n\n" .
                  "*رصيدك قبل الشحن*: {$balanceBefore} \$\n" .
                  "*رصيدك بعد الشحن*: {$balanceAfter} \$\n" .
                  "*تم شحن رصيدك بمبلغ*: {$amountUsd} \$",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => 'الدعم الفني', 'url' => 'https://t.me/SupNorthBot']]
            ]
        ])
    ]);

    // إرسال إشعار للأدمن
    bot('sendMessage', [
        'chat_id' => $dev,
        'text' => "*تم دفع الفاتورة*\n\n" .
                  "**معرف المستخدم**: `{$chatId}`\n" .
                  "**رقم الفاتورة**: `{$invoiceId}`\n" .
                  "**المبلغ المدفوع**: {$amountUsd} USD\n" .
                  "**رصيد العميل قبل الشحن**: {$balanceBefore} \$\n" .
                  "**رصيد العميل بعد الشحن**: {$balanceAfter} \$\n" .
                  "**نوع الدفع**: {$statusText}",
        'parse_mode' => 'Markdown'
    ]);

    // نحدث حالة الفاتورة إلى paid حتى في حالة الدفع الجزئي
    updateInvoiceStatus($invoiceId, 'paid');

} elseif ($paymentStatus['status'] == 'Pending') {
    // إذا كانت الحالة "قيد الانتظار"
    bot('editMessageText', [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => "*فاتورة شحن بقيمة {$amountPaidUsd} USD*\n\n" .
                  "**رقم الفاتورة**: `{$invoiceId}`\n" .
                  "**حالة الدفع**: ⏳ قيد الانتظار\n" .
                  "**الرجاء المحاولة مرة أخرى لاحقًا**",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => '🔄 تحديث', 'callback_data' => 'check:' . $invoiceId],
                 ['text' => '📞 الدعم الفني', 'url' => 'https://t.me/SupNorthBot']]
            ]
        ])
    ]);
} elseif ($paymentStatus['status'] == 'Failed') {
    // إذا كانت الحالة "فشل"
    bot('answerCallbackQuery', [
        'callback_query_id' => $update['callback_query']['id'],
        'text' => 'عذراً، لم يتم الدفع أو لا زال قيد المعالجة.',
        'show_alert' => true
    ]);
}
}
}
if ($data == 'back2'){
resetUserState($chatId);
unlink($file);
}
if (strpos($data, 'refill|') === 0) {
    $exdata = explode('|', $data);
    $order_id = $exdata[1];

    $api_sites = [
        [
            'url' => 'https://tigerspeed.store/api/v2',
            'key' => 'egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3'
        ],
        [
            'url' => 'https://bulkmedya.org/api/v2',
            'key' => 'ecbf5cec79658204f546f4d286438ea6'
        ],
        [
            'url' => 'https://thelordofthepanels.com/api/v2',
            'key' => '0bc1295f3100a0385ee8ea4bf9a7edd0'
        ],
        [
            'url' => 'https://smmstone.com/api/v2',
            'key' => '54a424b603072c613d6de5996e6faf34'
        ]
    ];

    $refill_success = false;
    $refill_id = null;

    foreach ($api_sites as $site) {
        $refill_url = $site['url'] . "?key=" . $site['key'] . "&action=refill&order=" . $order_id;
        $response = @file_get_contents($refill_url);
        $result = json_decode($response, true);

        if (isset($result['refill']) && $result['refill'] != 0) {
            $refill_id = $result['refill'];
            $refill_success = true;
            break;
        }
    }

    if ($refill_success) {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅︙تم *إرسال* طلب *التعويض* للعملية `$order_id` بنجاح.\n\n" .
                      "🆔︙رقم التعويض: `$refill_id`\n" .
                      "⏰︙تستغرق العملية من 0 إلى 24 ساعة، تأكد أن الحساب عام.",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '♻️تحقق︙من حالة التعويض', 'callback_data' => "checker|$refill_id"]]
                ]
            ])
        ]);
    } else {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "⚠️︙فشل التعويض ، الخدمة لاتدعم التعويض في الوقت الحالي.",
            'parse_mode' => 'Markdown'
        ]);
    }
}
if (strpos($data, 'cancel|') === 0) {
    $exdata = explode('|', $data);
    $order_id = $exdata[1];

    $api_sites = [
        [
            'url' => 'https://tigerspeed.store/api/v2',
            'key' => 'egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3'
        ],
        [
            'url' => 'https://bulkmedya.org/api/v2',
            'key' => 'ecbf5cec79658204f546f4d286438ea6'
        ],
        [
            'url' => 'https://thelordofthepanels.com/api/v2',
            'key' => '0bc1295f3100a0385ee8ea4bf9a7edd0'
        ],
        [
            'url' => 'https://smmstone.com/api/v2',
            'key' => '54a424b603072c613d6de5996e6faf34'
        ]
    ];

    $cancel_success = false;
    $cancel_id = null;

    foreach ($api_sites as $site) {
        $cancel_url = $site['url'] . "?key=" . $site['key'] . "&action=cancel&order=" . $order_id;
        $response = @file_get_contents($cancel_url);
        $result = json_decode($response, true);

        if (isset($result['cancel']) && $result['cancel'] == 1) {
            $cancel_success = true;
            $cancel_id = $order_id;
            break; // أول موقع يرجّع cancel = 1 نوقف عنده
        }
    }

    if ($cancel_success) {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅︙تم *إرسال* طلب *إلغاء* العملية `$cancel_id` بنجاح.\n\n" .
                      "⏰︙تستغرق العملية من 0 إلى 24 ساعة حسب حالة الطلب.",
            'parse_mode' => 'Markdown'
        ]);
    } else {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "عذراً ، الخدمة لاتدعم الإلغاء بالوقت الحالي.",
            'parse_mode' => 'Markdown'
        ]);
    }
}
if (strpos($data, 'checker|') === 0) {
    $exdata = explode('|', $data);
    $refill_id = $exdata[1];

    $api_sites = [
        [
            'url' => 'https://tigerspeed.store/api/v2',
            'key' => 'egiiCR7gzxiHJqIy5utOrhvDdyPy32sAvpydbUJk3SzpwTyalAE0OL4YdTP3'
        ],
        [
            'url' => 'https://bulkmedya.org/api/v2',
            'key' => 'ecbf5cec79658204f546f4d286438ea6'
        ],
        [
            'url' => 'https://thelordofthepanels.com/api/v2',
            'key' => '0bc1295f3100a0385ee8ea4bf9a7edd0'
        ],
        [
            'url' => 'https://smmstone.com/api/v2',
            'key' => '54a424b603072c613d6de5996e6faf34'
        ]
    ];

    $status_found = false;
    foreach ($api_sites as $site) {
        $status_url = $site['url'] . "?key=" . $site['key'] . "&action=refill_status&refill=" . urlencode($refill_id);
        $response = @file_get_contents($status_url);
        $result = json_decode($response, true);

        if (isset($result['status'])) {
            $status = strtolower(trim($result['status']));
            $msg = "ℹ️ *حالة التعويض*";

            if ($status === 'completed') {
                $msg .= ": *مكتمل ✅*.";
            } elseif ($status === 'rejected') {
                $msg .= ": تم *رفض التعويض 📌*.";
            } elseif ($status === 'pending') {
                $msg .= ": *قيد الانتظار ⏳*.";
            } elseif ($status === 'processing' || $status === 'in progress') {
                $msg .= ": *قيد المعالجة 🔁*.";
            } else {
                $msg .= "⚠️ حالة غير معروفة: " . $result['status'];
            }

            $status_found = true;

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $message_id
            ]);
            break;
        }
    }

    if (!$status_found) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⚠️ لم يتم العثور على حالة التعويض، حاول لاحقاً.",
            'reply_to_message_id' => $message_id
        ]);
    }
}
if ($data == "search_service_id") {
    $waiting = json_decode(file_get_contents("data/service_waiting.json"), true);
    $waiting[$chat_id] = true;
    file_put_contents("data/service_waiting.json", json_encode($waiting));

    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $message_id2,
        'text' => "*⚡️︙يمكنك البحث عن خدمة معينة عن طريقة كتابة رقمها فقط ..*
        
مثال : *219*
",
'parse_mode' => "Markdown",
            'reply_markup' => json_encode(['inline_keyboard' => $back2])
    ]);
}

$waiting = json_decode(file_get_contents("data/service_waiting.json"), true);

if (isset($waiting[$chat_id]) && $waiting[$chat_id] === true && is_numeric($text)) {
    unset($waiting[$chat_id]);
    file_put_contents("data/service_waiting.json", json_encode($waiting));

    include('sql_class.php');

    // استعلام الخدمة
    $service = $sql->sql_select("serv", "service_id", $text);

    if ($service) {
        $name = $service['name'];
        $num = $service['num'];
        $code = $service['codeserv'];
        $api = $service['api'];
        $prec = $service['precent'];

// استعلام القسم والتطبيق من جدول divi
$serv_aymn = $sql->sql_select('divi', 'code', $code);

// تأكد من أن نتيجة الاستعلام صحيحة قبل استخدامها
if ($serv_aymn && isset($serv_aymn['codedivi']) && isset($serv_aymn['name'])) {
    $name_aymn = $serv_aymn['codedivi']; // التطبيق
    $AymnTop = $serv_aymn['name'];       // القسم
} else {
    $name_aymn = "غير معروف";
    $AymnTop = "غير معروف";
}

        $user = $sql->sql_select('users', 'user', $chat_id);
        $info = get_coin_info($user['mycoin']);
        $coin_rate = $info[0];
        $coin_name = $info[1];

        $site = get_serv($api, $num);
        $base = $site['rate'];
        $price = round(((($base / 100) * $prec) + $base) * $coin_rate, 4);

        $msg = "✅︙*تم العثور على الخدمة بنجاح!*

🆔︙رقم الخدمة : `$text`
🛒︙الخدمة : *$name*
💸︙السعر : *$price $coin_name*

*🚀︙اضغط الزر لفتح الخدمة.*";

        $btn = [
            [['text' => "🛒 ⪼ فتح الخدمة رقم : ($text)", 'callback_data' => "selcetserv|$num|$code"]],
        ];

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $msg,
            'parse_mode' => "Markdown",
            'reply_markup' => json_encode(['inline_keyboard' => $btn])
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌︙لم يتم العثور على خدمة بهذا الرقم.",
        ]);
    }
}
#══════════════════════════#
#               الملف كتابة: Romam             
#                  By: @S5BB5                  
#═════════════════════════#
# 🕚 الوقت: الثلاثاء - الساعة 2:26 ص
#═════════════════════════#
const BINANCE_API_KEY = "SQ5GbiruogJi4RuRhkqKinFH3YWBwScfe38HJ5pXuc4rTAQELamrzyl7qw0d73Za";  // API BINANCE
const BINANCE_SECRET_KEY = "we0xu0kXrWJPzpzQtxRVO076CoEgGAcCyEwBNswDzWSwtDafCdEzbqonlHG7RuUn"; // المفتاح السري BINANCE
const BINANCE_USER_ID = "833208397";  // ID BINANCE
const ADMIN_CHANNEL = -1002792624320;  // ID قناة 
const OWNER_ID = 6506780205; // عيّن معرف صاحب البوت هنا

function calculatePoints($amount) {
    return $amount;
}

function isTransactionUsed($transaction_id) {
    $used_file = "data/used_transactions.json";
    if (!file_exists($used_file)) return false;
    $used_transactions = json_decode(file_get_contents($used_file), true);
    return in_array($transaction_id, $used_transactions ?: []);
}

function markTransactionUsed($transaction_id) {
    if (!is_dir('data')) mkdir('data', 0755, true);
    $used_file = "data/used_transactions.json";
    $used_transactions = file_exists($used_file) ? json_decode(file_get_contents($used_file), true) : [];
    if (!is_array($used_transactions)) $used_transactions = [];
    $used_transactions[] = $transaction_id;
    file_put_contents($used_file, json_encode($used_transactions, JSON_PRETTY_PRINT));
}

function getBinanceTransactionTime($transaction) {
    $time_fields = ['createTime', 'time', 'transactionTime', 'orderTime', 'updateTime'];
    foreach ($time_fields as $field) {
        if (isset($transaction[$field])) {
            $time = $transaction[$field];
            if (strlen((string)$time) === 13) {
                return (int)($time / 1000);
            }
            return (int)$time;
        }
    }
    return time();
}

function verifyBinanceTransaction($transaction_id, $expected_amount) {
    $apis_to_try = [
        'c2c_orders' => ['url' => '/sapi/v1/c2c/orderMatch/listUserOrderHistory', 'method' => 'GET', 'params' => ['rows' => 100]],
        'fiat_orders' => ['url' => '/sapi/v1/fiat/orders', 'method' => 'GET', 'params' => ['transactionType' => 0]],
        'pay_transactions' => ['url' => '/sapi/v1/pay/transactions', 'method' => 'GET', 'params' => ['limit' => 100]],
    ];
    
    foreach ($apis_to_try as $api_name => $api_config) {
        $result = callBinanceAPI($api_config['url'], $api_config['method'], $api_config['params'], $transaction_id, $expected_amount);
        if ($result['verified']) {
            $transaction_time = getBinanceTransactionTime($result['transaction']);
            $current_time = time();
            if (($current_time - $transaction_time) > 900) {
                return ['verified' => false, 'reason' => 'transaction_too_old'];
            }
            $result['api_used'] = $api_name;
            return $result;
        }
    }
    return ['verified' => false, 'reason' => 'not_found_in_any_api'];
}

function callBinanceAPI($endpoint, $method, $params, $transaction_id, $expected_amount) {
    $base_url = 'https://api.binance.com';
    $timestamp = time() * 1000;
    $params['timestamp'] = $timestamp;
    $params['recvWindow'] = 60000; 
    ksort($params);
    $query_string = http_build_query($params);
    $signature = hash_hmac('sha256', $query_string, BINANCE_SECRET_KEY);

    $url = $base_url . $endpoint;
    if ($method === 'GET') {
        $url .= '?' . $query_string . '&signature=' . $signature;
    } else {
        $params['signature'] = $signature; 
    }

    $headers = [
        'X-MBX-APIKEY: ' . BINANCE_API_KEY,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); 
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if (!empty($curl_error)) {
        return ['verified' => false, 'reason' => 'curl_error', 'error' => $curl_error];
    }
    if ($http_code !== 200) {
        return ['verified' => false, 'reason' => 'api_error', 'http_code' => $http_code, 'response' => $response];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['verified' => false, 'reason' => 'json_error', 'raw_response' => $response];
    }
    return searchForTransaction($data, $transaction_id, $expected_amount, $endpoint);
}

function searchForTransaction($data, $transaction_id, $expected_amount, $endpoint) {
    $data_array = [];
    if (isset($data['data']) && is_array($data['data'])) {
        $data_array = $data['data'];
    } elseif (is_array($data)) {
        $data_array = $data;
    } else {
        return ['verified' => false, 'reason' => 'invalid_data_structure'];
    }
    
    foreach ($data_array as $transaction) {
        if (!is_array($transaction)) continue;
        
        $id_fields = ['orderId', 'id', 'orderNumber', 'tradeId', 'transactionId', 'orderNum', 'ref', 'reference', 'payId', 'merchantTradeNo', 'prepayId', 'cashTag', 'orderNo', 'bizId', 'bizIdStr', 'clientOrderId', 'origClientOrderId'];
        
        $found_id = false;
        foreach ($id_fields as $field) {
            if (isset($transaction[$field]) && (string)$transaction[$field] === (string)$transaction_id) {
                $found_id = true;
                break;
            }
        }
        
        if (!$found_id && isset($transaction['orderInfo'])) {
            foreach ($id_fields as $field) {
                if (isset($transaction['orderInfo'][$field]) && (string)$transaction['orderInfo'][$field] === (string)$transaction_id) {
                    $found_id = true;
                    break;
                }
            }
        }

        if ($found_id) {
            $amount_fields = ['amount', 'totalPrice', 'price', 'totalAmount', 'sourceAmount', 'obtainAmount', 'qty', 'origQty', 'executedQty', 'cummulativeQuoteQty'];
            $transaction_amount = null;
            
            foreach ($amount_fields as $amount_field) {
                if (isset($transaction[$amount_field])) {
                    $transaction_amount = floatval($transaction[$amount_field]);
                    break;
                }
                if (isset($transaction['orderInfo'][$amount_field])) {
                    $transaction_amount = floatval($transaction['orderInfo'][$amount_field]);
                    break;
                }
            }

            if ($transaction_amount !== null) {
                $tolerance = max(0.01, $expected_amount * 0.05);
                if (abs($transaction_amount - $expected_amount) <= $tolerance) {
                    return [
                        'verified' => true,
                        'transaction' => $transaction,
                        'matched_amount' => $transaction_amount,
                        'expected_amount' => $expected_amount,
                        'endpoint' => $endpoint
                    ];
                }
            } else {
                return [
                    'verified' => true,
                    'transaction' => $transaction,
                    'matched_amount' => 'unknown',
                    'expected_amount' => $expected_amount,
                    'endpoint' => $endpoint,
                    'note' => 'verified_by_id_only'
                ];
            }
        }
    }
    return ['verified' => false, 'reason' => 'transaction_not_found'];
}
include ('d3m.php');