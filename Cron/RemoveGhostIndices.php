<?php
declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Cron;

class RemoveGhostIndices
{
    /**
     * @var \MageSuite\ElasticSuiteAddons\Model\RemoveGhostIndices
     */
    protected $removeGhostIndices;

    public function __construct(\MageSuite\ElasticSuiteAddons\Model\RemoveGhostIndices $removeGhostIndices)
    {
        $this->removeGhostIndices = $removeGhostIndices;
    }

    public function execute()
    {
        $this->removeGhostIndices->execute();
    }
}
