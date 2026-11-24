<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Model\Config\Backend;

use InvalidArgumentException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Yireo\SalesBlock2ByGeo\Utils\DatabasePath;

class GeoIpDatabase extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param DatabasePath $databasePath
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly DatabasePath $databasePath,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        $this->setValue($value);

        if ($value !== '') {
            try {
                $this->databasePath->resolve($value);
            } catch (InvalidArgumentException $exception) {
                throw new LocalizedException(__('Invalid GeoIP database: %1', $exception->getMessage()));
            }
        }

        return parent::beforeSave();
    }
}
