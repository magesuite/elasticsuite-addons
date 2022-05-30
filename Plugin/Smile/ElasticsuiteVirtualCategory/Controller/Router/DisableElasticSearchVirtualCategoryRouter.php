<?php

namespace MageSuite\ElasticSuiteAddons\Plugin\Smile\ElasticsuiteVirtualCategory\Controller\Router;

class DisableElasticSearchVirtualCategoryRouter
{
    /**
     * @var \MageSuite\ElasticSuiteAddons\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\ElasticSuiteAddons\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundMatch(
        \Smile\ElasticsuiteVirtualCategory\Controller\Router $router,
        callable $proceed
    ) {
        if ($this->configuration->isVirtualCategoryRouterEnabled()) {
            return $proceed();
        }

        return null;
    }
}
