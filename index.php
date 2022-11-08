<?php 

require_once 'vendor/autoload.php';

use App\CurrencyCommissionService;
use App\Providers\BinProvider;
use App\Providers\RateProvider;

// php index.php app_old/input.txt

try {
	$commissionService = new CurrencyCommissionService(
		$argv[1],
		new RateProvider(), //Please look in RateProvider.php for some info about rates url
		new BinProvider()
	);

	$result = $commissionService->getCommissions();

	var_dump($result['commissions']);
	var_dump($result['errors']);
} catch (\Throwable $e) {
	var_dump($e);
}