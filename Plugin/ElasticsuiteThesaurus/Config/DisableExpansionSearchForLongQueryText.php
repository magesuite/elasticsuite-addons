<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\ElasticsuiteThesaurus\Config;

class DisableExpansionSearchForLongQueryText extends AbstractConfig
{
    public function aroundIsExpansionSearchEnabled(
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
