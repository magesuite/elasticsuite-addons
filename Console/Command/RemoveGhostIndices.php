<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Console\Command;

class RemoveGhostIndices extends \Symfony\Component\Console\Command\Command
{
    public const COMMAND_NAME = 'es:remove_ghosts';
    public const COMMAND_DESCRIPTION = 'Removes ES ghost indices';

    protected \MageSuite\ElasticSuiteAddons\Service\GhostBusterFactory $ghostBusterFactory;

    public function __construct(\MageSuite\ElasticSuiteAddons\Service\GhostBusterFactory $ghostBusterFactory)
    {
        parent::__construct();

        $this->ghostBusterFactory = $ghostBusterFactory;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESCRIPTION);
    }

    /**
     * @throws \Exception
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ): int {
        $service = $this->ghostBusterFactory->create();
        $service->removeGhostIndices();

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
