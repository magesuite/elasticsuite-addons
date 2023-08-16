<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Test\Integration\Plugin\ElasticsuiteThesaurus;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class RewriteQueryIfSearchQueryTextIsTooLongTest extends \PHPUnit\Framework\TestCase
{
    protected const QUERY_TEXT_TYPE_LONG = 'long query';
    protected const QUERY_TEXT_TYPE_SHORT = 'short query';

    protected ?\Smile\ElasticsuiteCore\Api\Cluster\ClusterInfoInterface $clusterInfo;
    protected ?\MageSuite\ElasticSuiteAddons\Service\RewriteQuery $rewriteQuerySerice;
    protected ?\Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->clusterInfo = $this->objectManager->create(\Smile\ElasticsuiteCore\Api\Cluster\ClusterInfoInterface::class);
        $this->request = $this->objectManager->create(\Magento\Framework\App\RequestInterface::class);
        $this->queryFactory = $this->objectManager->create(\Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory::class);

        $this->objectManager->addSharedInstance(
            $this->request,
            \Magento\Framework\App\RequestInterface::class,
            true
        );

        $this->rewriteQuerySerice = $this->objectManager->create(\MageSuite\ElasticSuiteAddons\Service\RewriteQuery::class);
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 5
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/minimum_should_match 75
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/skip_short_numerical_terms 0
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/minimum_numerical_terms_lenght 0
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/tie_breaker 0.3
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/cut_off_frequency 0.15
     * @dataProvider getSearchQueryTexts
     */
    public function testEnabledAddon(string $queryText, string $queryType): void
    {
        $this->request->setParams([
            'q' => $queryText
        ]);

        $query = $this->createQuery($queryText);
        $query = $this->rewriteQuerySerice->processQuery($query);

        $this->assertRewritedQuery($queryType === self::QUERY_TEXT_TYPE_LONG, $query);

        if ($queryType === self::QUERY_TEXT_TYPE_SHORT) {
            return;
        }

        $this->assertRemoveShortNumericalTerms(false, $query, 12345, 6789);
        $this->assertConfiguration($query);
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_autocomplete_settings/term_autocomplete/generate_terms 1
     * @dataProvider getSearchQueryTexts
     */
    public function testDisableGenerateTerms(string $queryText, string $queryType)
    {
        $this->request->setParams([
            'q' => $queryText
        ]);

        $configuration = $this->objectManager->create(\MageSuite\Autocomplete\Helper\Configuration::class);
        $this->assertEquals(
            $queryType === self::QUERY_TEXT_TYPE_LONG ? false : true,
            $configuration->isGenerationEnabled()
        );
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 5
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/skip_short_numerical_terms 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/minimum_numerical_terms_lenght 5
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/tie_breaker 0.3
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/cut_off_frequency 0.15
     * @dataProvider getSearchQueryTexts
     */
    public function testEnabledAddonAndSkipShortNumericalTerms(string $queryText, string $queryType): void
    {
        $this->request->setParam('q', $queryText);

        $query = $this->createQuery($queryText);
        $query = $this->rewriteQuerySerice->processQuery($query);

        $this->assertRewritedQuery($queryType === self::QUERY_TEXT_TYPE_LONG, $query);

        if ($queryType === self::QUERY_TEXT_TYPE_SHORT) {
            return;
        }

        $this->assertRemoveShortNumericalTerms(true, $query, 12345, 6789);
        $this->assertConfiguration($query);
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 0
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 5
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/skip_short_numerical_terms 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/minimum_numerical_terms_lenght 5
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/tie_breaker 0.3
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/cut_off_frequency 0.15
     * @dataProvider getSearchQueryTexts
     */
    public function testDisabledAddon(string $queryText, string $queryType): void
    {
        $query = $this->createQuery($queryText);
        $query = $this->rewriteQuerySerice->processQuery($query);

        $this->assertRewritedQuery(false, $query);
    }

    protected function assertRewritedQuery(
        bool $rewrited,
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query
    ) {
        if ($rewrited) {
            $this->assertEquals($rewrited, $query->getMinimumShouldMatch() === '75%');
        } else {
            $this->assertTrue(method_exists($query, 'getQuery'));
        }
    }

    protected function assertRemoveShortNumericalTerms( //phpcs:ignore
        bool $skipped,
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query,
        int $longNumericalTerm,
        int $shortNumericalTerm
    ) {
        $this->assertStringContainsString((string) $longNumericalTerm, $query->getQueryText());
        if ($skipped) {
            $this->assertStringNotContainsString((string) $shortNumericalTerm, $query->getQueryText());
        } else {
            $this->assertStringContainsString((string) $shortNumericalTerm, $query->getQueryText());
        }
    }

    /**
     * @param \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query
     */
    protected function assertConfiguration(
        \Smile\ElasticsuiteCore\Search\Request\QueryInterface $query
    ) {
        $this->assertEquals('75%', $query->getMinimumShouldMatch());

        $this->assertEquals($this->getExpectedCutoffFrequency(), $query->getCutOffFrequency());
    }

    protected function getExpectedCutoffFrequency()
    {
        $result = 0.15;

        if ($this->clusterInfo->getServerDistribution() === \Smile\ElasticsuiteCore\Api\Cluster\ClusterInfoInterface::DISTRO_ES) {
            if (version_compare($this->clusterInfo->getServerVersion(), "8.0.0") >= 0) {
                $result = 0; // Will be evaluated as false and discarded by the Query Builder.
            }
        } elseif ($this->clusterInfo->getServerDistribution() === \Smile\ElasticsuiteCore\Api\Cluster\ClusterInfoInterface::DISTRO_OS) {
            if (version_compare($$this->clusterInfo->getServerVersion(), "2.0.0") >= 0) {
                $result = 0; // Will be evaluated as false and discarded by the Query Builder.
            }
        }

        return $result;

    }

    public function getSearchQueryTexts(): array
    {
        return [
            self::QUERY_TEXT_TYPE_LONG => [
                'query_text' => 'this is the real long search query 12345 6789',
                'type' => self::QUERY_TEXT_TYPE_LONG
            ],
            self::QUERY_TEXT_TYPE_SHORT => [
                'query_text' => 'search query 12345 6789',
                'type' => self::QUERY_TEXT_TYPE_SHORT
            ]
        ];
    }

    protected function createQuery(string $searchQuery): \Smile\ElasticsuiteCore\Search\Request\QueryInterface
    {
        $queryParams = [
            'fields' => ['name'],
            'queryText' => $searchQuery,
            'minimumShouldMatch' => '100%',
            'tieBreaker' => 0.1,
            'cutoffFrequency' => 1,
            'fuzzinessConfig' => null,
        ];

        $query = $this->queryFactory->create(\Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_MULTIMATCH, $queryParams);

        return $this->queryFactory->create(\Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_FILTER, ['query' => $query]);
    }
}
