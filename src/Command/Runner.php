<?php

namespace BethlehemIT\Indexer\Command;

use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer as AbstractEntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Runner extends Command
{
    /** @var string */
    protected static $defaultName = 'bit:index:run';

    /** @var EntityIndexerRegistry */
    private EntityIndexerRegistry $registry;

    /** @var iterable */
    private iterable $indexers;

    /**
     * Lister constructor.
     * @param EntityIndexerRegistry $registry
     * @param iterable $indexers
     */
    public function __construct(
        EntityIndexerRegistry $registry,
        iterable $indexers
    ) {
        $this->registry = $registry;
        $this->indexers = $indexers;
        parent::__construct(self::$defaultName);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addArgument(
            "Indexer name",
            InputArgument::REQUIRED,
            "Name of the index, get it from bit:index:list"
        );

        $this->addArgument(
            'ids',
            InputArgument::IS_ARRAY,
            "Optional list of ids to index, not filling it in results in everything getting reindexed"
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument("Indexer name");
        $ids = $input->getArgument("ids");

        if (substr($name, -8,8) !== ".indexer") {
            $name .= ".indexer";
        }

        if (!$this->registry->has($name)) {
            $output->writeln("$name is not a valid indexer!");
            return 1;
        }

        if (empty($ids)) {
            //There is no way to force full reindexing without queue, without breaking abstraction. So yay, breaking the abstraction
            //This could have been /so/ much cleaner as in the else clause
            foreach ($this->indexers as $indexer) {
                /** @var AbstractEntityIndexer $indexer */
                if ($indexer->getName() === $name) {
                    $offset = null;
                    /** @var EntityIndexingMessage $message */
                    while($message = $indexer->iterate($offset)) {
                        $offset = $message->getOffset();
                        $indexer->handle($message);
                    }
                    break;
                }
            }
        } else {
            $message = new EntityIndexingMessage($ids);
            $message->setIndexer($name);
            $this->registry->handle($message);
        }

        return 0;
    }

}
