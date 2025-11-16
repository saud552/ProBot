<?php 
$lang = $langs ?? [];
$txt = array(
"ar" => array(
"1" => "✅ - تم إعادة شحن حسابك بـ مبلغ __point__",
"2" => "تم سحب __point__  من حسابك",
"3" => "تم حظرك من استخدام البوت",
"4"=> "تم الغاء حظرك من استخدام البوت",
),
"en" => array(
    "1" => "✅ - Your account has been recharged with __point__",
    "2" => "__point__ has been deducted from your account",
    "3" => "You have been banned from using the bot",
    "4" => "You have been unbanned from using the bot",
),
"ru" => array(
    "1" => "✅ - Ваш счет был пополнен на сумму __point__",
    "2" => "__point__ было списано с вашего счета",
    "3" => "Вы были забанены от использования бота",
    "4" => "Вы были разблокированы для использования бота",
),
"fa" => array(
    "1" => "✅ - حساب شما با مبلغ __point__ شارژ شد",
    "2" => "__point__ از حساب شما کسر شد",
    "3" => "شما از استفاده از ربات مسدود شده‌اید",
    "4" => "مسدودیت شما از استفاده از ربات لغو شد",
),
"cht" => array(
    "1" => "✅ - 您的帳戶已加值 __point__",
    "2" => "__point__ 已從您的帳戶中扣除",
    "3" => "您已被禁止使用機器人",
    "4" => "您已被解除禁止使用機器人",
),
"chb" => array(
    "1" => "✅ - 您的账户已充值 __point__",
    "2" => "__point__ 已从您的账户中扣除",
    "3" => "您已被禁止使用机器人",
    "4" => "您已被解除禁止使用机器人",
),
"tr" => array(
    "1" => "✅ - Hesabınıza __point__ yüklendi",
    "2" => "__point__ bakiyenizden düşüldü",
    "3" => "Botu kullanmanız engellendi",
    "4" => "Botu kullanma engeliniz kaldırıldı",
)
);
if ($text == "/start" || $text == "/admin" || $data == "back") {
	$btn = 
	mkBtn(
		array(
			array(
				"اضافة رصيد" => "addPoint",
				"سحب رصيد" => "takePoint"
			),
			array(
				"حظر عضو" => "ban",
				"الغاء  الحظر" => "unban"
			),
			array(
				"اضافة وكيل" => "addWk",
				"حذف وكيل" => "remWk"
			),
			array(
				"الوكلاء" => "wk",
			),
			array(
				"اضافة دولة" => "addContry",
				"حذف دولة" => "remContry"
			),
			array(
				"الاشتراك الاجباري" => "subSettings",
				"استيراد الدول" => "importCountries"
			),
			array(
				"وضع الصيانة" => "maintenanceSettings",
				"الاذاعة" => "broadcast"
			),
			array(
				"اعدادات التسعير" => "pricingSettings",
				"الاحصائيات" => "stats"
			)				
		)
	
	);
	$info[$id]['action']="";
	saveInfo();
	$tx = "اهلا وسهلا بك عزيزي الادمن\n رصيدك في موقع الارقام $balance\n\nاوامر الادمن";
	if ($text)
	send($tx,$btn);
	else
	edit($tx,$btn);
} else if ($data == "addPoint") {
	$tx = "قم بارسال ايدي العضو";
	$info[$id]['action']="addPointId";
	saveInfo();
	edit($tx,$back);
} else if ($text && ($info[$id]['action'] == "addPointId") ){
	echo "-@Ba_ageel-";
	if(!isset ($points[$text])) {
		//user not exist 
		$tx = "لا يوجد مستخدم بهذا الايدي";
		send($tx,$back);
	} else {
		$info[$id]['idPoint']  = $text;
		$info[$id]['action']="addPoint";
		saveInfo();
		$tx = "قم بارسال الرصيد الذي تريد اضافته";
		send($tx,$back);
	}
} else if ($text && $info[$id]['action'] == "addPoint") {
	if( is_numeric($text) && $text > 0 ) {
		$points[$info[$id]['idPoint']] += ($text);
		savePoint();
		$tx = "تم التحويل بنجاح";
		send($tx,$back);
		$tx=str_replace("__point__",$text,$txt[$lang[$info[$id]['idPoint']]][1]);
		send($tx,null,$info[$id]['idPoint']);
		$info[$id]['idPoint']  = "";
		$info[$id]['action']="";
		saveInfo();
	} else {
		$tx = "يجب ان ترسل رقم اكبر من الصفر";
		send($tx,$back);
	}
}else if ($data == "takePoint") {
	$tx = "قم بارسال ايدي العضو";
	$info[$id]['action']="takePointId";
	saveInfo();
	edit($tx,$back);
} else if ($text && ($info[$id]['action'] == "takePointId") ){
	echo "-@Ba_ageel-";
	if(!isset ($points[$text])) {
		//user not exist 
		$tx = "لا يوجد مستخدم بهذا الايدي";
		send($tx,$back);
	} else {
		$info[$id]['idPoint']  = $text;
		$info[$id]['action']="takePoint";
		saveInfo();
		$tx = "قم بارسال الرصيد الذي تريد سحبه من العضو";
		send($tx,$back);
	}
} else if ($text && $info[$id]['action'] == "takePoint") {
	if( is_numeric($text) && $text > 0 ) {
		$points[$info[$id]['idPoint']] -= ($text);
		savePoint();
		$tx = "تم السحب بنجاح";
		send($tx,$back);
		$tx=str_replace("__point__",$text,$txt[$lang[$info[$id]['idPoint']]][2]);
		send($tx,null,$info[$id]['idPoint']);
		$info[$id]['idPoint']  = "";
		$info[$id]['action']="";
		saveInfo();
	} else {
		$tx = "يجب ان ترسل رقم اكبر من الصفر";
		send($tx,$back);
	}
} else if ($data == "ban") {
	$tx = "قم بارسال ايدي العضو";
	$info[$id]['action']="ban";
	saveInfo();
	edit($tx,$back);
} else if ($text && $info[$id]['action'] == "ban") {
	$tx = "تم حظر العضو بنجاح";
	$info[$id]['action']="";
	saveInfo();
	$bans[$text]=$text;
	saveBans();
	send($tx,$back);
	$tx = $txt[$lang[$text]][3];
	send($tx,null,$text);
}else if ($data == "unban") {
	$tx = "قم بارسال ايدي العضو";
	$info[$id]['action']="unban";
	saveInfo();
	edit($tx,$back);
}else if ($text && $info[$id]['action'] == "unban") {
	$tx = "تم الغاء الحظر بنجاح";
	$info[$id]['action']="";
	saveInfo();
	$bans[$text]=null;
	saveBans();
	send($tx,$back);
	$tx = $txt[$lang[$text]][4];
	send($tx,null,$text);
} else if ($data == "addWk") {
	$tx = "قم بارسال الاسم في سطر واليوزر في السطر الثاني";
	$info[$id]['action']="addWk";
	saveInfo();
	edit($tx,$back);
} else if ($text && $info[$id]['action']=="addWk") {
	$extx = explode ("\n",$text);
	if(count ($extx) == 2) {
		$info['bot']['wk'] [] = array(
			"name" => $extx[0],
			"user" => $extx[1],
		);
		$info[$id]['action']="";
		saveInfo ();
		$tx="تمت اضافة الوكيل بنجاح";
		send($tx,$back);
	} else {
		$tx = "قم بارسال الاسم في سطر واليوزر في السطر الثاني";
		send($tx,$back);
	}
} else if ($data == "remWk" ) {
	$btn = array();
	$btn[]= 
		array(
			"الاسم" => "ntn",
			"اليوزر" => "ntn"
		);
	
	foreach ($info['bot']['wk'] as $k => $v ) {
		$d= "remWk-{$k}";
		$btn[] = 
			array(
				"{$v['name']}"??""=> $d,
				"{$v['user']}"??"" => $d,
			);
		
	}
	$btn[]=array (
			"رجوع" => "back"
		);
	$tx ="اختار الوكيل الذي تريد حذفة";
	edit($tx, mkBtn ($btn));
}else if (preg_match("/remWk\-/",$data)) {
	$info['bot']['wk'][explode ("-",$data)[1]]=null;
	unset($info['bot']['wk'][explode ("-",$data)[1]]);
	saveInfo ();
	$tx ="تم الحذف بنجاح";
	edit($tx, mkBtn ($btn));
} else if ($data == "wk") {
	$btn = array();
	$btn[]= 
		array(
			"الاسم" => "ntn",
			"اليوزر" => "ntn"
		);
	
	foreach ($info['bot']['wk'] as $k => $v ) {
		$btn[] = 
			array(
				"{$v['name']}"??""=> "ntn",
				"{$v['user']}"??"" => "ntn",
			);
	}
	$btn[]=array (
			"رجوع" => "back"
		);
	$tx ="جميع الوكلاء";
	edit($tx, mkBtn ($btn));
} else if ($data == "subSettings") {
	$status = ($settings['forced_subscription']['enabled'] ?? true) ? "مفعلة ✅" : "معطلة ❌";
	$channelId = $settings['forced_subscription']['channel_id'] ?? $ch5;
	$channelLink = $settings['forced_subscription']['channel_link'] ?? $ch6;
	$tx = "حالة الاشتراك الإجباري: {$status}\nالقناة الحالية: {$channelId}\nالرابط: {$channelLink}";
	$btn = mkBtn([
		[
			"تفعيل" => "toggleSub#on",
			"تعطيل" => "toggleSub#off"
		],
		[
			"تغيير القناة" => "setSubChannel"
		],
		[
			"رجوع" => "back"
		]
	]);
	edit($tx, $btn);
} else if ($exData[0] == "toggleSub") {
	$settings['forced_subscription']['enabled'] = ($exData[1] ?? 'on') === 'on';
	saveSettings();
	$tx = $settings['forced_subscription']['enabled'] ? "تم تفعيل الاشتراك الإجباري." : "تم تعطيل الاشتراك الإجباري.";
	edit($tx, $back);
} else if ($data == "setSubChannel") {
	$tx = "أرسل ايدي القناة في السطر الأول والرابط في السطر الثاني.";
	$info[$id]['action'] = "setSubChannel";
	saveInfo();
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "setSubChannel") {
	$parts = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $text))));
	if (count($parts) < 2) {
		$parts = preg_split('/\s+/', trim($text));
	}
	if (count($parts) >= 2) {
		$settings['forced_subscription']['channel_id'] = $parts[0];
		$settings['forced_subscription']['channel_link'] = $parts[1];
		saveSettings();
		$info[$id]['action'] = "";
		saveInfo();
		$tx = "تم تحديث قناة الاشتراك الإجباري.";
	} else {
		$tx = "يرجى إرسال ايدي القناة في السطر الأول والرابط في السطر الثاني.";
	}
	edit($tx, $back);
} else if ($data == "importCountries") {
	$get = $api->getCountries();
	if (!is_array($get)) {
		edit("تعذر جلب الدول من المزود.", $back);
	} else {
		$margin = (float)($settings['pricing']['margin_percent'] ?? 0);
		$applied = 0;
		foreach ($get as $code => $basePrice) {
			if (!is_numeric($basePrice)) {
				continue;
			}
			$price = (float)$basePrice;
			if ($margin !== 0.0) {
				$price += $price * ($margin / 100);
			}
			$contries[$code] = round($price, 2);
			$applied++;
		}
		saveContries();
		$tx = "تم استيراد {$applied} دولة وتحديث أسعارها.";
		edit($tx, $back);
	}
} else if ($data == "pricingSettings") {
	$margin = $settings['pricing']['margin_percent'] ?? 0;
	$starPrice = $settings['stars']['usd_per_star'] ?? 0.0;
	$tx = "النسبة الحالية للأرباح: {$margin}%\nسعر النجمة بالدولار: {$starPrice}\nيمكنك تعيين نسبة عامة أو سعر مخصص لدولة معينة.";
	$btn = mkBtn([
		[
			"تعديل النسبة" => "setMargin",
			"سعر مخصص" => "setCustomPrice"
		],
		[
			"تعديل سعر النجوم" => "setStarPrice"
		],
		[
			"رجوع" => "back"
		]
	]);
	edit($tx, $btn);
} else if ($data == "setMargin") {
	$info[$id]['action'] = "setMargin";
	saveInfo();
	$tx = "أرسل النسبة المئوية للأرباح (مثال 15 أو 12.5).";
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "setMargin") {
	if (is_numeric($text)) {
		$settings['pricing']['margin_percent'] = (float)$text;
		saveSettings();
		$info[$id]['action'] = "";
		saveInfo();
		$tx = "تم تحديث النسبة المئوية للأرباح.";
	} else {
		$tx = "يرجى إرسال قيمة رقمية فقط.";
	}
	edit($tx, $back);
} else if ($data == "setCustomPrice") {
	$info[$id]['action'] = "customPrice";
	saveInfo();
	$tx = "أرسل كود الدولة والسعر مثال: US 1.75";
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "customPrice") {
	$parts = preg_split('/\s+/', trim($text));
	if (count($parts) === 2 && is_numeric($parts[1])) {
		$code = strtoupper($parts[0]);
		$contries[$code] = (float)$parts[1];
		saveContries();
		$info[$id]['action'] = "";
		saveInfo();
		$tx = "تم تحديث سعر الدولة {$code}.";
	} else {
		$tx = "صيغة غير صحيحة، استخدم مثال: US 1.75";
	}
	edit($tx, $back);
} else if ($data == "maintenanceSettings") {
	$mEnabled = $settings['maintenance']['enabled'] ?? false;
	$mMessage = $settings['maintenance']['message'] ?? "البوت في وضع الصيانة حالياً.";
	$status = $mEnabled ? "مفعل ✅" : "معطل ❌";
	$tx = "حالة وضع الصيانة: {$status}\nالرسالة الحالية:\n{$mMessage}";
	$btn = mkBtn([
		[
			"تفعيل" => "toggleMaintenance#on",
			"تعطيل" => "toggleMaintenance#off"
		],
		[
			"تعديل الرسالة" => "setMaintenanceMessage"
		],
		[
			"رجوع" => "back"
		]
	]);
	edit($tx, $btn);
} else if ($exData[0] == "toggleMaintenance") {
	$settings['maintenance']['enabled'] = ($exData[1] ?? 'on') === 'on';
	saveSettings();
	$tx = $settings['maintenance']['enabled'] ? "تم تفعيل وضع الصيانة." : "تم تعطيل وضع الصيانة.";
	edit($tx, $back);
} else if ($data == "setMaintenanceMessage") {
	$info[$id]['action'] = "setMaintenanceMessage";
	saveInfo();
	$tx = "أرسل رسالة الصيانة التي ستظهر للمستخدمين:";
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "setMaintenanceMessage") {
	$settings['maintenance']['message'] = $text;
	saveSettings();
	$info[$id]['action'] = "";
	saveInfo();
	$tx = "تم تحديث رسالة الصيانة.";
	edit($tx, $back);
} else if ($data == "broadcast") {
	$info[$id]['action'] = "broadcast";
	saveInfo();
	$tx = "أرسل نص الرسالة التي تريد إذاعتها لجميع المستخدمين.";
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "broadcast") {
	$audience = array_keys($points ?? []);
	$success = 0;
	foreach ($audience as $uid) {
		if (!$uid) {
			continue;
		}
		send($text, null, $uid);
		$success++;
	}
	$info[$id]['action'] = "";
	saveInfo();
	$tx = "تم إرسال الرسالة إلى {$success} مستخدم.";
	edit($tx, $back);
} else if ($data == "addContry" || $exData[0] == 'next' || $exData[0] == 'before') {
	#Lista:
	$get = $api->getCountries();
	$tx="الدول المتاحة\n";
	if ($data == "addContry" ) {
		$start = 0;
	} else if ($exData[0] == 'next') {
		$start= $exData[1];
		if ($start > count ($get)) {
			bot('answerCallbackQuery',[
				'callback_query_id'=>$update->callback_query->id,
				'show_alert'=>true,
				'text' => "لا توجد قائمة تاليه"
			]);
			exit;
		}
	} else if ( $exData[0] == 'before') {
		$start= $exData[1];
		if($start >= 30) {$start -=30;}
		else if ($start > 0) { $start = 0;}
		else  {
			bot('answerCallbackQuery',[
				'callback_query_id'=>$update->callback_query->id,
				'show_alert'=>true,
				'text' => "لا توجد قائمة سابقة"
			]);
			exit;
		}
	}	
	$end = $start + 30;
	$btn =array();
	$bt=array();
	$count=-1;
	$a=1;
	$btn[]=[['text' => "الدولة | الكلفة",'callback_data' => "test" ],['text' => "الدولة | الكلفة ",'callback_data' => "change#$z"]];
	foreach ($get as $k => $p) {
		$count++;
		if($count < $start) continue;
		else if ($count >= $end) break;
		$z = isset($contries[$k])? "✅" : "❌";
		//$btn[]=[['text' => "{$names[$k]} | $p",'callback_data' => "change#$k#$data" ],['text' => "$z",'callback_data' => "change#$k#$data"]];
		if($a%2==0) {
			$bt[]=['text' => "{$names[$k]} | $p",'callback_data' => "add#$k#$data" ];
			$btn[]=$bt;
			$bt=[];
		} else {
			$bt[]=['text' => "{$names[$k]} | $p",'callback_data' => "add#$k#$data" ];
		}
		$a++;
	}
	if(count($bt)>0) $btn[]=$bt;
	$btn[]=array(
		['text' => "السابق ⏮️",'callback_data' => "before#{$start}" ],
		['text' => "⏭️التالي ",'callback_data' => "next#{$end}" ],
	);
	$btn[]=array(
		['text' =>" رجوع 🔙",'callback_data' => "back" ],
	);
	
	edit($tx,$btn);
} /*else if ($exData[0] == "change") {
	if ( !isset($contries[$exData[1]])) {
		$contries[$exData[1]]=$exData[1];
	} else {
		unset($contries[$exData[1]]);
	}
	saveContries ();
	unset($exData[1]);
	unset($exData[0]);
	$data = implode ("#",$exData);
	$exData=explode ("#",$data);
	goto Lista;
}*/ else if ($data == "stats") {
	$a=$stats['all']['trybuy']??0;
	$b= $stats['all']['buy']??0;
	$tx = "عدد عمليات الشراء $a\nعدد العمليات الناجحة$b";
	edit ($tx,$back);
} else if ($exData[0] == "add") {
	$info[$id]['action']="addContry";
	$info[$id]['contry']=$exData[1];
	saveInfo();
	$tx = "قم بارسال سعر البيع";
	$btn = mkBtn([
		[
			"رجوع🔙" => $exData[2]
		]
	]);
	edit($tx,$btn);
} else if ($text && $info[$id]['action']=="addContry") {
	if ( is_numeric($text) && $text > 0 ) {
		$tx = "تمت اضافة الدولة بنجاح";
		$contries[$info[$id]['contry']]=$text;
		$info[$id]['action']="";
		$info[$id]['contry']="";
		saveInfo();
		saveContries ();
	} else {
		$tx="قم بارسال قيمة رقمية اكبر من الصفر";
	}
	send($tx,$back);
} else if ($data == "remContry" || $exData[0] == 'NEXT' || $exData[0] == 'BEFORE') {
	$tx="الدول المتاحة\n";
	if ($data == "remContry") {
		$start = 0;
	} else if ($exData[0] == 'NEXT') {
		$start= $exData[1];
		if ($start > count ($contries)) {
			bot('answerCallbackQuery',[
				'callback_query_id'=>$update->callback_query->id,
				'show_alert'=>true,
				'text' => "لا توجد قائمة تاليه"
			]);
			exit;
		}
	} else if ( $exData[0] == 'BEFORE') {
		$start= $exData[1];
		if($start >= 30) {$start -=30;}
		else if ($start > 0) { $start = 0;}
		else  {
			bot('answerCallbackQuery',[
				'callback_query_id'=>$update->callback_query->id,
				'show_alert'=>true,
				'text' => "لا توجد قائمة سابقة"
			]);
			exit;
		}
	}	
	$end = $start + 30;
	$btn =array();
	
	$tx="
	قم باختيار الدولة التي تريد خذفها
	";
	$bt=array();
	$count=-1;
	$a=0;
	foreach ($contries as $k => $p) {
		$count++;
		if($count < $start) continue;
		else if ($count >= $end) break;	
		/*$p = $prices[$k];
		$p = $p+($p*$revenue/100);*/
		if($a%2==0) {
			$btn[]=$bt;
			$bt=[];
			$bt[]=['text' => "{$names[$k]} | $p",'callback_data' => "remove#$k"];
		} else {
			$bt[]=['text' => "{$names[$k]} | $p",'callback_data' => "remove#$k" ];
		}
		$a++;
		//$tx .= "$k | {$names[$k]} | $p \n ";	
	}
	if(count($bt)>0) $btn[]=$bt;
	$btn[]=array(
		['text' => "السابق ⏮️",'callback_data' => "BEFORE#{$start}" ],
		['text' => "⏭️التالي ",'callback_data' => "NEXT#{$end}" ],
	);
	$btn[]=array(
	['text' => "رجوع 🔙",'callback_data' => "back" ],
	);
	edit($tx,$btn);
} else if ($exData[0] == "remove") {
	//send($exData[1]);
	$contries[$exData[1]]=null;
	unset($contries[$exData[1]]);
	saveContries ();
	//send(json_encode($contries));
	$tx="تم الحذف بنجاح";
	edit($tx,$back);
} else if ($data == "setStarPrice") {
	$info[$id]['action'] = "setStarPrice";
	saveInfo();
	$tx = "أرسل سعر نجمة واحدة بالدولار (مثال 0.011).";
	edit($tx, $back);
} else if ($text && ($info[$id]['action'] ?? '') === "setStarPrice") {
	if (is_numeric($text) && $text > 0) {
		$settings['stars']['usd_per_star'] = (float)$text;
		saveSettings();
		$info[$id]['action'] = "";
		saveInfo();
		$tx = "تم تحديث سعر النجوم.";
	} else {
		$tx = "يرجى إرسال قيمة رقمية صحيحة.";
	}
	edit($tx, $back);
}
