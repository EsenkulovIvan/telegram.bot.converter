<?php

require __DIR__ . '/vendor/autoload.php';

use app\BotHandlerApp;
use Telegram\Bot\Api;

$botHandlerApp = new BotHandlerApp();
$date = $botHandlerApp->getCurrentDate();
$currentExchangeRate = $botHandlerApp->getExchangeRates($date);
$resultArray = new SimpleXMLElement($currentExchangeRate);

$telegram = new Api(BotHandlerApp::TOKEN);
$message = $telegram->getWebhookUpdate();
$message1 = $message->getMessage();
$text = $message1->getText();
$chat = $message1->getChat()->getId();

$arr = explode(' ', $text);
file_put_contents(__DIR__ . '/logs.txt', print_r($arr, 1), FILE_APPEND);
if ($text === '/start') {
    $message = 'Привет! Желаешь узнать текущий курс валют? Для вывода списка доступных команд введите /help';
    $telegram->sendMessage([
        'chat_id' => $chat,
        'text' => $message,
        'reply_markup' => json_encode([
            'keyboard' => [
                [
                    [
                        'text' => 'Курс валют',
                    ],
                ]
            ],
            'resize_keyboard' => true,
        ])]);
} elseif ($text === '/help') {
    $message = 'Список доступных команд:
        /start - начало работы с ботом
        /info - информация о формате ввода';
    $telegram->sendMessage(['chat_id' => $chat, 'text' => $message]);
} elseif ($text === 'Курс валют') {
    $result = '';
    foreach ($resultArray->Valute as $item) {
        /*if ($text == $item->CharCode) {
            $result = $item->Name;
        }*/
        $result .= "Код валюты: $item->CharCode.\n $item->Nominal $item->Name составляет в рублях $item->Value\n";
    }
    $telegram->sendMessage(['chat_id' => $chat, 'text' => $result]);
} elseif ($arr[0] === 'converter') {
        $result = preg_split('#(?<=\d)(?=[a-zA-Z]+)#i', $arr[1]);
        if ($result[1] == 'RUB') {
            $formatted = $botHandlerApp->converterFromRub($resultArray, $arr);
        } elseif ($arr[2] == 'RUB') {
            $formatted = $botHandlerApp->converterToRub($resultArray, $result);
        } else {
            $formatted = $botHandlerApp->converter($resultArray, $arr, $result);
        }
        $telegram->sendMessage(['chat_id' => $chat, 'text' => $formatted]);
} elseif ($text === '/info') {
    $telegram->sendMessage(['chat_id' => $chat, 'text' => 'Вписать команду "converter" и через пробел ввести данные вида "5USD EUR или 10RUB USD и т.п.']);
} else {
    $telegram->sendMessage(['chat_id' => $chat, 'text' => 'Неверный формат ввода. Введите команду "/info", чтобы увидеть формат ввода.']);
}