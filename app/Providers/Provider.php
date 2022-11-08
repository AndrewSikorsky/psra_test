<?php

declare(strict_types=1);

namespace App\Providers;

abstract class Provider
{
    abstract public function getData(string $suffix = ""): array;

    protected function curlRequest(string $url, string $apiKey = ""): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, 
            array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: text/plain",
                    "apikey: $apiKey"
                ),
                CURLOPT_RETURNTRANSFER => true,
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;  
    }
}