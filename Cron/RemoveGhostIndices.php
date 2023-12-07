<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Cron;

class RemoveGhostIndices
{
    protected \MageSuite\ElasticSuiteAddons\Service\GhostBuster $ghostBuster;

    public function __construct(\MageSuite\ElasticSuiteAddons\Service\GhostBuster $ghostBuster)
    {
        $this->ghostBuster = $ghostBuster;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->ghostBuster->removeGhostIndices();
    }
}
