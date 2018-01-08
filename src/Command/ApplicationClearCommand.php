<?php

namespace DeployTracker\Command;

use DeployTracker\Entity\Application;
use DeployTracker\Repository\ApplicationRepository;
use DeployTracker\Repository\DeploymentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ApplicationClearCommand extends Command
{
    const NAME = 'deploy-tracker:application:clear';

    const ARGUMENT_NAME = 'name';

    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param ApplicationRepository $repository
     */
    public function __construct(
        ApplicationRepository $applicationRepository,
        DeploymentRepository $deploymentRepository
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->deploymentRepository = $deploymentRepository;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Clear deployments for application.')
            ->addArgument(
                self::ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The name of the application.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::ARGUMENT_NAME);

        $application = $this->applicationRepository->findOneByName($name);

        if (null === $application) {
            throw new \RuntimeException(sprintf(
                'Application with name "%s" does not exist.',
                $name
            ));
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf(
            'Remove all deployments for application "%s"? [y/N] ',
            $name
        ), false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $deployments = $application->getDeployments();
        $deploymentCount = $deployments->count();

        $this->deploymentRepository->removeCollection($deployments);

        $output->writeln(sprintf(
            '<info>%d deployments removed.</info>',
            $deploymentCount
        ));
    }
}
