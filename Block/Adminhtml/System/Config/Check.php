<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Yireo\SalesBlock2ByGeo\Utils\GeoLookup;

class Check extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Yireo_SalesBlock2ByGeo::config/check.phtml';

    /**
     * @param Context $context
     * @param GeoLookup $geoLookup
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly GeoLookup $geoLookup,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Override to render the template instead of the regular output
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string[]
     */
    public function getRequiredFunctions(): array
    {
        return [
            'geoip_country_code_by_name',
            'geoip_continent_code_by_name',
        ];
    }

    public function hasPhpFunction(string $phpFunction): bool
    {
        return function_exists($phpFunction);
    }

    public function isGeoIp2DatabaseAvailable(): bool
    {
        return $this->geoLookup->isDatabaseAvailable();
    }
}
