<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getCountryDatabase(): string
    {
        return (string)$this->scopeConfig->getValue('salesblock/settings/geoip_country_database');
    }
}
