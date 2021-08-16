<?php
declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Model;

class RemoveGhostIndices
{
    const REMOVE_AFTER_DAYS = 2;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Client\ClientInterface
     */
    protected $client;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Index\IndexSettingsInterface
     */
    protected $indexSettings;

    /**
     * @var \Smile\ElasticsuiteCore\Helper\IndexSettings
     */
    protected $indexSettingsHelper;

    /**
     * @param \Smile\ElasticsuiteCore\Api\Client\ClientInterface $client
     * @param \Smile\ElasticsuiteCore\Api\Index\IndexSettingsInterface $indexSettings
     * @param \Smile\ElasticsuiteCore\Helper\IndexSettings $indexSettingsHelper
     */
    public function __construct(
        \Smile\ElasticsuiteCore\Api\Client\ClientInterface $client,
        \Smile\ElasticsuiteCore\Api\Index\IndexSettingsInterface $indexSettings,
        \Smile\ElasticsuiteCore\Helper\IndexSettings $indexSettingsHelper
    ) {
        $this->client = $client;
        $this->indexSettings = $indexSettings;
        $this->indexSettingsHelper = $indexSettingsHelper;
    }

    public function execute(): void
    {
        foreach ($this->client->getIndexAliases() as $indexName => $aliases) {
            $alias = $aliases ? key($aliases['aliases']) : null;

            if (!empty($alias) || !$this->isElasticSuiteIndex($indexName) || $this->isExternalIndex($indexName)) {
                continue;
            }

            $indexDate = $this->getUpdatedDateFromIndexName($indexName, $alias);

            if (!$this->isGhost($indexDate)) {
                continue;
            }

            $this->client->deleteIndex($indexName);
        }
    }

    protected function isElasticSuiteIndex($indexName): bool
    {
        $list = array_keys($this->indexSettings->getIndicesConfig());

        foreach ($list as $elasticSuiteIndex) {
            if (strpos($indexName, $elasticSuiteIndex) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function isExternalIndex($indexName): bool
    {
        $indexAlias = $this->indexSettingsHelper->getIndexAlias();

        return substr($indexName, 0, strlen($indexAlias)) !== $indexAlias;
    }

    protected function getUpdatedDateFromIndexName($indexName, $alias)
    {
        $matches = [];
        $pattern = $this->indexSettingsHelper->getIndicesPattern();
        preg_match_all('/{{([\w]*)}}/', $pattern, $matches);

        if (empty($matches[1])) {
            return false;
        }

        $count = 0;
        $format = '';

        foreach ($matches[1] as $value) {
            $count += strlen($value);
            $format .= $value;
        }

        try {
            $indexName = str_replace($alias, '', $indexName);
            $date = substr(preg_replace('/[^0-9]/', '', $indexName), -$count);

            return new \Zend_Date($date, $format);
        } catch (Zend_Date_Exception $e) {
            return false;
        }
    }

    protected function isGhost($indexDate): bool
    {
        $date = new \Zend_Date();
        $date->sub($indexDate);

        return $date->getTimestamp() / 86400 >= self::REMOVE_AFTER_DAYS;
    }
}
