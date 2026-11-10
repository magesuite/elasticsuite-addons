<?php

namespace MageSuite\ElasticSuiteAddons\Plugin\Smile\ElasticsuiteCore\Search\Request\Builder;

class InjectSuggestedTermsAsSearchPhrase
{
    /**
     * @var \Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider
     */
    protected $queryTermsDataProvider;

    /**
     * @var \MageSuite\ElasticSuiteAddons\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface
     */
    protected $search;

    /**
     * @var int[]
     */
    protected $resultsCountCache;

    public function __construct(
        \Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider $queryTermsDataProvider,
        \MageSuite\ElasticSuiteAddons\Helper\Configuration $configuration,
        \Magento\Framework\Search\SearchEngineInterface $search
    ) {
        $this->queryTermsDataProvider = $queryTermsDataProvider;
        $this->configuration = $configuration;
        $this->search = $search;
    }

    public function aroundCreate(
        \Smile\ElasticsuiteCore\Search\Request\Builder $subject,
        $proceed,
        $storeId,
        $containerName,
        $from,
        $size,
        $query = null,
        $sortOrders = [],
        $filters = [],
        $queryFilters = [],
        $facets = []
    ) {
        $result = $proceed(
            $storeId,
            $containerName,
            $from,
            $size,
            $query,
            $sortOrders,
            $filters,
            $queryFilters,
            $facets
        );

        if ($query === null) {
            return $result;
        }

        if ($containerName !== 'quick_search_container') {
            return $result;
        }

        $originalQueryResultsCount = $this->getResultsCountForOriginalQuery($query, fn() => $proceed($storeId, $containerName, 0, 0, $query));

        if (!$this->queryInjectionShouldHappen($result, $originalQueryResultsCount)) {
            return $result;
        }

        $newQuery = array_unique($this->getSearchQueryBasedOnSuggestedPhrases());

        if (empty($newQuery)) {
            return $result;
        }

        return $proceed(
            $storeId,
            $containerName,
            $from,
            $size,
            $newQuery,
            $sortOrders,
            $filters,
            $queryFilters,
            $facets
        );
    }

    protected function getSearchQueryBasedOnSuggestedPhrases()
    {
        $terms = $this->queryTermsDataProvider->getItems();

        $newQuery = [];

        foreach ($terms as $term) {
            $newQuery[] = strip_tags($term->getTitle());
        }

        return $newQuery;
    }

    protected function getResultsCountForOriginalQuery($query, callable $createUnfilteredRequest): int
    {
        $cacheKey = is_array($query) ? implode(' ', $query) : $query;

        if (!isset($this->resultsCountCache[$cacheKey])) {
            $this->resultsCountCache[$cacheKey] = $this->search->search($createUnfilteredRequest())->count();
        }

        return $this->resultsCountCache[$cacheKey];
    }

    protected function queryInjectionShouldHappen(\Smile\ElasticsuiteCore\Search\RequestInterface $result, int $originalQueryResultsCount): bool
    {
        if ($this->configuration->shouldAlwaysInjectPhrases()) {
            return true;
        }

        if ($result->isSpellchecked() && $this->configuration->isInjectingSuggestedPhrasesWhenNoResultsAreFoundEnabled()) {
            return true;
        }

        if ($originalQueryResultsCount <= $this->configuration->getMaximumAmountOfProductsThatTriggerPhraseInjection()) {
            return true;
        }

        return false;
    }
}
