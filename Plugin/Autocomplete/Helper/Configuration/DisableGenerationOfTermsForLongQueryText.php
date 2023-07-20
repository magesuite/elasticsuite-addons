<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\Autocomplete\Helper\Configuration;

class DisableGenerationOfTermsForLongQueryText extends \MageSuite\ElasticSuiteAddons\Plugin\ElasticsuiteThesaurus\Config\AbstractConfig
{
    public function aroundIsGenerationEnabled(
        \MageSuite\Autocomplete\Helper\Configuration $subject,
        callable $proceed
    ): bool {
        $config = $this->configuration->getConfig();

        if ($this->queryTextHelper->queryIsLarge($config)) {
            return false;
        }

        return (bool) $proceed();
    }
}
