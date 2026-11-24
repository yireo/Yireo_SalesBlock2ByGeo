<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Unit\Utils;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2\Logger\Debugger;
use Yireo\SalesBlock2ByGeo\Config\Config;
use Yireo\SalesBlock2ByGeo\Utils\DatabasePath;
use Yireo\SalesBlock2ByGeo\Utils\GeoLookup;

class GeoLookupTest extends TestCase
{
    private string $databaseFile = '';

    protected function tearDown(): void
    {
        if ($this->databaseFile !== '' && file_exists($this->databaseFile)) {
            unlink($this->databaseFile);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function getInvalidIps(): array
    {
        return [
            'empty' => [''],
            'garbage' => ['foo'],
            'with port' => ['1.2.3.4:80'],
            'header list' => ['1.2.3.4, 5.6.7.8'],
        ];
    }

    #[DataProvider('getInvalidIps')]
    public function testInvalidIpIsUnknown(string $ip): void
    {
        $databasePath = $this->createMock(DatabasePath::class);
        $databasePath->expects($this->never())->method('resolve');

        $geoLookup = new GeoLookup($this->getConfig('geoip/country.mmdb'), $databasePath, $this->getDebugger());
        $this->assertSame(['country' => '', 'continent' => ''], $geoLookup->lookup($ip));
    }

    public function testInvalidDatabasePathIsUnknown(): void
    {
        if (function_exists('geoip_country_code_by_name')) {
            $this->markTestSkipped('GeoIP v1 extension is used as fallback');
        }

        $databasePath = $this->createMock(DatabasePath::class);
        $databasePath->method('resolve')->willThrowException(new InvalidArgumentException('Invalid'));

        $geoLookup = new GeoLookup($this->getConfig('../../etc/passwd'), $databasePath, $this->getDebugger());
        $this->assertSame(['country' => '', 'continent' => ''], $geoLookup->lookup('8.8.8.8'));
        $this->assertFalse($geoLookup->isDatabaseAvailable());
    }

    public function testCorruptDatabaseDoesNotThrow(): void
    {
        if (function_exists('geoip_country_code_by_name')) {
            $this->markTestSkipped('GeoIP v1 extension is used as fallback');
        }

        $this->databaseFile = sys_get_temp_dir() . '/salesblock2bygeo-' . uniqid() . '.mmdb';
        file_put_contents($this->databaseFile, 'not a maxmind database');

        $databasePath = $this->createMock(DatabasePath::class);
        $databasePath->method('resolve')->willReturn($this->databaseFile);

        $debugger = $this->getDebugger();
        $debugger->expects($this->atLeastOnce())->method('debug');

        $geoLookup = new GeoLookup($this->getConfig('geoip/country.mmdb'), $databasePath, $debugger);
        $this->assertSame(['country' => '', 'continent' => ''], $geoLookup->lookup('8.8.8.8'));
    }

    private function getConfig(string $database): Config
    {
        $config = $this->createMock(Config::class);
        $config->method('getCountryDatabase')->willReturn($database);

        return $config;
    }

    /**
     * @return Debugger&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getDebugger(): Debugger
    {
        return $this->createMock(Debugger::class);
    }
}
