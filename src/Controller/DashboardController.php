<?php

namespace DeployTracker\Controller;

use DeployTracker\Entity\Deployment;
use DeployTracker\Histogram\DateHistogramFactory;
use DeployTracker\Manager\ApplicationManager;
use DeployTracker\Manager\DeploymentManager;
use DeployTracker\Repository\ApplicationRepository;
use DeployTracker\Repository\DeploymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    /**
     * @param DeploymentRepository $repository
     * @param DateHistogramFactory $factory
     * @return Response
     */
    public function index(DeploymentRepository $repository, DateHistogramFactory $factory): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'success' => $repository->findLastByStatus(Deployment::STATUS_SUCCESS),
            'failed' => $repository->findLastByStatus(Deployment::STATUS_FAILED),
            'rollback' => $repository->findLastByStatus(Deployment::STATUS_ROLLBACK),
            'stats' => $repository->aggregateDeploymentStats(),
            'topDeployers' => $repository->findTopDeployers(),
            'lastYearHistogram' => $factory->createMonthlyDeploymentHistogram(
                $repository,
                new \DateTime('-1 year'),
                new \DateTime()
            ),
            'lastWeekHistogram' => $factory->createDailyDeploymentHistogram(
                $repository,
                new \DateTime('-1 week'),
                new \DateTime()
            ),
        ]);
    }

    /**
     * @param DeploymentManager $manager
     * @return Response
     */
    public function recent(DeploymentManager $manager): Response
    {
        $result = $manager->findMostRecent();

        return $this->render('dashboard/recent.html.twig', [
            'paginator' => $result->getPaginator(),
            'filters' => $result->getFilters(),
        ]);
    }

    /**
     * @param DeploymentManager $manager
     * @return Response
     */
    public function history(DeploymentManager $manager): Response
    {
        $result = $manager->findAll();

        return $this->render('dashboard/history.html.twig', [
            'paginator' => $result->getPaginator(),
            'filters' => $result->getFilters(),
        ]);
    }

    /**
     * @param ApplicationManager $manager
     * @return Response
     */
    public function applications(ApplicationManager $manager): Response
    {
        return $this->render('dashboard/applications.html.twig', [
            'paginator' => $manager->findAll()->getPaginator(),
        ]);
    }

    /**
     * @param int $id
     * @param ApplicationRepository $repository
     * @param DeploymentManager $manager
     * @return Response
     */
    public function application(int $id, ApplicationRepository $repository, DeploymentManager $manager): Response
    {
        if (null === ($application = $repository->findOneById($id))) {
            throw new NotFoundHttpException();
        }

        $result = $manager->findByApplication($application);

        return $this->render('dashboard/application.html.twig', [
            'application' => $application,
            'paginator' => $result->getPaginator(),
            'filters' => $result->getFilters(),
        ]);
    }

    /**
     * @param Request $request
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function deployers(DeploymentManager $manager): Response
    {
        return $this->render('dashboard/deployers.html.twig', [
            'paginator' => $manager->findDeployers()->getPaginator(),
        ]);
    }
}
