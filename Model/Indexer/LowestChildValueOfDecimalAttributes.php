<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Model\Indexer;

class LowestChildValueOfDecimalAttributes
{
    public function __construct(
        protected \MageSuite\ElasticSuiteAddons\Model\ResourceModel\DecimalFilterableAttribute $decimalFilterableAttribute,
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Indexer\Fulltext\Datasource\AttributeData $attributeDataResource,
        protected \Smile\ElasticsuiteCatalog\Helper\ProductAttribute $attributeHelper
    ) {
    }

    public function applyToIndexData(array $indexData, int $storeId): array
    {
        $attributeCodes = $this->decimalFilterableAttribute->getAttributeCodes();
        $childIds = $this->getChildIds($indexData);

        if (empty($attributeCodes) || empty($childIds)) {
            return $indexData;
        }

        $childValues = $this->getChildValues($storeId, $childIds, $attributeCodes);

        foreach ($indexData as $productId => $productData) {
            if (empty($productData['children_ids'])) {
                continue;
            }

            $indexData[$productId] = $this->applyLowestChildValues($productData, $childValues, $attributeCodes);
        }

        return $indexData;
    }

    protected function getChildIds(array $indexData): array
    {
        $childIds = array_merge(...array_values(array_map(fn (array $productData): array => $productData['children_ids'] ?? [], $indexData)));

        return array_values(array_unique($childIds));
    }

    protected function getChildValues(int $storeId, array $childIds, array $attributeCodes): array
    {
        $childValues = [];

        foreach ($this->getAttributeIdsByBackendTable($attributeCodes) as $backendTable => $attributeIds) {
            foreach ($this->attributeDataResource->getAttributesRawData($storeId, $childIds, $backendTable, $attributeIds) as $row) {
                $childId = (int)$row['entity_id'];
                $childValues[$childId] = ($childValues[$childId] ?? []) + $this->attributeHelper->prepareIndexValue($row['attribute_id'], $storeId, $row['value']);
            }
        }

        return $childValues;
    }

    protected function getAttributeIdsByBackendTable(array $attributeCodes): array
    {
        $attributeIdsByBackendTable = [];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $attributeIdsByBackendTable[$attribute->getBackendTable()][] = (int)$attribute->getId();
        }

        return $attributeIdsByBackendTable;
    }

    protected function applyLowestChildValues(array $productData, array $childValues, array $attributeCodes): array
    {
        foreach ($attributeCodes as $attributeCode) {
            $values = $this->getValuesOfChildren($productData['children_ids'], $childValues, $attributeCode);

            if (empty($values)) {
                continue;
            }

            $productData[$attributeCode] = [min($values)];
        }

        return $productData;
    }

    protected function getValuesOfChildren(array $childIds, array $childValues, string $attributeCode): array
    {
        return array_merge(...array_map(fn (int|string $childId): array => $childValues[$childId][$attributeCode] ?? [], $childIds));
    }
}
