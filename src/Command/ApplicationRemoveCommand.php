<?php

namespace DeployTracker\Command;

use DeployTracker\Entity\Application;
use DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ApplicationRemoveCommand extends Command
{
    const NAME = 'deploy-tracker:application:remove';

    const ARGUMENT_NAME = 'name';

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
            ->setDescription('Remove application and all its deployments from the database.')
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

        $application = $this->repository->findOneByName($name);

        if (null === $application) {
            throw new \RuntimeException(sprintf(
                'Application with name "%s" does not exist.',
                $name
            ));
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf(
            'Remove entry for application "%s" and all associated deployments? [y/N] ',
            $name
        ), false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $deploymentCount = $application->getDeployments()->count();

        $this->repository->remove($application);

        $output->writeln(sprintf(
            '<info>Application "%s" and all %d associated deployments removed.</info>',
            $name,
            $deploymentCount
        ));
    }
}
