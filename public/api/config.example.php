<?php
/**
 * Скопируйте этот файл как config.php и заполните своими данными.
 * config.php не должен попадать в git — он создаётся только на сервере.
 */

return [
    // Telegram Bot API
    'telegram_bot_token' => 'YOUR_BOT_TOKEN_HERE',
    'telegram_chat_id'   => 'YOUR_CHAT_ID_HERE',

    // Email для резервных уведомлений
    'email_to'      => 'info@example.by',
    'email_from'    => 'noreply@example.by',
    'email_subject' => 'Новая заявка с сайта СТО',

    // Ограничение: не более N заявок с одного IP за час
    'rate_limit' => 5,
];
