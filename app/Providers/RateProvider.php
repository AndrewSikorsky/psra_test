<?php

declare(strict_types=1);

namespace App\Providers;

class RateProvider extends Provider
{
    // "https://api.exchangeratesapi.io/latest"; 
    // This url is not working. It needs api-key, and api-key received from apilayer is not valid for some reason
    // Maybe because of it the commission values are little bit different from default
    // I used https://api.apilayer.com/exchangerates_data/latest from documentation after receiving the api-key
    private const URL = "https://api.apilayer.com/exchangerates_data/latest";

    // API_KEY from apilayer.com where exchangeratesapi.io sends you to get the api-key
    // Its free key for testing purposes from temp mail, so its ok to keep it here like this
    private const API_KEY = "keahWJ55cQ7uOUaWoOLyhqeYiKBagRkb"; 

    public function getData(string $suffix = ""): array
    {
        $rawData = json_decode($this->curlRequest(self::URL, self::API_KEY), true);
        if (empty($rawData["rates"])) {
            throw new Exception("Cant get rates!");
        }

        return $rawData["rates"];
    }
}
