<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData;

class IndexLowestChildValueOfDecimalAttributes
{
    public function __construct(
        protected \MageSuite\ElasticSuiteAddons\Helper\Configuration\LayeredNavigation $configuration,
        protected \MageSuite\ElasticSuiteAddons\Model\Indexer\LowestChildValueOfDecimalAttributes $lowestChildValueOfDecimalAttributes
    ) {
    }

    public function afterAddData(\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData $subject, array $result, int|string $storeId): array
    {
        if (!$this->configuration->isLowestChildValueOfDecimalAttributesIndexed((int)$storeId)) {
            return $result;
        }

        return $this->lowestChildValueOfDecimalAttributes->applyToIndexData($result);
    }
}
