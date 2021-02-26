<?php

namespace BethlehemIT\Indexer\Command;

use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer as AbstractEntityIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Lister extends Command
{
    /** @var string */
    protected static $defaultName = 'bit:index:list';

    /** @var iterable */
    private iterable $indexers;

    /**
     * Lister constructor.
     * @param iterable $indexers
     */
    public function __construct(
        iterable $indexers
    ) {
        $this->indexers = $indexers;
        parent::__construct(self::$defaultName);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AbstractEntityIndexer $indexer */
        foreach ($this->indexers as $indexer) {
            $output->writeln($indexer->getName());
        }

        return 0;
    }
}
