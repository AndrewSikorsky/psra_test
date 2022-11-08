<?php

declare(strict_types=1);

namespace App\Providers;

class BinProvider extends Provider
{
    private const URL = "https://lookup.binlist.net";

    public function getData(string $suffix = ""): array
    {
        if (empty($suffix)) {
            throw new Exception("Suffix cannot be empty");
        }

        return json_decode($this->curlRequest(self::URL . $suffix), true) ?? [];
    }
}