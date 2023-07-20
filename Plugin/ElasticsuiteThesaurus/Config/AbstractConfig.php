<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\ElasticsuiteThesaurus\Config;

abstract class AbstractConfig
{
    protected \MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration $configuration;
    protected \MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper $queryTextHelper;

    public function __construct(
        \MageSuite\ElasticSuiteAddons\Helper\SearchOptimizationConfiguration $configuration,
        \MageSuite\ElasticSuiteAddons\Helper\QueryTextHelper $queryTextHelper
    ) {
        $this->configuration = $configuration;
        $this->queryTextHelper = $queryTextHelper;
    }
}
