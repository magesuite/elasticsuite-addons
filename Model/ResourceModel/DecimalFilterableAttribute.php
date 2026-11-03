<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Model\ResourceModel;

class DecimalFilterableAttribute
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
    }

    public function getAttributeCodes(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['attribute' => $this->resourceConnection->getTableName('eav_attribute')], ['attribute_code'])
            ->join(['entity_type' => $this->resourceConnection->getTableName('eav_entity_type')], 'entity_type.entity_type_id = attribute.entity_type_id', [])
            ->join(['catalog_attribute' => $this->resourceConnection->getTableName('catalog_eav_attribute')], 'catalog_attribute.attribute_id = attribute.attribute_id', [])
            ->where('entity_type.entity_type_code = ?', \Magento\Catalog\Model\Product::ENTITY)
            ->where('attribute.attribute_code <> ?', \Magento\Catalog\Api\Data\ProductInterface::PRICE)
            ->where('catalog_attribute.is_filterable > 0 OR catalog_attribute.is_filterable_in_search = 1')
            ->where(sprintf(
                '%s OR (%s AND %s)',
                $connection->quoteInto('attribute.backend_type = ?', 'decimal'),
                $connection->quoteInto('attribute.backend_type = ?', 'varchar'),
                $connection->quoteInto('attribute.frontend_class = ?', 'validate-number')
            ));

        return $connection->fetchCol($select);
    }
}
