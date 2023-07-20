<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Service;

class RewriteQuery
{
    protected \MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration $configuration;
    protected \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory;
    protected \MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper $queryTextHelper;

    public function __construct(
        \MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration $configuration,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper $queryTextHelper
    ) {
        $this->configuration = $configuration;
        $this->queryFactory = $queryFactory;
        $this->queryTextHelper = $queryTextHelper;
    }

    public function processQuery(
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query
    ): \Smile\ElasticsuiteCore\Search\Request\QueryInterface {

        if ($this->queryShouldBeOverwrited($query) === true) {
            $queryText = $this->getQueryText(
                $query->getQuery()->getQueryText()
            );
            $query = $this->getQueryForLargeQueryText($queryText, $query);
        }

        return $query;
    }

    public function getQueryForLargeQueryText(
        string $queryText,
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query
    ): \Smile\ElasticsuiteCore\Search\Request\QueryInterface {

        $queryParams = [
            'fields' => $query->getQuery()->getFields(),
            'queryText' => $this->removeSpecialChars($queryText),
            'minimumShouldMatch' => $this->configuration->getMinimumShouldMatch(),
            'tieBreaker' => $this->configuration->getTieBreaker(),
            'cutoffFrequency' => $this->configuration->getCutOffFrequency(),
            'fuzzinessConfig' => null,
        ];

        return $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_MULTIMATCH,
            $queryParams
        );
    }

    public function getQueryText($queryText): string
    {
        $queryText = is_array($queryText) ? $queryText[0] ?? '' : $queryText;

        if ($this->configuration->isSkippingOfShortNumericalTermsEnabled() === false) {
            return $queryText;
        }

        return $this->skipShortNumericalTerms($queryText);
    }

    protected function skipShortNumericalTerms($queryText): string
    {
        $minLenght = $this->configuration->getMinimumNumericalTermsLenght();
        $terms = explode(' ', $queryText);

        foreach ($terms as $key => $term) {
            if (is_numeric($term) && strlen($term) < $minLenght) {
                unset($terms[$key]);
            }
        }

        return implode(' ', $terms);
    }

    protected function queryShouldBeOverwrited(\Smile\ElasticsuiteCore\Search\Request\QueryInterface $query): bool
    {
        $config = $this->configuration->getConfig();

        return $this->queryTextHelper->queryIsLarge($config)
            && $query instanceof \Smile\ElasticsuiteCore\Search\Request\Query\Filtered;
    }

    protected function removeSpecialChars(string $queryText): string
    {
        return preg_replace(['/[^a-zA-Z0-9\-\s]/i', '/\s\s/i'], '', $queryText);
    }
}
