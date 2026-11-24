<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Unit\Utils;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2ByGeo\Utils\CountryCodeConverter;
use Yireo\SalesBlock2ByGeo\Utils\GeoLookup;
use Yireo\SalesBlock2ByGeo\Utils\GeoMatcher;

class GeoMatcherTest extends TestCase
{
    private const NL_IP = '145.0.0.1';
    private const UNKNOWN_IP = '10.0.0.1';

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function getPatterns(): array
    {
        return [
            'country' => [self::NL_IP, 'NL', true],
            'country lowercase' => [self::NL_IP, 'nl', true],
            'country with whitespace' => [self::NL_IP, " NL\n", true],
            'other country' => [self::NL_IP, 'DE', false],
            'iso3 country' => [self::NL_IP, 'NLD', true],
            'iso3 country lowercase' => [self::NL_IP, 'nld', true],
            'other iso3 country' => [self::NL_IP, 'DEU', false],
            'unknown iso3 country' => [self::NL_IP, 'XYZ', false],
            'continent' => [self::NL_IP, 'continent:EU', true],
            'continent lowercase' => [self::NL_IP, 'Continent:eu', true],
            'continent with space' => [self::NL_IP, 'continent: EU', true],
            'other continent' => [self::NL_IP, 'continent:AF', false],
            'continent code without prefix is a country' => [self::NL_IP, 'EU', false],
            'invalid continent' => [self::NL_IP, 'continent:EUR', false],
            'exact ip' => [self::NL_IP, self::NL_IP, true],
            'empty pattern' => [self::NL_IP, '', false],
            'garbage pattern' => [self::NL_IP, 'N1', false],
            'four letters' => [self::NL_IP, 'NLDE', false],
            'pattern with trailing junk' => [self::NL_IP, "NL\nfoo", false],
            'unknown location country' => [self::UNKNOWN_IP, 'NL', false],
            'unknown location iso3' => [self::UNKNOWN_IP, 'NLD', false],
            'unknown location continent' => [self::UNKNOWN_IP, 'continent:EU', false],
            'empty ip' => ['', 'NL', false],
            'empty ip and pattern' => ['', '', false],
        ];
    }

    #[DataProvider('getPatterns')]
    public function testMatch(string $ip, string $pattern, bool $expected): void
    {
        $this->assertSame($expected, $this->getGeoMatcher()->match($ip, $pattern));
    }

    private function getGeoMatcher(): GeoMatcher
    {
        $geoLookup = $this->createMock(GeoLookup::class);
        $geoLookup->method('lookup')->willReturnCallback(
            fn (string $ip) => $ip === self::NL_IP
                ? ['country' => 'NL', 'continent' => 'EU']
                : ['country' => '', 'continent' => '']
        );

        $countryCodeConverter = $this->createMock(CountryCodeConverter::class);
        $countryCodeConverter->method('toIso2')->willReturnCallback(
            fn (string $code) => ['NLD' => 'NL', 'DEU' => 'DE'][strtoupper($code)] ?? ''
        );

        return new GeoMatcher($geoLookup, $countryCodeConverter);
    }
}
