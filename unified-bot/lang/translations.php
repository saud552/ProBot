<?php

return [
    'ar' => [
        'label' => 'العربية 🇸🇦',
        'back' => 'رجوع',
        'main_menu' => 'القائمة الرئيسية',
        'change_language' => 'تغيير اللغة',
        'strings' => [
            'main_numbers_button' => 'قسم شراء الأرقام',
            'main_smm_button' => 'قسم الرشق',
            'numbers_usd_button' => 'شراء بالدولار',
            'numbers_stars_button' => 'شراء بالنجوم',
            'smm_usd_button' => 'الرشق بالدولار',
            'smm_stars_button' => 'الرشق بالنجوم',
            'smm_select_category' => 'يرجى اختيار قسم الرشق.',
            'smm_select_service' => 'اختر الخدمة المناسبة.',
            'smm_service_details' => 'تفاصيل الخدمة',
            'smm_price_info' => 'السعر لكل 1k: __rate__$ | الحد الأدنى: __min__ | الحد الأقصى: __max__',
            'smm_continue' => 'متابعة',
            'smm_enter_link' => 'أرسل الرابط الذي تريد رشقُه أو أرسل /cancel لإلغاء العملية.',
            'smm_enter_quantity' => 'أرسل الكمية المطلوبة (بين __min__ و __max__).',
            'smm_quantity_invalid' => 'الكمية غير صالحة أو خارج الحدود.',
            'smm_link_saved' => 'تم حفظ الرابط. الآن أرسل الكمية.',
            'smm_order_summary' => "الخدمة: __service__\nالكمية: __quantity__\nالسعر: __price__$",
            'smm_confirm_button' => 'تأكيد الطلب',
            'smm_cancel_button' => 'إلغاء',
            'smm_order_success' => 'تم إنشاء طلب الرشق بنجاح.',
            'smm_order_failed' => 'تعذر إكمال طلب الرشق، حاول لاحقاً.',
            'smm_input_cancelled' => 'تم إلغاء العملية.',
            'verify_button' => 'اضغط للتحقق',
            'verify_text' => "🖐︙مرحبا بك\n\n- يجب الإشتراك بقناة البوت الرسمية لإستخدام البوت \n- رابط القناة: {{channel_link}}\n\n🙋‍♂️ ⁞ إضغط على الزر بالأسفل للتحقق",
            'subscribe_button' => 'اشترك في القناة',
            'subscription_verified' => 'تم التحقق من اشتراكك بنجاح.',
            'subscription_not_verified' => 'لم يتم العثور على اشتراكك بعد.',
            'banned_message' => 'لا يمكنك استخدام البوت لانك محظور',
            'invite_reward' => ' تم دخول عضو عن طريق رابطك وقد ربحت  {{invite_point}}',
            'menu_purchase' => '٭ شـراء حسابات Telegram ٭',
            'menu_purchase_usd' => 'شراء حسابات بالدولار',
            'menu_purchase_stars' => 'شراء حسابات بالنجوم',
            'menu_recharge' => '٭ اشحن رصيدك ٭',
            'menu_support' => '٭ فريق ألدعم ٭',
            'menu_agents' => '٭ الوكـلاء ٭',
            'menu_bot_activations' => '٭ تفعيلات البوت ٭',
            'menu_free_balance' => '٭ ربح رصيد مجــاني ٭',
            'welcome' => "- السَلام عليكُم مرحبا بك في بوت حسابات التيليجرام .\n\n- تستطيع ان تحصل بسهولة على رقم افتراضي مع حسابات التليجرام الجاهزة ، بسرعة خيالية .. تلقائي بالكامل ، بأقل سعر ممكن ، متوفر دعم على مدار 24 ساعة .\n\n- ايدي حسابك : <code>{{user_id}}</code>\n- رصيدك : {{balance}}$",
            'charge_info' => "لشحن حسابك قم بالتواصل مع الادارة {{charge_link}}\nاو بالتواصل مع احد الوكلاء في البوت",
            'support_info' => "في حالة واجهت اي مشكلة قم بالتواصل معنا عبر اليوزر التالي \n{{support_link}}",
            'no_agents' => 'لا يوجد وكلاء في البوت في الوقت الحالي',
            'invite_info' => "قم بدعوة اصدقائك على الرابط التالي  واكسب {{invite_point}} على كل شخص ينضم  بواسطتك\n\n\n<code>{{ref_link}}</code>",
            'available_countries' => "الدول المتاحة\n",
            'no_next_page' => 'لا توجد قائمة تالية',
            'no_previous_page' => 'لا توجد قائمة سابقة',
            'country_selection' => "يرجى إختيار الدولة التي تريد شراء حساب منها•\n\nجميع الدول في الأسفل تمتلك حسابات لتفعيل التيليجرام ، وتستقبل وصول الكود على أي نسخة ، إضغط على الدولة للشراء •",
            'button_previous' => 'السابق',
            'button_next' => 'التالي',
            'confirm_purchase' => 'تاكيد الشراء',
            'disclaimer' => "شروط إخلاء المسؤوليه\n\nعزيزي العميل بعد موافقتك والظغط على زر الشراء يتم خصم قيمة الرقم ويعتبر الرقم هو ملك لك ولن يتم استرداد الأموال.\n\nهل تريد شراء رقم",
            'stars_disabled' => 'خيار الدفع بالنجوم غير متاح حالياً.',
            'stars_purchase_disclaimer' => "شروط إخلاء المسؤولية\n\nالدولة: __c__\nالسعر بالدولار: __p__$\nالسعر بالنجوم: __s__⭐️\n\nبعد الدفع سيتم خصم القيمة ولا يمكن استرجاعها.\n\nهل تريد شراء هذا الرقم؟",
            'no_numbers' => 'لا توجد ارقام لهذه الدولة في الوقت الحالي✖️',
            'purchase_success' => "✅- تم شراء حساب تيليجرام بنجاح -✅\n\n🌎 - الدولة: __c__\n☎️ - الرقم: <code>__num__</code>\n💰- السعر :  __p__$\n💬 - الكود : ××××\n🔑 - كلمة المرور: ××××××\n\n- يرجى تسجيل الرقم في تيليجرام ، بعد التسجيل ، إضغط على ( طلب الكود  ) للحصول على كود التفعيل .",
            'request_code' => 'طلب الكود',
            'insufficient_balance' => 'رصيدك غير كافي',
            'code_pending' => 'لم يصل الكود بعد',
            'code_received' => "💬- تم وصول الكود في الاسفل •\n\n🌎 - الدولة: __c__\n☎️ - الرقم: <code>__num__</code>\n💰- السعر :  __p__$\n💬 - الكود : <code>__code__</code>\n🔑 - كلمة المرور: <code>__pass__</code>\n\n✅- قم بتفعيل الرقم في تطبيق تيليجرام ✳️",
            'purchase_in_progress' => '⏳ توجد عملية شراء قيد التنفيذ، يرجى الانتظار.',
            'maintenance_message' => "⚙️ البوت في وضع الصيانة حالياً وسيعود قريباً.\nشكراً لتفهمك.",
            'stars_invoice_title' => 'شراء رقم تيليجرام',
            'stars_invoice_description' => 'شراء رقم __c__ بقيمة __p__$ (حوالي __s__⭐️).',
            'stars_invoice_message' => "السعر: __p__$ ≈ __s__⭐️\nاضغط على الزر للدفع عبر نجوم تيليجرام ثم اضغط زر التحقق في البوت.",
            'stars_invoice_button' => 'الدفع عبر نجوم تيليجرام',
            'stars_price_perk' => 'تقريباً نجوم لكل 1000: __s__⭐️',
            'stars_price_total' => 'النجوم التقريبية: __s__⭐️',
            'purchase_failed' => 'تعذر إتمام عملية الشراء، يرجى التواصل مع الدعم.',
            'support_intro' => 'هل تواجه مشكلة؟ افتح تذكرة وسنقوم بمساعدتك في أسرع وقت.',
            'support_new_ticket_button' => 'فتح تذكرة',
            'support_my_tickets_button' => 'تذاكري',
            'support_ticket_subject_prompt' => 'أرسل عنواناً موجزاً للتذكرة.',
            'support_ticket_message_prompt' => 'اكتب تفاصيل مشكلتك.',
            'support_ticket_created' => 'تم إنشاء التذكرة، سيتم الرد عليك قريباً.',
            'support_ticket_list_title' => 'تذاكري',
            'support_ticket_list_empty' => 'لا توجد تذاكر حتى الآن.',
            'support_ticket_reply_prompt' => 'أرسل ردك.',
            'support_ticket_closed' => 'تم إغلاق التذكرة. شكراً لك.',
            'support_ticket_reply_button' => 'رد',
            'support_ticket_close_button' => 'إغلاق',
            'support_ticket_header' => 'تذكرة',
            'support_ticket_status_label' => 'الحالة',
            'support_ticket_subject_label' => 'العنوان',
            'support_admin_label' => 'الدعم',
            'support_user_label' => 'أنت',
            'support_waiting_for_admin' => 'تم إرسال ردك، يرجى انتظار رد الدعم.',
            'support_admin_reply_notice' => 'قام الدعم بالرد على تذكرتك.',
            'support_admin_reply_sent' => 'تم إرسال الرد للمستخدم.',
            'support_input_cancelled' => 'تم إلغاء العملية.',
            'referral_title' => 'برنامج الإحالات',
            'referral_link_label' => "رابط الدعوة:\n__link__\nالكود: __code__",
            'referral_stats' => "عدد المدعوين: __invited__\nالمنتظر: __pending__\nالمتوفر: __eligible__$\nالمدفوع: __rewarded__$",
            'referral_withdraw_button' => 'سحب الأرباح',
            'referral_withdraw_success' => 'تم تحويل __amount__$ إلى محفظتك.',
            'referral_no_rewards' => 'لا يوجد رصيد إحالات متاح.',
            'referral_attached' => 'تم ربط الإحالة بنجاح.',
                'button_refresh' => 'تحديث',
                'user_banned' => 'تم تقييد وصولك إلى هذا البوت.',
                'feature_disabled' => 'هذا القسم معطل حالياً.',
                'admin_only' => 'هذا الإجراء متاح للمشرفين فقط.',
                'admin_panel_button' => 'لوحة الإدارة',
                'admin_panel_title' => 'لوحة تحكم الإدارة',
                'admin_section_tickets' => 'التذاكر',
                'admin_section_users' => 'المستخدمون',
                'admin_section_features' => 'الأقسام',
                'admin_section_stars' => 'مدفوعات النجوم',
                'admin_section_forcesub' => 'الاشتراك الإجباري',
                'admin_section_referrals' => 'الإحالات',
                'admin_features_title' => 'التحكم في الأقسام',
                'admin_stars_title' => 'إعدادات الدفع بالنجوم',
                'admin_stars_enabled_label' => 'الحالة',
                'admin_stars_price_label' => 'سعر النجمة بالدولار',
                'admin_stars_toggle_button' => 'تفعيل / تعطيل',
                'admin_stars_set_price_button' => 'تعديل السعر',
                'admin_prompt_star_price' => 'أرسل قيمة الدولار للنجمة الواحدة.',
                'admin_star_price_updated' => 'تم تحديث سعر النجمة.',
                'admin_forcesub_title' => 'إعدادات الاشتراك الإجباري',
                'admin_forcesub_toggle_button' => 'تشغيل / إيقاف',
                'admin_forcesub_set_link_button' => 'تعديل الرابط',
                'admin_forcesub_set_channel_button' => 'تعديل القناة',
                'admin_forcesub_link_prompt' => 'أرسل رابط الدعوة أو الرابط الاحتياطي.',
                'admin_forcesub_link_updated' => 'تم تحديث الرابط.',
                'admin_forcesub_channel_prompt' => 'أرسل المعرف والرابط بصيغة ID|https://t.me/...',
                'admin_forcesub_channel_updated' => 'تم تحديث القناة.',
                'admin_referrals_hint' => 'استخدم ‎/referrals <معرف تيليجرام> لمراجعة الشريك.',
                'admin_referrals_toggle_button' => 'تفعيل / تعطيل الإحالات',
                'admin_users_title' => "إدارة المستخدمين\nاستخدم ‎/user <معرف تيليجرام>\nاستخدم أوامر ‎/ban و ‎/unban",
                'admin_user_id_prompt' => 'أرسل معرف تيليجرام صالحاً.',
                'admin_user_not_found' => 'لم يتم العثور على المستخدم.',
                'admin_user_updated' => 'تم تحديث بيانات المستخدم.',
                'admin_user_status_banned' => 'محظور',
                'admin_user_status_active' => 'نشط',
                'admin_user_ban_button' => 'حظر المستخدم',
                'admin_user_unban_button' => 'إلغاء حظر المستخدم',
                'referral_disabled' => 'برنامج الإحالات غير متاح حالياً.',
        ],
    ],
    'en' => [
        'label' => 'English 🇺🇲',
        'back' => 'Back',
        'main_menu' => 'Main Menu',
        'change_language' => 'Change Language',
        'strings' => [
            'main_numbers_button' => 'Numbers Section',
            'main_smm_button' => 'Boosting Section',
            'numbers_usd_button' => 'Buy with USD',
            'numbers_stars_button' => 'Buy with Stars',
            'smm_usd_button' => 'Boost with USD',
            'smm_stars_button' => 'Boost with Stars',
            'smm_select_category' => 'Please choose a boosting category.',
            'smm_select_service' => 'Select the service you need.',
            'smm_service_details' => 'Service details',
            'smm_price_info' => 'Price/1k: __rate__$ | Min: __min__ | Max: __max__',
            'smm_continue' => 'Continue',
            'smm_enter_link' => 'Send the target link (or /cancel to stop).',
            'smm_enter_quantity' => 'Send the desired quantity (between __min__ and __max__).',
            'smm_quantity_invalid' => 'Invalid quantity or out of bounds.',
            'smm_link_saved' => 'Link saved, now send the quantity.',
            'smm_order_summary' => "Service: __service__\nQuantity: __quantity__\nPrice: __price__$",
            'smm_confirm_button' => 'Confirm',
            'smm_cancel_button' => 'Cancel',
            'smm_order_success' => 'Boost order submitted successfully.',
            'smm_order_failed' => 'Could not submit the boost order.',
            'smm_input_cancelled' => 'Operation cancelled.',
            'verify_button' => 'Click to verify',
            'verify_text' => "🖐︙Welcome\n\n- You must subscribe to the official bot channel to use the bot\n- Channel link: {{channel_link}}\n\n🙋‍♂️ ⁞ Click the button below to verify",
            'subscribe_button' => 'Join the channel',
            'subscription_verified' => 'Subscription verified successfully.',
            'subscription_not_verified' => 'Subscription not detected yet.',
            'banned_message' => 'You cannot use the bot because you are banned.',
            'invite_reward' => 'A member has joined through your link, and you have earned {{invite_point}}.',
            'menu_purchase' => '* Purchase Telegram Accounts *',
            'menu_purchase_usd' => 'Buy Accounts (USD)',
            'menu_purchase_stars' => 'Buy Accounts (Stars)',
            'menu_recharge' => '* Recharge Your Balance *',
            'menu_support' => '* Support Team *',
            'menu_agents' => '* Agents *',
            'menu_bot_activations' => '* Bot Activations *',
            'menu_free_balance' => '* Win Free Balance *',
            'welcome' => "- Hello, welcome to the Telegram accounts bot.\n\n- You can easily get a virtual number with ready-made Telegram accounts, at an incredible speed.. fully automated, at the lowest possible price, with 24/7 support.\n\n- Your account ID: <code>{{user_id}}</code>\n- Your balance: {{balance}}$",
            'charge_info' => "To recharge your account, please contact the administration {{charge_link}}\nor contact one of the agents in the bot",
            'support_info' => "If you encounter any issues, please contact us via the following username \n{{support_link}}",
            'no_agents' => 'There are no agents in the bot at this time',
            'invite_info' => "Invite your friends using the following link and earn {{invite_point}} for every person who joins through you\n\n<code>{{ref_link}}</code>",
            'available_countries' => "Available countries\n",
            'no_next_page' => 'No next list available',
            'no_previous_page' => 'No previous list available',
            'country_selection' => "Please choose the country you want to buy an account from•\n\nAll countries listed below have accounts for activating Telegram, and receive code access on any version, click the country to purchase •",
            'button_previous' => 'Previous',
            'button_next' => 'Next',
            'confirm_purchase' => 'Confirm Purchase',
            'disclaimer' => "Disclaimer Conditions\n\nDear customer, after you agree and click the purchase button, the value of the number will be deducted, and the number will be yours and will not be refunded.\n\nDo you want to purchase a number",
            'stars_disabled' => 'Stars payments are not available right now.',
            'stars_purchase_disclaimer' => "Disclaimer Conditions\n\nCountry: __c__\nPrice (USD): __p__\nPrice (Stars): __s__⭐️\n\nAfter payment the amount is deducted and cannot be refunded.\n\nDo you want to purchase this number?",
            'no_numbers' => 'There are no numbers available for this country at the moment✖️',
            'purchase_success' => "✅- Telegram account purchased successfully -✅\n\n🌎 - Country: __c__\n☎️ - Number: <code>__num__</code>\n💰- Price: __p__$\n💬 - Code: ××××\n🔑 - Password: ××××××\n\n- Please register the number in Telegram. After registration, click (Request Code) to get the activation code.",
            'request_code' => 'Request Code',
            'insufficient_balance' => 'Your balance is insufficient',
            'code_pending' => 'The code has not arrived yet',
            'code_received' => "💬- The code has arrived below •\n\n🌎 - Country: __c__\n☎️ - Number: <code>__num__</code>\n💰- Price: __p__$\n💬 - Code: <code>__code__</code>\n🔑 - Password: <code>__pass__</code>\n\n✅- Please activate the number in the Telegram app ✳️",
            'purchase_in_progress' => 'A purchase is already in progress, please wait.',
            'maintenance_message' => "⚙️ The bot is currently in maintenance mode and will be back soon.\nThanks for your patience!",
            'stars_invoice_title' => 'Buy Telegram Account',
            'stars_invoice_description' => 'Purchase a __c__ number for __p__$ (about __s__⭐️).',
            'stars_invoice_message' => "Price: __p__$ ≈ __s__⭐️\nTap the button below to pay with Stars, then return and press verify.",
            'stars_invoice_button' => 'Pay with Stars',
            'stars_price_perk' => 'Approx Stars/1k: __s__⭐️',
            'stars_price_total' => 'Approx Stars: __s__⭐️',
            'purchase_failed' => 'We could not complete the purchase. Please contact support.',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user.',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
    'ru' => [
        'label' => 'Русский 🇷🇺',
        'back' => 'Назад',
        'main_menu' => 'Главное меню',
        'change_language' => 'Сменить язык',
        'strings' => [
            'main_numbers_button' => 'Раздел номеров',
            'main_smm_button' => 'Раздел раскрутки',
            'numbers_usd_button' => 'Покупка за доллары',
            'numbers_stars_button' => 'Покупка за звёзды',
            'smm_usd_button' => 'Раскрутка за доллары',
            'smm_stars_button' => 'Раскрутка за звёзды',
            'smm_select_category' => 'Выберите категорию услуг.',
            'smm_select_service' => 'Выберите нужную услугу.',
            'smm_service_details' => 'Описание услуги',
            'smm_price_info' => 'Цена за 1k: __rate__$ | Мин: __min__ | Макс: __max__',
            'smm_continue' => 'Продолжить',
            'smm_enter_link' => 'Отправьте ссылку или /cancel для отмены.',
            'smm_enter_quantity' => 'Отправьте количество (__min__ - __max__).',
            'smm_quantity_invalid' => 'Неверное количество.',
            'smm_link_saved' => 'Ссылка сохранена, теперь отправьте количество.',
            'smm_order_summary' => "Услуга: __service__\nКоличество: __quantity__\nЦена: __price__$",
            'smm_confirm_button' => 'Подтвердить',
            'smm_cancel_button' => 'Отмена',
            'smm_order_success' => 'Заказ успешно отправлен.',
            'smm_order_failed' => 'Не удалось выполнить заказ.',
            'smm_input_cancelled' => 'Операция отменена.',
            'verify_button' => 'Нажмите для проверки',
            'verify_text' => "🖐︙Добро пожаловать\n\n- Вы должны подписаться на официальный канал бота, чтобы использовать бота\n- Ссылка на канал: {{channel_link}}\n\n🙋‍♂️ ⁞ Нажмите кнопку ниже, чтобы подтвердить",
            'subscribe_button' => 'Подписаться на канал',
            'subscription_verified' => 'Подписка успешно подтверждена.',
            'subscription_not_verified' => 'Подписка ещё не обнаружена.',
            'banned_message' => 'Вы не можете использовать бота, потому что вы забанены.',
            'invite_reward' => 'Член присоединился по вашей ссылке, и вы заработали {{invite_point}}.',
            'menu_purchase' => '* Покупка аккаунтов Telegram *',
            'menu_purchase_usd' => 'Покупка аккаунтов (USD)',
            'menu_purchase_stars' => 'Покупка аккаунтов (Stars)',
            'menu_recharge' => '* Пополните свой баланс *',
            'menu_support' => '* Команда поддержки *',
            'menu_agents' => '* Агенты *',
            'menu_bot_activations' => '* Активации бота *',
            'menu_free_balance' => '* Выиграйте бесплатный баланс *',
            'welcome' => "- Здравствуйте, добро пожаловать в бот аккаунтов Telegram.\n\n- Вы можете легко получить виртуальный номер с готовыми аккаунтами Telegram, с невероятной скоростью.. полностью автоматически, по самой низкой цене, с поддержкой 24/7.\n\n- Ваш ID аккаунта: <code>{{user_id}}</code>\n- Ваш баланс: {{balance}}$",
            'charge_info' => "Чтобы пополнить свой счет, пожалуйста, свяжитесь с администрацией {{charge_link}}\nили свяжитесь с одним из агентов в боте",
            'support_info' => "Если у вас возникли проблемы, свяжитесь с нами по следующему имени пользователя \n{{support_link}}",
            'no_agents' => 'В данный момент в боте нет агентов',
            'invite_info' => "Пригласите своих друзей по следующей ссылке и заработайте {{invite_point}} за каждого человека, который присоединится через вас\n\n<code>{{ref_link}}</code>",
            'available_countries' => "Доступные страны\n",
            'no_next_page' => 'Нет следующего списка',
            'no_previous_page' => 'Нет предыдущего списка',
            'country_selection' => "Пожалуйста, выберите страну, из которой вы хотите купить аккаунт•\n\nВсе страны, указанные ниже, имеют аккаунты для активации Telegram и получают доступ к коду на любой версии, нажмите на страну, чтобы купить •",
            'button_previous' => 'Предыдущий',
            'button_next' => 'Следующий',
            'confirm_purchase' => 'Подтвердить покупку',
            'disclaimer' => "Условия отказа от ответственности\n\nУважаемый клиент, после того как вы согласитесь и нажмете кнопку покупки, стоимость номера будет снята, и номер станет вашим, и деньги не будут возвращены.\n\nВы хотите купить номер",
            'stars_disabled' => 'Оплата звёздами недоступна в данный момент.',
            'stars_purchase_disclaimer' => "Условия\n\nСтрана: __c__\nЦена (USD): __p__\nЦена (звёзды): __s__⭐️\n\nПосле оплаты средства не возвращаются.\n\nПокупаем номер?",
            'no_numbers' => 'В данный момент нет номеров для этой страны✖️',
            'purchase_success' => "✅- Аккаунт Telegram успешно куплен -✅\n\n🌎 - Страна: __c__\n☎️ - Номер: <code>__num__</code>\n💰- Цена: __p__$\n💬 - Код: ××××\n🔑 - Пароль: ××××××\n\n- Пожалуйста, зарегистрируйте номер в Telegram. После регистрации нажмите (Запросить код), чтобы получить код активации.",
            'request_code' => 'Запросить код',
            'insufficient_balance' => 'Ваш баланс недостаточен',
            'code_pending' => 'Код еще не пришел',
            'code_received' => "💬- Код пришел ниже •\n\n🌎 - Страна: __c__\n☎️ - Номер: <code>__num__</code>\n💰- Цена: __p__$\n💬 - Код: <code>__code__</code>\n🔑 - Пароль: <code>__pass__</code>\n\n✅- Пожалуйста, активируйте номер в приложении Telegram ✳️",
            'purchase_in_progress' => 'Покупка уже выполняется, подождите.',
            'maintenance_message' => "⚙️ Бот находится на обслуживании и скоро вернётся.\nСпасибо за ожидание!",
            'stars_invoice_title' => 'Покупка номера Telegram',
            'stars_invoice_description' => 'Покупка номера __c__ за __p__$ (≈ __s__⭐️).',
            'stars_invoice_message' => "Цена: __p__$ ≈ __s__⭐️\nНажмите кнопку, чтобы оплатить звёздами, затем вернитесь и нажмите «Проверить».",
            'stars_invoice_button' => 'Оплатить звёздами',
            'stars_price_perk' => 'Примерно звёзд за 1k: __s__⭐️',
            'stars_price_total' => 'Примерно звёзд: __s__⭐️',
            'purchase_failed' => 'Не удалось завершить покупку. Свяжитесь с поддержкой.',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
    'fa' => [
        'label' => 'فارسى 🇮🇷',
        'back' => 'بازگشت',
        'main_menu' => 'منوی اصلی',
        'change_language' => 'تغییر زبان',
        'strings' => [
            'main_numbers_button' => 'بخش خرید شماره',
            'main_smm_button' => 'بخش رشق',
            'numbers_usd_button' => 'خرید با دلار',
            'numbers_stars_button' => 'خرید با ستاره',
            'smm_usd_button' => 'رشق با دلار',
            'smm_stars_button' => 'رشق با ستاره',
            'smm_select_category' => 'لطفاً دسته‌بندی مورد نظر را انتخاب کنید.',
            'smm_select_service' => 'خدمت مورد نظر را انتخاب کنید.',
            'smm_service_details' => 'جزئیات خدمت',
            'smm_price_info' => 'قیمت هر 1k: __rate__$ | حداقل: __min__ | حداکثر: __max__',
            'smm_continue' => 'ادامه',
            'smm_enter_link' => 'لینک هدف را ارسال کنید یا /cancel برای لغو.',
            'smm_enter_quantity' => 'تعداد مورد نیاز را بین __min__ و __max__ ارسال کنید.',
            'smm_quantity_invalid' => 'تعداد نامعتبر است.',
            'smm_link_saved' => 'لینک ذخیره شد، حالا تعداد را ارسال کنید.',
            'smm_order_summary' => "خدمت: __service__\nتعداد: __quantity__\nقیمت: __price__$",
            'smm_confirm_button' => 'تأیید',
            'smm_cancel_button' => 'لغو',
            'smm_order_success' => 'سفارش با موفقیت ثبت شد.',
            'smm_order_failed' => 'ثبت سفارش انجام نشد.',
            'smm_input_cancelled' => 'فرآیند لغو شد.',
            'verify_button' => 'برای تأیید کلیک کنید',
            'verify_text' => "🖐︙خوش آمدید\n\n- برای استفاده از ربات، باید به کانال رسمی ربات عضو شوید\n- لینک کانال: {{channel_link}}\n\n🙋‍♂️ ⁞ برای تأیید، روی دکمه زیر کلیک کنید",
            'subscribe_button' => 'عضویت در کانال',
            'subscription_verified' => 'اشتراک شما با موفقیت تأیید شد.',
            'subscription_not_verified' => 'اشتراک شما هنوز شناسایی نشده است.',
            'banned_message' => 'شما نمی‌توانید از ربات استفاده کنید زیرا شما مسدود شده‌اید.',
            'invite_reward' => 'یک عضو از طریق لینک شما وارد شده است و شما {{invite_point}} برنده شده‌اید.',
            'menu_purchase' => '* خرید حساب‌های تلگرام *',
            'menu_purchase_usd' => 'خرید حساب با دلار',
            'menu_purchase_stars' => 'خرید حساب با ستاره‌ها',
            'menu_recharge' => '* موجودی خود را شارژ کنید *',
            'menu_support' => '* تیم پشتیبانی *',
            'menu_agents' => '* نمایندگان *',
            'menu_bot_activations' => '* فعال‌سازی‌های ربات *',
            'menu_free_balance' => '* برنده شدن موجودی رایگان *',
            'welcome' => "- سلام، خوش آمدید به ربات حساب‌های تلگرام.\n\n- شما می‌توانید به راحتی یک شماره مجازی با حساب‌های تلگرام آماده دریافت کنید، با سرعتی شگفت‌انگیز.. به‌طور کامل خودکار، با پایین‌ترین قیمت ممکن، با پشتیبانی ۲۴ ساعته.\n\n- شناسه حساب شما: <code>{{user_id}}</code>\n- موجودی شما: {{balance}}$",
            'charge_info' => "برای شارژ حساب خود، لطفاً با مدیریت {{charge_link}} تماس بگیرید\nیا با یکی از نمایندگان در ربات تماس بگیرید",
            'support_info' => "اگر با مشکلی مواجه شدید، لطفاً با ما از طریق نام کاربری زیر تماس بگیرید \n{{support_link}}",
            'no_agents' => 'در حال حاضر نماینده‌ای در ربات وجود ندارد',
            'invite_info' => "دوستان خود را با استفاده از لینک زیر دعوت کنید و به ازای هر شخصی که از طریق شما به جمع می‌پیوندد {{invite_point}} دریافت کنید\n\n<code>{{ref_link}}</code>",
            'available_countries' => "کشورهای موجود\n",
            'no_next_page' => 'لیست بعدی موجود نیست',
            'no_previous_page' => 'لیست قبلی موجود نیست',
            'country_selection' => "لطفاً کشوری را که می‌خواهید حسابی از آن خریداری کنید انتخاب کنید•\n\nتمام کشورهایی که در زیر ذکر شده‌اند حساب‌هایی برای فعال‌سازی تلگرام دارند و کد را بر روی هر نسخه‌ای دریافت می‌کنند، برای خرید بر روی کشور کلیک کنید •",
            'button_previous' => 'قبلی',
            'button_next' => 'بعدی',
            'confirm_purchase' => 'تأیید خرید',
            'disclaimer' => "شرایط سلب مسئولیت\n\nمشتری عزیز، پس از اینکه شما موافقت کردید و روی دکمه خرید کلیک کردید، مبلغ شماره کسر شده و شماره متعلق به شما خواهد بود و وجهی بازگشت داده نخواهد شد.\n\nآیا می‌خواهید شماره‌ای خریداری کنید",
            'stars_disabled' => 'پرداخت با ستاره‌ها در حال حاضر فعال نیست.',
            'stars_purchase_disclaimer' => "شرایط\n\nکشور: __c__\nقیمت (دلار): __p__\nقیمت (ستاره): __s__⭐️\n\nپس از پرداخت امکان بازگشت وجه وجود ندارد.\n\nآیا خرید را ادامه می‌دهید؟",
            'no_numbers' => 'در حال حاضر شماره‌ای برای این کشور موجود نیست✖️',
            'purchase_success' => "✅- حساب تلگرام با موفقیت خریداری شد -✅\n\n🌎 - کشور: __c__\n☎️ - شماره: <code>__num__</code>\n💰- قیمت: __p__$\n💬 - کد: ××××\n🔑 - کلمه عبور: ××××××\n\n- لطفاً شماره را در تلگرام ثبت کنید. پس از ثبت‌نام، روی (درخواست کد) کلیک کنید تا کد فعال‌سازی را دریافت کنید.",
            'request_code' => 'درخواست کد',
            'insufficient_balance' => 'موجودی شما کافی نیست',
            'code_pending' => 'کد هنوز نرسیده است',
            'code_received' => "💬- کد در پایین آمده است •\n\n🌎 - کشور: __c__\n☎️ - شماره: <code>__num__</code>\n💰- قیمت: __p__$\n💬 - کد: <code>__code__</code>\n🔑 - کلمه عبور: <code>__pass__</code>\n\n✅- لطفاً شماره را در برنامه تلگرام فعال کنید ✳️",
            'purchase_in_progress' => 'یک فرایند خرید در حال انجام است، لطفاً کمی صبر کنید.',
            'maintenance_message' => "⚙️ ربات در حال نگهداری است و به زودی در دسترس خواهد بود.\nاز شکیبایی شما سپاسگزاریم.",
            'stars_invoice_title' => 'خرید حساب تلگرام',
            'stars_invoice_description' => 'خرید شماره __c__ به قیمت __p__$ (حدود __s__⭐️).',
            'stars_invoice_message' => "قیمت: __p__$ ≈ __s__⭐️\nبرای پرداخت با ستاره‌ها روی دکمه زیر بزنید و سپس گزینه تأیید را بزنید.",
            'stars_invoice_button' => 'پرداخت با ستاره‌ها',
            'stars_price_perk' => 'تقریباً ستاره برای هر ۱۰۰۰: __s__⭐️',
            'stars_price_total' => 'تعداد ستاره تقریبی: __s__⭐️',
            'purchase_failed' => 'تکمیل خرید ممکن نشد، لطفاً با پشتیبانی تماس بگیرید.',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
    'cht' => [
        'label' => '繁體中文 🇨🇳',
        'back' => '返回',
        'main_menu' => '主菜單',
        'change_language' => '更改語言',
        'strings' => [
            'main_numbers_button' => '購買號碼區',
            'main_smm_button' => '增粉區',
            'numbers_usd_button' => '美元購買',
            'numbers_stars_button' => '星星購買',
            'smm_usd_button' => '美元增粉',
            'smm_stars_button' => '星星增粉',
            'smm_select_category' => '請選擇增粉類別。',
            'smm_select_service' => '請選擇服務。',
            'smm_service_details' => '服務詳情',
            'smm_price_info' => '每 1k 價格: __rate__$ | 最低: __min__ | 最高: __max__',
            'smm_continue' => '繼續',
            'smm_enter_link' => '請發送連結，或輸入 /cancel 取消。',
            'smm_enter_quantity' => '請輸入數量 (__min__ ~ __max__)。',
            'smm_quantity_invalid' => '數量不正確。',
            'smm_link_saved' => '已儲存連結，請輸入數量。',
            'smm_order_summary' => "服務: __service__\n數量: __quantity__\n價格: __price__$",
            'smm_confirm_button' => '確認下單',
            'smm_cancel_button' => '取消',
            'smm_order_success' => '下單成功。',
            'smm_order_failed' => '下單失敗，請稍後再試。',
            'smm_input_cancelled' => '操作已取消。',
            'verify_button' => '點擊以驗證',
            'verify_text' => "🖐︙歡迎\n\n- 您必須訂閱官方機器人頻道才能使用機器人\n- 頻道鏈接：{{channel_link}}\n\n🙋‍♂️ ⁞ 點擊下方按鈕進行驗證",
            'subscribe_button' => '訂閱頻道',
            'subscription_verified' => '已成功驗證訂閱。',
            'subscription_not_verified' => '尚未偵測到您的訂閱。',
            'banned_message' => '您無法使用機器人，因為您已被禁止。',
            'invite_reward' => '有成員通過您的連結加入，您贏得了 {{invite_point}}。',
            'menu_purchase' => '* 購買 Telegram 帳戶 *',
            'menu_purchase_usd' => '以美元購買帳號',
            'menu_purchase_stars' => '以 Stars 購買帳號',
            'menu_recharge' => '* 充值您的餘額 *',
            'menu_support' => '* 支持團隊 *',
            'menu_agents' => '* 代理商 *',
            'menu_bot_activations' => '* 機器人激活 *',
            'menu_free_balance' => '* 贏取免費餘額 *',
            'welcome' => "- 您好，歡迎來到 Telegram 帳戶機器人。\n\n- 您可以輕鬆獲得虛擬號碼和現成的 Telegram 帳戶，以驚人的速度.. 完全自動化，以最低的價格，提供 24/7 支持。\n\n- 您的帳號 ID：<code>{{user_id}}</code>\n- 您的餘額：{{balance}}$",
            'charge_info' => "要充值您的帳戶，請聯繫管理員 {{charge_link}}\n或聯繫機器人中的一位代理商",
            'support_info' => "如果您遇到任何問題，請通過以下用戶名與我們聯繫 \n{{support_link}}",
            'no_agents' => '目前機器人中沒有代理商',
            'invite_info' => "邀請您的朋友使用以下鏈接，每位通過您加入的人您將獲得 {{invite_point}}\n\n<code>{{ref_link}}</code>",
            'available_countries' => "可用國家\n",
            'no_next_page' => '沒有下一個列表',
            'no_previous_page' => '沒有上一個列表',
            'country_selection' => "請選擇您想購買帳戶的國家•\n\n所有列出的國家都有可用於激活 Telegram 的帳戶，並在任何版本上接收代碼，點擊該國以進行購買 •",
            'button_previous' => '上一個',
            'button_next' => '下一個',
            'confirm_purchase' => '確認購買',
            'disclaimer' => "免責聲明條款\n\n親愛的客戶，當您同意並點擊購買按鈕後，將扣除號碼的費用，該號碼將屬於您且不會退款。\n\n您想購買號碼嗎",
            'stars_disabled' => '目前無法使用 Stars 付款。',
            'stars_purchase_disclaimer' => "免責聲明\n\n國家: __c__\n美元價格: __p__\nStars 價格: __s__⭐️\n\n付款後恕不退款。\n\n是否購買此號碼？",
            'no_numbers' => '目前此國家沒有可用的號碼✖️',
            'purchase_success' => "✅- Telegram 帳戶成功購買 -✅\n\n🌎 - 國家: __c__\n☎️ - 號碼: <code>__num__</code>\n💰- 價格: __p__$\n💬 - 代碼: ××××\n🔑 - 密碼: ××××××\n\n- 請在 Telegram 中註冊該號碼。註冊後，點擊（請求代碼）以獲取激活代碼。",
            'request_code' => '請求代碼',
            'insufficient_balance' => '您的餘額不足',
            'code_pending' => '代碼尚未到達',
            'code_received' => "💬- 代碼已到達如下 •\n\n🌎 - 國家: __c__\n☎️ - 號碼: <code>__num__</code>\n💰- 價格: __p__$\n💬 - 代碼: <code>__code__</code>\n🔑 - 密碼: <code>__pass__</code>\n\n✅- 請在 Telegram 應用中激活該號碼 ✳️",
            'purchase_in_progress' => '目前有購買請求在處理，請稍候。',
            'maintenance_message' => "⚙️ 機器人正在維護中，很快恢復服務。\n感謝您的耐心等待。",
            'stars_invoice_title' => '購買 Telegram 帳號',
            'stars_invoice_description' => '購買 __c__ 號碼，價格 __p__$ (約 __s__⭐️)。',
            'stars_invoice_message' => "價格: __p__$ ≈ __s__⭐️\n點擊下方按鈕以 Stars 付款，然後返回按下驗證。",
            'stars_invoice_button' => '使用 Stars 付款',
            'stars_price_perk' => '每 1000 約 __s__⭐️',
            'stars_price_total' => '約 __s__⭐️',
            'purchase_failed' => '無法完成購買，請聯絡支援。',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
    'chb' => [
        'label' => '简体中文 🇨🇳',
        'back' => '返回',
        'main_menu' => '主菜单',
        'change_language' => '更改语言',
        'strings' => [
            'main_numbers_button' => '购号专区',
            'main_smm_button' => '涨粉专区',
            'numbers_usd_button' => '美元购买',
            'numbers_stars_button' => '星星购买',
            'smm_usd_button' => '美元涨粉',
            'smm_stars_button' => '星星涨粉',
            'smm_select_category' => '请选择涨粉类别。',
            'smm_select_service' => '请选择服务。',
            'smm_service_details' => '服务详情',
            'smm_price_info' => '每 1k 价格: __rate__$ | 最低: __min__ | 最高: __max__',
            'smm_continue' => '继续',
            'smm_enter_link' => '发送链接，或输入 /cancel 取消。',
            'smm_enter_quantity' => '请输入数量 (__min__ ~ __max__)。',
            'smm_quantity_invalid' => '数量无效。',
            'smm_link_saved' => '已保存链接，请输入数量。',
            'smm_order_summary' => "服务: __service__\n数量: __quantity__\n价格: __price__$",
            'smm_confirm_button' => '确认下单',
            'smm_cancel_button' => '取消',
            'smm_order_success' => '下单成功。',
            'smm_order_failed' => '下单失败，请稍后重试。',
            'smm_input_cancelled' => '操作已取消。',
            'verify_button' => '点击以验证',
            'verify_text' => "🖐︙欢迎\n\n- 您必须订阅官方机器人频道才能使用机器人\n- 频道链接：{{channel_link}}\n\n🙋‍♂️ ⁞ 点击下方按钮进行验证",
            'subscribe_button' => '订阅频道',
            'subscription_verified' => '订阅验证成功。',
            'subscription_not_verified' => '尚未检测到您的订阅。',
            'banned_message' => '您无法使用机器人，因为您已被禁止。',
            'invite_reward' => '有成员通过您的链接加入，您赢得了 {{invite_point}}。',
            'menu_purchase' => '* 购买 Telegram 账户 *',
            'menu_purchase_usd' => '用美元购买账号',
            'menu_purchase_stars' => '用 Stars 购买账号',
            'menu_recharge' => '* 充值您的余额 *',
            'menu_support' => '* 支持团队 *',
            'menu_agents' => '* 代理商 *',
            'menu_bot_activations' => '* 机器人激活 *',
            'menu_free_balance' => '* 赢取免费余额 *',
            'welcome' => "- 您好，欢迎来到 Telegram 账户机器人。\n\n- 您可以轻松获得虚拟号码和现成的 Telegram 账户，以惊人的速度.. 完全自动化，以最低的价格，提供 24/7 支持。\n\n- 您的账户 ID：<code>{{user_id}}</code>\n- 您的余额：{{balance}}$",
            'charge_info' => "要充值您的账户，请联系管理员 {{charge_link}}\n或联系机器人中的一位代理商",
            'support_info' => "如果您遇到任何问题，请通过以下用户名与我们联系 \n{{support_link}}",
            'no_agents' => '目前机器人中没有代理商',
            'invite_info' => "邀请您的朋友使用以下链接，每位通过您加入的人您将获得 {{invite_point}}\n\n<code>{{ref_link}}</code>",
            'available_countries' => "可用国家\n",
            'no_next_page' => '没有下一个列表',
            'no_previous_page' => '没有上一个列表',
            'country_selection' => "请选择您想购买账户的国家•\n\n所有列出的国家都有用于激活 Telegram 的账户，并在任何版本上接收代码，点击该国以进行购买 •",
            'button_previous' => '上一个',
            'button_next' => '下一个',
            'confirm_purchase' => '确认购买',
            'disclaimer' => "免责声明条款\n\n亲爱的客户，当您同意并点击购买按钮后，将扣除号码的费用，该号码将属于您且不会退款。\n\n您想购买号码吗",
            'stars_disabled' => '暂时无法使用 Stars 支付。',
            'stars_purchase_disclaimer' => "免责声明\n\n国家: __c__\n美元价格: __p__\nStars 价格: __s__⭐️\n\n付款后无法退款。\n\n确定购买该号码吗？",
            'no_numbers' => '目前此国家没有可用的号码✖️',
            'purchase_success' => "✅- Telegram 账户购买成功 -✅\n\n🌎 - 国家: __c__\n☎️ - 号码: <code>__num__</code>\n💰- 价格: __p__$\n💬 - 代码: ××××\n🔑 - 密码: ××××××\n\n- 请在 Telegram 中注册该号码。注册后，点击（请求代码）以获取激活代码。",
            'request_code' => '请求代码',
            'insufficient_balance' => '您的余额不足',
            'code_pending' => '代码尚未到达',
            'code_received' => "💬- 代码已到达如下 •\n\n🌎 - 国家: __c__\n☎️ - 号码: <code>__num__</code>\n💰- 价格: __p__$\n💬 - 代码: <code>__code__</code>\n🔑 - 密码: <code>__pass__</code>\n\n✅- 请在 Telegram 应用中激活该号码 ✳️",
            'purchase_in_progress' => '正在处理一次购买请求，请稍候。',
            'maintenance_message' => "⚙️ 机器人正在维护中，很快恢复服务。\n感谢您的耐心等待。",
            'stars_invoice_title' => '购买 Telegram 账号',
            'stars_invoice_description' => '购买 __c__ 号码，价格 __p__$ (约 __s__⭐️)。',
            'stars_invoice_message' => "价格: __p__$ ≈ __s__⭐️\n点击按钮使用 Stars 支付，然后返回并点击验证。",
            'stars_invoice_button' => '使用 Stars 支付',
            'stars_price_perk' => '每 1000 约 __s__⭐️',
            'stars_price_total' => '约 __s__⭐️',
            'purchase_failed' => '无法完成购买，请联系客服。',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
    'tr' => [
        'label' => 'Türkçe 🇹🇷',
        'back' => 'Geri',
        'main_menu' => 'Ana Menü',
        'change_language' => 'Dili Değiştir',
        'strings' => [
            'main_numbers_button' => 'Numara Satın Alma',
            'main_smm_button' => 'SMM Hizmetleri',
            'numbers_usd_button' => 'Dolar ile satın al',
            'numbers_stars_button' => 'Yıldız ile satın al',
            'smm_usd_button' => 'Dolar ile hizmet al',
            'smm_stars_button' => 'Yıldız ile hizmet al',
            'smm_select_category' => 'Lütfen rüşetme kategorisini seç.',
            'smm_select_service' => 'İstediğin hizmeti seç.',
            'smm_service_details' => 'Hizmet detayları',
            'smm_price_info' => '1k fiyatı: __rate__$ | Min: __min__ | Max: __max__',
            'smm_continue' => 'Devam et',
            'smm_enter_link' => 'Hedef linki gönder (iptal için /cancel).',
            'smm_enter_quantity' => 'Miktarı gönder (__min__ - __max__).',
            'smm_quantity_invalid' => 'Geçersiz miktar.',
            'smm_link_saved' => 'Link kaydedildi, şimdi miktarı gönder.',
            'smm_order_summary' => "Hizmet: __service__\nMiktar: __quantity__\nFiyat: __price__$",
            'smm_confirm_button' => 'Onayla',
            'smm_cancel_button' => 'İptal',
            'smm_order_success' => 'Sipariş başarıyla gönderildi.',
            'smm_order_failed' => 'Sipariş oluşturulamadı.',
            'smm_input_cancelled' => 'İşlem iptal edildi.',
            'verify_button' => 'Doğrulamak için tıkla',
            'verify_text' => "🖐︙Hoş geldin\n\n- Botu kullanabilmek için resmi bot kanalına abone olmalısın\n- Kanal bağlantısı: {{channel_link}}\n\n🙋‍♂️ ⁞ Doğrulamak için aşağıdaki butona tıkla",
            'subscribe_button' => 'Kanala katıl',
            'subscription_verified' => 'Abonelik başarıyla doğrulandı.',
            'subscription_not_verified' => 'Aboneliğin henüz doğrulanmadı.',
            'banned_message' => 'Botu kullanamazsın çünkü engellendin.',
            'invite_reward' => 'Bağlantından bir üye katıldı ve {{invite_point}} kazandın.',
            'menu_purchase' => '* Telegram Hesabı Satın Al *',
            'menu_purchase_usd' => 'Dolar ile hesap satın al',
            'menu_purchase_stars' => 'Stars ile hesap satın al',
            'menu_recharge' => '* Bakiyeni Yükle *',
            'menu_support' => '* Destek Ekibi *',
            'menu_agents' => '* Bayiler *',
            'menu_bot_activations' => '* Bot Aktivasyonları *',
            'menu_free_balance' => '* Ücretsiz Bakiye Kazan *',
            'welcome' => "- Merhaba, Telegram hesap botuna hoş geldin.\n\n- Hazır Telegram hesaplarıyla sanal numarayı son derece hızlı, tamamen otomatik ve mümkün olan en düşük fiyatla elde edebilirsin. 7/24 destek sunuyoruz.\n\n- Hesap kimliğin: <code>{{user_id}}</code>\n- Bakiyen: {{balance}}$",
            'charge_info' => "Bakiyeni yüklemek için yönetim ile iletişime geç {{charge_link}}\nveya bottaki bayilerden biriyle görüş",
            'support_info' => "Herhangi bir sorunla karşılaşırsan aşağıdaki kullanıcı adı üzerinden bizimle iletişime geç \n{{support_link}}",
            'no_agents' => 'Şu anda botta kayıtlı bayi yok',
            'invite_info' => "Arkadaşlarını aşağıdaki link ile davet et ve senin üzerinden katılan her kişi için {{invite_point}} kazan\n\n<code>{{ref_link}}</code>",
            'available_countries' => "Mevcut ülkeler\n",
            'no_next_page' => 'Sonraki liste yok',
            'no_previous_page' => 'Önceki liste yok',
            'country_selection' => "Lütfen hesap satın almak istediğin ülkeyi seç•\n\nAşağıdaki tüm ülkelerde Telegram aktivasyonu için kullanılabilecek numaralar bulunur. Satın almak için ülkeye tıkla •",
            'button_previous' => 'Önceki',
            'button_next' => 'Sonraki',
            'confirm_purchase' => 'Satın Almayı Onayla',
            'disclaimer' => "Sorumluluk Reddi Şartları\n\nDeğerli müşterimiz, onay verip satın alma butonuna bastıktan sonra numaranın ücreti bakiyenden düşülür ve numara sana ait olur. Ücret iadesi yapılmaz.\n\nBir numara satın almak istiyor musun?",
            'stars_disabled' => 'Stars ile ödeme şu anda kullanılamıyor.',
            'stars_purchase_disclaimer' => "Sorumluluk Reddi\n\nÜlke: __c__\nFiyat (USD): __p__\nFiyat (Stars): __s__⭐️\n\nÖdeme sonrası iade yoktur.\n\nNumarayı satın almak istiyor musun?",
            'no_numbers' => 'Bu ülke için şu anda numara yok✖️',
            'purchase_success' => "✅- Telegram hesabı başarıyla satın alındı -✅\n\n🌎 - Ülke: __c__\n☎️ - Numara: <code>__num__</code>\n💰- Fiyat: __p__$\n💬 - Kod: ××××\n🔑 - Şifre: ××××××\n\n- Numarayı Telegram\'da kaydet. Kaydettikten sonra aktivasyon kodunu almak için (Kod İste) butonuna tıkla.",
            'request_code' => 'Kodu İste',
            'insufficient_balance' => 'Bakiyen yetersiz',
            'code_pending' => 'Kod henüz gelmedi',
            'code_received' => "💬- Kod aşağıda yer alıyor •\n\n🌎 - Ülke: __c__\n☎️ - Numara: <code>__num__</code>\n💰- Fiyat: __p__$\n💬 - Kod: <code>__code__</code>\n🔑 - Şifre: <code>__pass__</code>\n\n✅- Lütfen numarayı Telegram uygulamasında etkinleştir ✳️",
            'purchase_in_progress' => 'Bir satın alma işlemi devam ediyor, lütfen bekle.',
            'maintenance_message' => "⚙️ Bot şu anda bakım modunda, kısa süre içinde tekrar aktif olacak.\nAnlayışınız için teşekkürler.",
            'stars_invoice_title' => 'Telegram hesabı satın al',
            'stars_invoice_description' => '__c__ numarası __p__$ (yaklaşık __s__⭐️) karşılığında.',
            'stars_invoice_message' => "Fiyat: __p__$ ≈ __s__⭐️\nStars ile ödeme yapmak için aşağıdaki butona tıkla ve ardından doğrula.",
            'stars_invoice_button' => 'Stars ile öde',
            'stars_price_perk' => 'Yaklaşık Stars/1k: __s__⭐️',
            'stars_price_total' => 'Yaklaşık Stars: __s__⭐️',
            'purchase_failed' => 'Satın alma tamamlanamadı, lütfen destekle iletişime geç.',
            'support_intro' => 'Need help? Open a ticket and we will assist you shortly.',
            'support_new_ticket_button' => 'Open Ticket',
            'support_my_tickets_button' => 'My Tickets',
            'support_ticket_subject_prompt' => 'Send a short subject for your ticket.',
            'support_ticket_message_prompt' => 'Describe your issue.',
            'support_ticket_created' => 'Ticket created, we will get back to you soon.',
            'support_ticket_list_title' => 'My tickets',
            'support_ticket_list_empty' => 'No tickets yet.',
            'support_ticket_reply_prompt' => 'Please type your reply.',
            'support_ticket_closed' => 'Ticket closed. Thank you.',
            'support_ticket_reply_button' => 'Reply',
            'support_ticket_close_button' => 'Close',
            'support_ticket_header' => 'Ticket',
            'support_ticket_status_label' => 'Status',
            'support_ticket_subject_label' => 'Subject',
            'support_admin_label' => 'Support',
            'support_user_label' => 'You',
            'support_waiting_for_admin' => 'Reply sent. Please wait for support.',
            'support_admin_reply_notice' => 'Support replied to your ticket.',
            'support_admin_reply_sent' => 'Reply sent to user.',
            'support_input_cancelled' => 'Operation cancelled.',
            'user_banned' => 'Access to this bot is restricted.',
            'feature_disabled' => 'This section is disabled.',
            'admin_only' => 'Admins only.',
            'admin_panel_button' => 'Admin Panel',
            'admin_panel_title' => 'Admin Panel',
            'admin_section_tickets' => 'Tickets',
            'admin_section_users' => 'Users',
            'admin_section_features' => 'Features',
            'admin_section_stars' => 'Stars',
            'admin_section_forcesub' => 'Forced Subscription',
            'admin_section_referrals' => 'Referrals',
            'admin_features_title' => 'Feature toggles',
            'admin_stars_title' => 'Stars payments',
            'admin_stars_enabled_label' => 'Enabled',
            'admin_stars_price_label' => 'USD per star',
            'admin_stars_toggle_button' => 'Toggle',
            'admin_stars_set_price_button' => 'Set price',
            'admin_prompt_star_price' => 'Send the new USD value for a single star.',
            'admin_star_price_updated' => 'Stars price updated.',
            'admin_forcesub_title' => 'Forced subscription',
            'admin_forcesub_toggle_button' => 'Toggle',
            'admin_forcesub_set_link_button' => 'Set fallback link',
            'admin_forcesub_set_channel_button' => 'Set channel',
            'admin_forcesub_link_prompt' => 'Send the fallback link or invite URL.',
            'admin_forcesub_link_updated' => 'Fallback link updated.',
            'admin_forcesub_channel_prompt' => 'Send channel ID and link in the format ID|https://t.me/...',
            'admin_forcesub_channel_updated' => 'Channel updated.',
            'admin_referrals_hint' => 'Use /referrals <telegram_id> to review a partner.',
            'admin_referrals_toggle_button' => 'Toggle referrals',
            'admin_users_title' => "User controls\nUse /user <telegram_id>\nUse /ban <telegram_id> /unban <telegram_id>",
            'admin_user_id_prompt' => 'Provide a valid Telegram ID.',
            'admin_user_not_found' => 'User not found.',
            'admin_user_updated' => 'User updated.',
            'admin_user_status_banned' => 'BANNED',
            'admin_user_status_active' => 'ACTIVE',
            'admin_user_ban_button' => 'Ban user',
            'admin_user_unban_button' => 'Unban user',
            'referral_disabled' => 'Referral program is disabled.',
            'referral_title' => 'Referral Program',
            'referral_link_label' => "Share link:\n__link__\nCode: __code__",
            'referral_stats' => "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$",
            'referral_withdraw_button' => 'Withdraw earnings',
            'referral_withdraw_success' => 'Transferred __amount__$ to your wallet.',
            'referral_no_rewards' => 'No referral rewards available.',
            'referral_attached' => 'Referral recorded successfully.',
            'button_refresh' => 'Refresh',
        ],
    ],
];
