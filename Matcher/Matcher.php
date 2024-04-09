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
use Yireo\SalesBlock2\RuleMatch\RuleMatch;
use Yireo\SalesBlock2ByGeo\Utils\CurrentIp;
use Yireo\SalesBlock2ByGeo\Utils\GeoMatcher;

/**
 * Class Matcher
 * @package Yireo\SalesBlock2ByGeo\Matcher
 */
class Matcher implements MatcherInterface
{
    /**
     * @var CurrentIp
     */
    private $currentIp;

    /**
     * @var GeoMatcher
     */
    private $geoMatcher;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Matcher constructor.
     * @param CurrentIp $currentIp
     * @param GeoMatcher $geoMatcher
     * @param Data $helper
     */
    public function __construct(
        CurrentIp $currentIp,
        GeoMatcher $geoMatcher,
        Data $helper
    ) {
        $this->currentIp = $currentIp;
        $this->geoMatcher = $geoMatcher;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'geo';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Geo location';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Match by geo location';
    }

    /**
     * @param string $matchString
     * @return RuleMatch
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

            $message = sprintf('Matched location with %s', $matchString);

            $match = new RuleMatch($message);
            $match->setVariables(['geo' => $currentIp]);
            return $match;
        }

        throw new NoMatchException(__('No match found'));
    }
}
