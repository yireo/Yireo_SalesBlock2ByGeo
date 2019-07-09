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

        if (stristr($ip, $matchPattern)) {
            return true;
        }

        return false;
    }
}

