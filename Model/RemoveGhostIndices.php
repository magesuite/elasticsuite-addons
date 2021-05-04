<?php
declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Model;

class RemoveGhostIndices
{
    /**
     * @var \Smile\ElasticsuiteIndices\Model\Index\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Smile\ElasticsuiteIndices\Model\IndexStatsProvider
     */
    protected $indexStatsProvider;

    public function __construct(
        \Smile\ElasticsuiteIndices\Model\Index\CollectionFactory $collectionFactory,
        \Smile\ElasticsuiteIndices\Model\IndexStatsProvider $indexStatsProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->indexStatsProvider = $indexStatsProvider;
    }

    /**
     * Remove indices without alias older than 2 days
     * @see \Smile\ElasticsuiteIndices\Model\IndexStatusProvider::isGhost
     */
    public function execute(): void
    {
        $collection = $this->collectionFactory->create();

        foreach ($collection as $index) {
            if ($index->getIndexStatus() != \Smile\ElasticsuiteIndices\Block\Widget\Grid\Column\Renderer\IndexStatus::GHOST_STATUS) {
                continue;
            }

            $this->indexStatsProvider->deleteIndex($index->getIndexName());
        }
    }
}
