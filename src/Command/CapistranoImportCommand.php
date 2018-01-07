<?php

namespace DeployTracker\Command;

use DeployTracker\Importer\CapistranoRevisionLogImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class CapistranoImportCommand extends Command
{
    const NAME = 'deploy-tracker:capistrano:import';

    const ARGUMENT_APPLICATION_NAME = 'application-name';
    const ARGUMENT_STAGE = 'stage';
    const ARGUMENT_REVISON_LOG = 'revision-log';

    /**
     * @var CapistranoRevisionLogImporter
     */
    protected $importer;

    /**
     * @param CapistranoRevisionLogImporter $importer
     */
    public function __construct(CapistranoRevisionLogImporter $importer)
    {
        $this->importer = $importer;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Imports deployment information from a Capistrano revision log file.')
            ->setHelp("This command imports deployment information from a Capistrano revision log file.\n" .
                'Keep in mind that this will not produce very accurate results for rollbacks as ' .
                "these do not contain date information.\n" .
                'In this case an estimated rollback date will be calculated based on the previous ' .
                "and the next deployment, if possible.\n" .
                'Also, failed deployments are not included.')
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

        $this->importer->setLogger(new ConsoleLogger($output));
        $this->importer->import($filename, $applicationName, $stage);
    }
}
