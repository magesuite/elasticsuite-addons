<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\ViewModel\Layer;

class DisplayFiltersConfiguration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \MageSuite\ElasticSuiteAddons\Helper\Configuration $configuration;

    public function __construct(
        \MageSuite\ElasticSuiteAddons\Helper\Configuration $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function canDisplayFilters($filter)
    {
        $minOptionsQty = $this->configuration->getMinOptionsQtyToShowFilters();
        if ($minOptionsQty == 0) {
            return true;
        }
        return $filter->getItemsCount() >= $minOptionsQty;
    }
}
