<?php

namespace app;

class BotHandlerApp
{
    const TOKEN = '7170757347:AAFCnMS7ovyOzcMUZKI7RUMSTQnwDiANWo0';
    const CENTER_BANK_URL = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    public function getExchangeRates($url)
    {
        $cachefile = 'cache.cache';
        $cachetime = 60;

        if (file_exists($cachefile) && time() - $cachetime <= filemtime($cachefile)) {
            $currentExchangeRate = file_get_contents($cachefile);
        } else {
            unlink($cachefile);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $currentExchangeRate = curl_exec($curl);
            file_put_contents($cachefile, $currentExchangeRate);
        }
        return $currentExchangeRate;
    }

    public function getCurrentDate()
    {
//        date_default_timezone_set('Europe/Saratov');
        return self::CENTER_BANK_URL . date('d/m/Y', time());
    }

    public function converter($arrCurrency, $arr, $resultArr)
    {
        $currentCurrencies = [];
        foreach ($arrCurrency->Valute as $item) {
            if (($resultArr[1] == $item->CharCode || $arr[2] == $item->CharCode)) {
                $currentCurrencies["$item->CharCode"] = $item;
            }
        }
        $convertToRub = ($resultArr[0] / $currentCurrencies[$resultArr[1]]->Nominal) * $currentCurrencies[$resultArr[1]]->Value;
        $out = $convertToRub / $currentCurrencies[$arr[2]]->Value * $currentCurrencies[$arr[2]]->Nominal;
        return sprintf("%.2f", $out);
    }

    public function converterFromRub($arrCurrency, $arr)    {
        foreach ($arrCurrency->Valute as $item) {
            if ($arr[2] == $item->CharCode) {
                $out = $arr[1] / $item->Value * $item->Nominal;
            }
        }
        return sprintf("%.2f", $out);
    }

    public function converterToRub($arrCurrency, $resultArr)
    {
        foreach ($arrCurrency->Valute as $item) {
            if ($resultArr[1] == $item->CharCode) {
                $out = $resultArr[0] / $item->Nominal * $item->Value;
            }
        }
        return sprintf("%.2f", $out);
    }
}