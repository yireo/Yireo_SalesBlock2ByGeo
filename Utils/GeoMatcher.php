<?php declare(strict_types = 1);

/**
 * Yireo SalesBlock2ByGeo for Magento
 *
 * @package     Yireo_SalesBlock2ByGeo
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\SalesBlock2ByGeo\Utils;

class GeoMatcher
{
    public function __construct(
        private readonly GeoLookup $geoLookup,
        private readonly CountryCodeConverter $countryCodeConverter
    ) {
    }

    /**
     * Supported patterns: an exact IP, a two-letter country code (NL), a three-letter country code (NLD)
     * or a two-letter continent code with prefix (continent:EU). An unknown location never matches.
     */
    public function match(string $ip, string $matchPattern): bool
    {
        $matchPattern = trim($matchPattern, " \f\n\r\t\v\x00");
        if ($ip === '' || $matchPattern === '') {
            return false;
        }

        if ($ip === $matchPattern) {
            return true;
        }

        if (preg_match('/^continent:\s*([a-z]{2})$/i', $matchPattern, $matches)) {
            return $this->matchByContinentCode($ip, $matches[1]);
        }

        if (preg_match('/^[a-z]{2}$/i', $matchPattern)) {
            return $this->matchByTwoLetterCountryCode($ip, $matchPattern);
        }

        if (preg_match('/^[a-z]{3}$/i', $matchPattern)) {
            return $this->matchByThreeLetterCountryCode($ip, $matchPattern);
        }

        return false;
    }

    public function matchByTwoLetterCountryCode(string $ip, string $countryCode): bool
    {
        return $this->isEqual($this->geoLookup->lookup($ip)['country'], $countryCode);
    }

    public function matchByThreeLetterCountryCode(string $ip, string $countryCode): bool
    {
        $iso2Code = $this->countryCodeConverter->toIso2($countryCode);
        if ($iso2Code === '') {
            return false;
        }

        return $this->matchByTwoLetterCountryCode($ip, $iso2Code);
    }

    public function matchByContinentCode(string $ip, string $continentCode): bool
    {
        return $this->isEqual($this->geoLookup->lookup($ip)['continent'], $continentCode);
    }

    private function isEqual(string $value, string $pattern): bool
    {
        if ($value === '' || strlen($pattern) !== 2) {
            return false;
        }

        return strtoupper($value) === strtoupper($pattern);
    }
}
