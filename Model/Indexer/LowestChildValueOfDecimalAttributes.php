<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Model\Indexer;

class LowestChildValueOfDecimalAttributes
{
    public function __construct(
        protected \MageSuite\ElasticSuiteAddons\Model\ResourceModel\DecimalFilterableAttribute $decimalFilterableAttribute
    ) {
    }

    public function applyToIndexData(array $indexData): array
    {
        $attributeCodes = $this->decimalFilterableAttribute->getAttributeCodes();

        if (empty($attributeCodes)) {
            return $indexData;
        }

        foreach ($indexData as $productId => $productData) {
            $indexData[$productId] = $this->reduceToLowestValue($productData, $attributeCodes);
        }

        return $indexData;
    }

    protected function reduceToLowestValue(array $productData, array $attributeCodes): array
    {
        foreach ($attributeCodes as $attributeCode) {
            if (!isset($productData[$attributeCode]) || !is_array($productData[$attributeCode]) || count($productData[$attributeCode]) < 2) {
                continue;
            }

            $productData[$attributeCode] = [min($productData[$attributeCode])];
        }

        return $productData;
    }
}
