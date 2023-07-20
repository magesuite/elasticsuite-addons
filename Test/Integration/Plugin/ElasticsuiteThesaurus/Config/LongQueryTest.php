<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Test\Integration\Plugin\ElasticsuiteThesaurus\Config;

/**
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class LongQueryTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration $configuration;
    protected ?\Magento\Framework\ObjectManagerInterface $objectManager;
    protected ?\Magento\Framework\App\RequestInterface $request;
    protected ?\Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory;
    protected ?\MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper $queryTextHelper;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->request = $this->objectManager->create(\Magento\Framework\App\RequestInterface::class);
        $this->queryFactory = $this->objectManager->create(\Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory::class);

        $this->objectManager->addSharedInstance(
            $this->request,
            \Magento\Framework\App\RequestInterface::class,
            true
        );

        $this->configuration = $this->objectManager->create(\MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration::class);
        $this->queryTextHelper = $this->objectManager->create(\MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper::class);
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 5
     */
    public function testLongQuery(): void
    {
        $this->request->setParams([
            'q' => 'this is the real long search query'
        ]);

        $config = $this->configuration->getConfig();
        $this->assertTrue($this->queryTextHelper->queryIsLarge($config));
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 0
     */
    public function testLongQueryAndNotSetQueryWordsLimit(): void
    {
        $this->request->setParams([
            'q' => 'this is the real long search query'
        ]);

        $config = $this->configuration->getConfig();
        $this->assertFalse($this->queryTextHelper->queryIsLarge($config));
    }

    /**
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/is_enabled 1
     * @magentoConfigFixture current_store smile_elasticsuite_optimization_configuration/general/query_words_limit 5
     */
    public function testShortQuery(): void
    {
        $this->request->setParams([
            'q' => 'search query'
        ]);

        $config = $this->configuration->getConfig();
        $this->assertFalse($this->queryTextHelper->queryIsLarge($config));
    }
}
