<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    spl_autoload_register(function ($className) {
        require_once __DIR__ . '/classes/' . $className . '.php';
    });

    $telegramMessage = new TelegramMessage($_POST);
    $telegramMessage->sendTelegramMessage();
} else {
    header("HTTP/1.0 404 Not Found");
    echo 'Тут ничего нет';
    exit;
}

// Формат получения данных из Битрикса
/*$_POST = [
    'event' => 'ONCRMLEADADD',
    'data' => [
        'FIELDS' => [
            'ID' => '0000',
        ]
    ],
    'ts' => '0000000000',
    'auth' => [
        'domain' => 'your-bitrix-domain.bitrix24.ru',
        'client_endpoint' => 'https:\/\/your-bitrix-domain.bitrix24.ru\/rest\/',
        'server_endpoint' => 'https:\/\/oauth.bitrix.info\/rest\/',
        'member_id' => '1234567890qwertyuiopasdfghjklzxc',
        'application_token' => '1234567890qwertyuiopasdfghjklzxc'
    ]
];*/