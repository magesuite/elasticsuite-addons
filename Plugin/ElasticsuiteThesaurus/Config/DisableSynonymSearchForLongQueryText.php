<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\ElasticsuiteThesaurus\Config;

class DisableSynonymSearchForLongQueryText extends AbstractConfig
{
    public function aroundIsSynonymSearchEnabled(
        \Smile\ElasticsuiteThesaurus\Config\ThesaurusConfig $subject,
        callable $proceed
    ): bool {
        $config = $this->configuration->getConfig();

        if ($this->queryTextHelper->queryIsLarge($config)) {
            return false;
        }

        return $proceed();
    }
}
