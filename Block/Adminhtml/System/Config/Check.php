<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Check extends Field
{
    /**
     * Override to set a different PHTML template
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('config/check.phtml');

        return $this;
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
            'geoip_country_code3_by_name',
            'geoip_continent_code_by_name',
        ];
    }

    /**
     * Check if GD supports WebP
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