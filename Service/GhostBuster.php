<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Service;

class GhostBuster
{
    protected \Smile\ElasticsuiteIndices\Model\IndexStatsProvider $indexStatsProvider;

    public function __construct(
        \Smile\ElasticsuiteIndices\Model\IndexStatsProvider $indexStatsProvider
    ) {
        $this->indexStatsProvider = $indexStatsProvider;
    }

    /**
     * @throws \Exception
     */
    public function removeGhostIndices(): void
    {
        $indices = $this->indexStatsProvider->getElasticSuiteIndices();

        foreach ($indices as $name => $alias) {
            if ($this->isIndexGhost($name, $alias)) {
                $this->indexStatsProvider->deleteIndex($name);
            }
        }
    }

    protected function isIndexGhost(string $name, string $alias): bool
    {
        $indexStats = $this->indexStatsProvider->indexStats($name, $alias);
        $status = $indexStats['index_status'] ?? null;

        return $status === \Smile\ElasticsuiteIndices\Block\Widget\Grid\Column\Renderer\IndexStatus::GHOST_STATUS;
    }
}
