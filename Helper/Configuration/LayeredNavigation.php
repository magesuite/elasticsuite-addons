<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Helper\Configuration;

class LayeredNavigation
{
    public const XML_PATH_INDEX_LOWEST_CHILD_VALUE_OF_DECIMAL_ATTRIBUTES = 'catalog/catalog_filters/index_lowest_child_value_of_decimal_attributes';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isLowestChildValueOfDecimalAttributesIndexed(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_INDEX_LOWEST_CHILD_VALUE_OF_DECIMAL_ATTRIBUTES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
