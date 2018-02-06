<?php

namespace DeployTracker\Command;

use DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use DeployTracker\Entity\RevisionLog;
use DeployTracker\Processor\RevisionLogProcessorFactory;
use DeployTracker\Importer\RevisionLogImporterInterface;

class RevisionLogImportCommand extends Command
{
    const NAME = 'deploy-tracker:revision-log:import';

    const ARGUMENT_APPLICATION_NAME = 'application-name';
    const ARGUMENT_STAGE = 'stage';
    const ARGUMENT_REVISON_LOG = 'revision-log';

    const OPTION_PROCESSOR = 'processor';

    /**
     * @var RevisionLogImporterInterface
     */
    private $importer;

    /**
     * @var RevisionLogProcessorFactory
     */
    private $processorFactory;

    /**
     * @var ApplicationRepository
     */
    private $repository;

    /**
     * @param RevisionLogImporterInterface $importer
     * @param RevisionLogProcessorFactory $processorFactory
     * @param ApplicationRepository $repository
     */
    public function __construct(
        RevisionLogImporterInterface $importer,
        RevisionLogProcessorFactory $processorFactory,
        ApplicationRepository $repository
    ) {
        $this->importer = $importer;
        $this->processorFactory = $processorFactory;
        $this->repository = $repository;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Imports deployment history from a revision log file.')
            ->setHelp('This command imports deployment history from a revision log file.')
            ->addArgument(
                self::ARGUMENT_APPLICATION_NAME,
                InputArgument::REQUIRED,
                'The name of the application to import the revision log for.'
            )
            ->addArgument(
                self::ARGUMENT_STAGE,
                InputArgument::REQUIRED,
                'The stage of the revision log.'
            )
            ->addArgument(
                self::ARGUMENT_REVISON_LOG,
                InputArgument::REQUIRED,
                'The path to the revision log file to import.'
            )
            ->addOption(
                self::OPTION_PROCESSOR,
                null,
                InputOption::VALUE_REQUIRED,
                'The revision log processor to use.',
                'capistrano'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $applicationName = $input->getArgument(self::ARGUMENT_APPLICATION_NAME);
        $stage = $input->getArgument(self::ARGUMENT_STAGE);
        $filename = $input->getArgument(self::ARGUMENT_REVISON_LOG);
        $processorType = $input->getOption(self::OPTION_PROCESSOR);

        $application = $this->repository->findOneByName($applicationName);

        if (null === $application) {
            throw new \RuntimeException(sprintf(
                'Application "%s" does not exist.',
                $applicationName
            ));
        }

        $revisionLog = new RevisionLog($application, $stage, $filename);

        $this->processorFactory
            ->create($processorType)
            ->process($revisionLog);

        $this->importer->import($revisionLog);

        $output->writeln(sprintf(
            '<info>%d entries imported.</info>',
            $revisionLog->getDeployments()->count()
        ));
    }
}
