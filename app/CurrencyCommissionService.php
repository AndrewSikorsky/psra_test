<?php

declare(strict_types=1);

namespace App;

use App\Providers\Provider;

class CurrencyCommissionService
{
    //can be used yaml cfg for this
    private const EU_LIST = [
      'AT','BE','BG','CY','CZ','DE',
      'DK','EE','ES','FI','FR','GR',
      'HR','HU','IE','IT','LT','LU',
      'LV','MT','NL','PO','PT','RO',
      'SE','SI','SK',
    ];

    private const EU_INDEX = 0.01;
    private const INDEX = 0.02;

    private string $inputFileName;
    private Provider $currencyRatesProvider;
    private Provider $binProvider;

    public function __construct(
        string $inputFileName,
        Provider $currencyRatesProvider,
        Provider $binProvider
    ) {
        $this->inputFileName = $inputFileName;
        $this->currencyRatesProvider = $currencyRatesProvider;
        $this->binProvider = $binProvider;
    }

    public function getCommissions(): array
    {
        if (empty($this->inputFileName)) {
            throw new \Exception("Empty file");
        }

        $commissions = [];
        $errors = [];

        $rateData = $this->currencyRatesProvider->getData();
        foreach ($this->readFile($this->inputFileName) as $encodedLine) {
            $line = json_decode($encodedLine, true);

            $binData = $this->binProvider->getData("/" . $line["bin"]);
            if (empty($binData["country"]["alpha2"])) {
                $errors['invalidBins'][] = $line["bin"];
                continue;
            }
            
            if (empty($rateData[$line["currency"]])) {
                $errors['invalidCurrencies'][] = $line["currency"];
                continue;
            }

            $isEu = in_array($binData["country"]["alpha2"], self::EU_LIST);
            $currencyRate = $rateData[$line["currency"]];

            $amntFixed = $line["amount"];
            if ($line["currency"] != 'EUR' || $currencyRate != 0) {
                $amntFixed = $line["amount"] / $currencyRate;
            }

            $commissions[] = round($amntFixed * ($isEu ? self::EU_INDEX : self::INDEX), 2);
        }

        return [
          'commissions' => $commissions,
          'errors' => $errors
        ];
    }

    protected function readFile(string $filename): \Generator
    {
        $file = fopen($filename, 'r');
        
        while (($line = fgets($file)) !== false) {
          yield $line;
        }

        fclose($file);
    }
}
