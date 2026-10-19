<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Check extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Yireo_SalesBlock2ByGeo::config/check.phtml';

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
            'geoip_country_code3_by_name',
            'geoip_continent_code_by_name',
        ];
    }

    /**
     * Check if a PHP function exists
     *
     * @return bool
     */
    public function hasPhpFunction(string $phpFunction): bool
    {
        if (!function_exists($phpFunction)) {
            return false;
        }

        return true;
    }
}
