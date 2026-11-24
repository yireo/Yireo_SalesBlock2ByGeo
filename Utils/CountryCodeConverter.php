<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Utils;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Throwable;

class CountryCodeConverter implements ResetAfterRequestInterface
{
    /**
     * @var array<string, string>
     */
    private array $iso2Codes = [];

    public function __construct(
        private readonly CountryFactory $countryFactory
    ) {
    }

    public function toIso2(string $iso3Code): string
    {
        $iso3Code = strtoupper($iso3Code);
        if (!preg_match('/^[A-Z]{3}$/', $iso3Code)) {
            return '';
        }

        if (!isset($this->iso2Codes[$iso3Code])) {
            $this->iso2Codes[$iso3Code] = $this->loadIso2Code($iso3Code);
        }

        return $this->iso2Codes[$iso3Code];
    }

    public function _resetState(): void
    {
        $this->iso2Codes = [];
    }

    private function loadIso2Code(string $iso3Code): string
    {
        try {
            $country = $this->countryFactory->create()->loadByCode($iso3Code);
        } catch (Throwable) {
            return '';
        }

        return strtoupper((string)$country->getData('iso2_code'));
    }
}
