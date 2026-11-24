<?php declare(strict_types=1);

/**
 * Yireo SalesBlock2ByGeo for Magento
 *
 * @package     Yireo_SalesBlock2ByGeo
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\SalesBlock2ByGeo\Matcher;

use Yireo\SalesBlock2\Api\MatcherInterface;
use Yireo\SalesBlock2\Exception\NoMatchException;
use Yireo\SalesBlock2\Helper\Data;
use Yireo\SalesBlock2\Logger\Debugger;
use Yireo\SalesBlock2\RuleMatch\RuleMatch;
use Yireo\SalesBlock2ByGeo\Utils\CurrentIp;
use Yireo\SalesBlock2ByGeo\Utils\GeoLookup;
use Yireo\SalesBlock2ByGeo\Utils\GeoMatcher;

class Matcher implements MatcherInterface
{
    public function __construct(
        private readonly CurrentIp $currentIp,
        private readonly GeoMatcher $geoMatcher,
        private readonly GeoLookup $geoLookup,
        private readonly Data $helper,
        private readonly Debugger $debugger
    ) {
    }

    public function getCode(): string
    {
        return 'geo';
    }

    public function getName(): string
    {
        return 'Geo location';
    }

    public function getDescription(): string
    {
        return 'Match by geo location';
    }

    /**
     * @throws NoMatchException
     */
    public function match(string $matchString): RuleMatch
    {
        $matchStrings = $this->helper->stringToArray($matchString);
        $currentIp = $this->currentIp->getValue();

        foreach ($matchStrings as $matchString) {
            if (!$this->geoMatcher->match($currentIp, $matchString)) {
                continue;
            }

            $country = $this->geoLookup->lookup($currentIp)['country'];
            $this->debugger->debug(
                sprintf('IP %s (country "%s") matched geo pattern %s', $currentIp, $country, $matchString)
            );

            $match = new RuleMatch((string)__('Sales are not available in your region'));
            $match->setVariables(['ip' => $currentIp, 'country' => $country]);
            return $match;
        }

        throw new NoMatchException(__('No match found'));
    }
}
