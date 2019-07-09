<?php
/**
 * Yireo SalesBlock2ByGeo for Magento
 *
 * @package     Yireo_SalesBlock2ByGeo
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types = 1);

namespace Yireo\SalesBlock2ByGeo\Utils;

/**
 * Class GeoMatcher
 * @package Yireo\SalesBlock2ByGeo\Utils
 */
class GeoMatcher
{
    /**
     * @param string $ip
     * @param string $matchPattern
     * @return bool
     */
    public function match(string $ip, string $matchPattern): bool
    {
        if ($ip === $matchPattern) {
            return true;
        }

        if ($this->matchByTwoLetterCountryCode($ip, $matchPattern)) {
            return true;
        }

        if ($this->matchByThreeLetterCountryCode($ip, $matchPattern)) {
            return true;
        }

        if ($this->matchByTwoLetterContinentCode($ip, $matchPattern)) {
            return true;
        }

        return false;
    }

    public function matchByTwoLetterCountryCode(string $ip, string $matchPattern): bool
    {
        if (strlen($matchPattern) !== 2) {
            return false;
        }

        if (!function_exists('geoip_country_code_by_name')) {
            return false;
        }

        if (strtolower(geoip_country_code_by_name($ip)) !== strtolower($matchPattern)) {
            return false;
        }

        return true;
    }

    public function matchByThreeLetterCountryCode(string $ip, string $matchPattern): bool
    {
        if (strlen($matchPattern) !== 3) {
            return false;
        }

        if (!function_exists('geoip_country_code3_by_name')) {
            return false;
        }

        if (strtolower(geoip_country_code3_by_name($ip)) !== strtolower($matchPattern)) {
            return false;
        }

        return true;
    }

    public function matchByTwoLetterContinentCode(string $ip, string $matchPattern): bool
    {
        if (strlen($matchPattern) !== 2) {
            return false;
        }

        if (!function_exists('geoip_continent_code_by_name')) {
            return false;
        }

        if (strtolower(geoip_continent_code_by_name($ip)) !== strtolower($matchPattern)) {
            return false;
        }

        return true;
    }
}

