<?php

namespace DeployTracker\Command;

use DeployTracker\Entity\Application;
use DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationCreateCommand extends Command
{
    const NAME = 'deploy-tracker:application:create';

    const ARGUMENT_NAME = 'name';
    const ARGUMENT_PROJECT_URL = 'project-url';

    /**
     * @var ApplicationRepository
     */
    private $repository;

    /**
     * @param ApplicationRepository $repository
     */
    public function __construct(ApplicationRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Create entry for application in the database.')
            ->addArgument(
                self::ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The name of the application.'
            )
            ->addArgument(
                self::ARGUMENT_PROJECT_URL,
                InputArgument::REQUIRED,
                'The project url.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::ARGUMENT_NAME);
        $projectUrl = $input->getArgument(self::ARGUMENT_PROJECT_URL);

        if (null !== $this->repository->findOneByName($name)) {
            throw new \RuntimeException(sprintf(
                'Application with name "%s" already exists.',
                $name
            ));
        }

        $application = new Application();
        $application->setName($name)
            ->setProjectUrl($projectUrl);

        $this->repository->persist($application);

        $output->writeln(sprintf(
            '<info>Application "%s" created.</info>',
            $name
        ));
    }
}
