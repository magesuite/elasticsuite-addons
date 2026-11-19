<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Helper;

class QueryTextHelper
{
    protected \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function queryIsLarge(\Magento\Framework\DataObject $config): bool
    {
        if ($config->getIsEnabled() == false) {
            return false;
        }

        $words = preg_match_all('/[\p{L}\p{M}\'\-]+/u', strip_tags((string) $this->request->getParam('q')));

        return $words > $config->getQueryWordsLimit() && $config->getQueryWordsLimit() > 0;
    }
}
