<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\ElasticsuiteThesaurus;

class RewriteQueryIfSearchQueryTextIsTooLong
{
    protected \MageSuite\ElasticSuiteAddons\Service\RewriteQuery $rewriteQuerySerice;

    protected array $rewritesCache = [];

    public function __construct(
        \MageSuite\ElasticSuiteAddons\Service\RewriteQuery $rewriteQuerySerice
    ) {
        $this->rewriteQuerySerice = $rewriteQuerySerice;
    }

    public function afterCreate(
        \Smile\ElasticsuiteCore\Search\Request\Query\Fulltext\QueryBuilder $subject,
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query,
        \Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface $containerConfig,
        $queryText,
        $spellingType,
        $boost = 1
    ) {
        $storeId = $containerConfig->getStoreId();
        $requestName = $containerConfig->getName();
        $rewriteCacheKey = $requestName . '|' . $storeId . '|' . md5(json_encode($queryText)); // phpcs:ignore

        if (!isset($this->rewritesCache[$rewriteCacheKey])) {
            $query = $this->rewriteQuerySerice->processQuery($query);
            $this->rewritesCache[$rewriteCacheKey] = $query;
        }

        return $this->rewritesCache[$rewriteCacheKey];
    }
}
