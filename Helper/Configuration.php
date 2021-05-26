<?php

namespace MageSuite\ElasticSuiteAddons\Helper;

class Configuration
{
    const ELASTICSUITE_SEARCH_SETTINGS_XML_PATH = 'smile_elasticsuite_catalogsearch_settings/catalogsearch';

    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface) {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function shouldAlwaysInjectPhrases()
    {
        $config = $this->getConfig();

        return (boolean)$config['always_inject_suggested_phrases'];
    }

    public function getMaximumAmountOfProductsThatTriggerPhraseInjection()
    {
        $config = $this->getConfig();

        return (int)$config['maximum_amount_of_products_that_trigger_phrase_injection'];
    }

    public function isInjectingSuggestedPhrasesWhenNoResultsAreFoundEnabled()
    {
        $config = $this->getConfig();

        return (boolean)$config['inject_suggested_phrases_when_no_results_are_found'];
    }

    private function getConfig()
    {
        if(!$this->config){
            $this->config = $this->scopeConfig->getValue(
                self::ELASTICSUITE_SEARCH_SETTINGS_XML_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->config;
    }
}
