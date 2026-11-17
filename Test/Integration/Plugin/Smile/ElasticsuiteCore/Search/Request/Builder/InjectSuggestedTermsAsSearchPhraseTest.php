<?php

namespace MageSuite\ElasticSuiteAddons\Test\Integration\Plugin\Smile\ElasticsuiteCore\Search\Request\Builder;

class InjectSuggestedTermsAsSearchPhraseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->queryFactory = $this->objectManager->get(\Magento\Search\Model\QueryFactory ::class);
        $this->indexer = $this->objectManager->get(\Magento\Framework\Indexer\IndexerInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ElasticSuiteAddons::Test/Integration/_files/products_search.php
     */
    public function testItInjectsWhenThereAreLowAmountOfResultsForOriginalPhrase()
    {
        $products = $this->search('polo');
        $skus = array_map(
            function ($product) {
                return $product->getSku();
            },
            $products
        );

        $this->assertCount(3, $products);
        $this->assertContains('poloshirt', $skus);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ElasticSuiteAddons::Test/Integration/_files/products_search.php
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/maximum_amount_of_products_that_trigger_phrase_injection 1
     */
    public function testItDoesNotInjectWhenThereAreEnoughOriginalResults()
    {
        $products = $this->search('polo');
        $skus = array_map(
            function ($product) {
                return $product->getSku();
            },
            $products
        );

        $this->assertCount(2, $products);
        $this->assertNotContains('poloshirt', $skus);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ElasticSuiteAddons::Test/Integration/_files/products_search.php
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/maximum_amount_of_products_that_trigger_phrase_injection 1
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/always_inject_suggested_phrases 1
     */
    public function testItAlwaysInjectsWhenSettingIsEnabled()
    {
        $products = $this->search('polo');
        $skus = array_map(
            function ($product) {
                return $product->getSku();
            },
            $products
        );

        $this->assertCount(3, $products);
        $this->assertContains('poloshirt', $skus);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ElasticSuiteAddons::Test/Integration/_files/products_search_only_poloshirt.php
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/maximum_amount_of_products_that_trigger_phrase_injection 1
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/always_inject_suggested_phrases 0
     * @magentoConfigFixture current_store smile_elasticsuite_catalogsearch_settings/catalogsearch/inject_suggested_phrases_when_no_results_are_found 1
     */
    public function testItInjectsWhenNoResultsWereFound()
    {
        $this->indexer->load('catalogsearch_fulltext')->reindexAll();
        $products = $this->search('polo');
        $skus = array_map(
            function ($product) {
                return $product->getSku();
            },
            $products
        );

        $this->assertCount(1, $products);
        $this->assertContains('poloshirt', $skus);
    }

    #[\Magento\TestFramework\Fixture\AppArea('frontend')]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_ElasticSuiteAddons::Test/Integration/_files/products_search.php')]
    #[\Magento\TestFramework\Fixture\Config('smile_elasticsuite_catalogsearch_settings/catalogsearch/maximum_amount_of_products_that_trigger_phrase_injection', 1, 'store', 'default')]
    public function testItIgnoresLayeredNavigationFiltersWhenDecidingAboutInjection(): void
    {
        $products = $this->search('polo', null, ['entity_id' => [101, 103]]);
        $skus = array_map(fn($product) => $product->getSku(), $products);

        $this->assertSame(['polo'], $skus);
    }

    protected function search(string $text, $visibilityFilter = null, array $layeredNavigationFilters = []): array
    {
        $query = $this->queryFactory->get();

        $query->unsetData();
        $query->setQueryText($text);
        $query->saveIncrementalPopularity();

        $searchLayer = $this->objectManager->create(\Magento\Catalog\Model\Layer\Search::class);

        $collection = $searchLayer->getProductCollection();
        $collection->addSearchFilter($text);

        if (null !== $visibilityFilter) {
            $collection->setVisibility($visibilityFilter);
        }

        foreach ($layeredNavigationFilters as $field => $condition) {
            $collection->addFieldToFilter($field, $condition);
        }

        $products = [];

        foreach ($collection as $product) {
            $products[] = $product;
        }

        return $products;
    }
}
