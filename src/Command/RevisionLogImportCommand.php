<?php

namespace DeployTracker\Command;

use DeployTracker\Importer\RevisionLogImporterFactory;
use DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Psr\Log\LoggerAwareInterface;

class RevisionLogImportCommand extends Command
{
    const NAME = 'deploy-tracker:revision-log:import';

    const ARGUMENT_APPLICATION_NAME = 'application-name';
    const ARGUMENT_STAGE = 'stage';
    const ARGUMENT_REVISON_LOG = 'revision-log';

    const OPTION_IMPORTER = 'importer';

    /**
     * @var RevisionLogImporterFactory
     */
    private $importerFactory;

    /**
     * @var ApplicationRepository
     */
    private $repository;

    /**
     * @param RevisionLogImporterFactory $importerFactory
     * @param ApplicationRepository $repository
     */
    public function __construct(RevisionLogImporterFactory $importerFactory, ApplicationRepository $repository)
    {
        $this->importerFactory = $importerFactory;
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
                self::OPTION_IMPORTER,
                null,
                InputOption::VALUE_REQUIRED,
                'The importer to use.',
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
        $importerType = $input->getOption(self::OPTION_IMPORTER);

        $application = $this->repository->findOneByName($applicationName);

        if (null === $application) {
            throw new \RuntimeException(sprintf(
                'Application "%s" does not exist.',
                $applicationName
            ));
        }

        $importer = $this->importerFactory->create($importerType);

        if ($importer instanceof LoggerAwareInterface) {
            $importer->setLogger(new ConsoleLogger($output));
        }

        $importer->import($filename, $application, $stage);

        $output->writeln('<info>Revision log import finished.</info>');
    }
}
