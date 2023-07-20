<?php

namespace MageSuite\ElasticSuiteAddons\Helper;

class SearchOptimizationConfiguration extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected const XML_PATH_SEARCH_OPTIMIZATION_CONFIG = 'smile_elasticsuite_optimization_configuration/general';

    protected ?\Magento\Framework\DataObject $config = null;

    public function getConfig(): \Magento\Framework\DataObject
    {
        if ($this->config === null) {
            $this->config = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(self::XML_PATH_SEARCH_OPTIMIZATION_CONFIG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }
        return $this->config;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getConfig()->getIsEnabled();
    }

    public function isSkippingOfShortNumericalTermsEnabled(): bool
    {
        return $this->getConfig()->getSkipShortNumericalTerms() && $this->getMinimumNumericalTermsLenght() > 0;
    }

    public function getMinimumNumericalTermsLenght(): int
    {
        return (int) $this->getConfig()->getMinimumNumericalTermsLenght();
    }

    public function getQueryWordsLimit(): int
    {
        return (int) $this->getConfig()->getQueryWordsLimit();
    }

    public function getMinimumShouldMatch(): string
    {
        return (int) $this->getConfig()->getMinimumShouldMatch() . '%';
    }

    public function getTieBreaker(): float
    {
        return (float) $this->getConfig()->getTieBreaker();
    }

    public function getCutOffFrequency(): float
    {
        return (float) $this->getConfig()->getCutOffFrequency();
    }
}
