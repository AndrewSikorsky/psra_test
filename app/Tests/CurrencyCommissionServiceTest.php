<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use App\CurrencyCommissionService;
use App\Providers\BinProvider;
use App\Providers\RateProvider;
use App\Providers\Provider;

// vendor/bin/phpunit app/Tests/CurrencyCommissionServiceTest.php

final class CurrencyCommissionServiceTest extends TestCase
{
    private const VALID_VALUES = [
        '{"bin":"4745030","amount":"2000.00","currency":"GBP"}',
        '{"bin":"516793","amount":"50.00","currency":"USD"}'
    ];

    private const INVALID_VALUES = [
        '{"bin":"9179799","amount":"100.00","currency":"EUR"}',
        '{"bin":"516793","amount":"100.00","currency":"XYZ"}'
    ];   

    private CurrencyCommissionService $dataService;
    private string $inputFilePath;

    public function setUp(): void
    {
        $this->inputFilePath = dirname(__DIR__, 3) . "/app_old/input.txt";
    }

    public function tearDown(): void
    {
        \Mockery::close();              
        gc_collect_cycles();
    }

/*  If file not stored on server this test irrelevant

    public function testInputFileExist(): void
    {
        $this->assertFileExists( 
            $this->inputFilePath,
            "File not exist!"
        );
    }
*/
    public function testSuccessCommissions(): void
    {
        $providerMock = $this->createMock(Provider::class);
        $binProviderMock = $this->getMockBuilder(BinProvider::class)
            ->setMethods(null)
            ->getMock();

        $rateProviderMock = $this->getMockBuilder(RateProvider::class)
            ->setMethods(null)
            ->getMock();

        $currencyCommissionServiceMock = $this->getMockBuilder(CurrencyCommissionService::class)
            ->setConstructorArgs(
                [
                    $this->inputFilePath,
                    $rateProviderMock,
                    $binProviderMock
                ]
            )
            ->onlyMethods(['readFile'])
            ->getMock();

        $currencyCommissionServiceMock
            ->expects($this->once())
            ->method('readFile')
            ->will($this->generate(self::VALID_VALUES));

        $result = $currencyCommissionServiceMock->getCommissions();

        $this->assertEmpty($result['errors']);
        $this->assertNotEmpty($result['commissions']);
        $this->assertCount(2, $result['commissions']);

        foreach ($result['commissions'] as $commission) {
            $this->assertNotNull($commission);
            $this->assertIsFloat($commission);
        }
    }

    public function testFailCommissions(): void
    {
        $providerMock = $this->createMock(Provider::class);
        $binProviderMock = $this->getMockBuilder(BinProvider::class)
            ->setMethods(null)
            ->getMock();

        $rateProviderMock = $this->getMockBuilder(RateProvider::class)
            ->setMethods(null)
            ->getMock();

        $currencyCommissionServiceMock = $this->getMockBuilder(CurrencyCommissionService::class)
            ->setConstructorArgs(
                [
                    $this->inputFilePath,
                    $rateProviderMock,
                    $binProviderMock
                ]
            )
            ->onlyMethods(['readFile'])
            ->getMock();

        $currencyCommissionServiceMock
            ->expects($this->once())
            ->method('readFile')
            ->will($this->generate(self::INVALID_VALUES));

        $result = $currencyCommissionServiceMock->getCommissions();

        $this->assertEmpty($result['commissions']);
        $this->assertNotEmpty($result['errors']);
        $this->assertNotEmpty($result['errors']['invalidBins']);
        $this->assertNotEmpty($result['errors']['invalidCurrencies']);

        $this->assertCount(1, $result['errors']['invalidBins']);
        $this->assertCount(1, $result['errors']['invalidCurrencies']);

        $this->assertEquals("XYZ", reset($result['errors']['invalidCurrencies']));
        $this->assertEquals("9179799", reset($result['errors']['invalidBins']));
    }

    private function generate(array $yieldValues): ReturnCallback
    {
        return $this->returnCallback(function() use ($yieldValues) {
            foreach ($yieldValues as $value) {
                yield $value;
            }
        });
    }
}
