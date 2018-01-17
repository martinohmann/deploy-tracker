<?php

namespace DeployTracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use DeployTracker\Repository\DeploymentRepository;
use DeployTracker\Repository\ApplicationRepository;
use DeployTracker\Entity\Deployment;
use DeployTracker\Controller\PageAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    use PageAwareTrait;

    /**
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function index(DeploymentRepository $repository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'success' => $repository->findLastSuccessful(),
            'failed' => $repository->findLastFailed(),
            'rollback' => $repository->findLastRollbacks(),
            'countsSuccess' => $repository->findSuccessfulCounts(),
            'countsFailed' => $repository->findFailedCounts(),
            'countsRollback' => $repository->findRollbackCounts(),
            'deploymentStats' => $repository->getDeploymentStats(),
            'topDeployers' => $repository->getTopDeployers(),
        ]);
    }
    /**
     * @param Request $request
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function recent(Request $request, DeploymentRepository $repository): Response
    {
        $page = $this->getPage($request);
        $filters = $repository->getFiltersFromRequest($request);
        $paginator = $repository->findMostRecent($page, $filters);

        $this->validatePagination($request, $paginator);

        return $this->render('dashboard/recent.html.twig', [
            'paginator' => $paginator,
            'filters' => $filters,
        ]);
    }

    /**
     * @param Request $request
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function history(Request $request, DeploymentRepository $repository): Response
    {
        $page = $this->getPage($request);
        $filters = $repository->getFiltersFromRequest($request);
        $paginator = $repository->findAll($page, $filters);

        $this->validatePagination($request, $paginator);

        return $this->render('dashboard/history.html.twig', [
            'paginator' => $paginator,
            'filters' => $filters,
        ]);
    }

    /**
     * @param Request $request
     * @param ApplicationRepository $repository
     * @return Response
     */
    public function applications(Request $request, ApplicationRepository $repository): Response
    {
        $page = $this->getPage($request);
        $paginator = $repository->findAll($page);

        $this->validatePagination($request, $paginator);

        return $this->render('dashboard/applications.html.twig', ['paginator' => $paginator]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param ApplicationRepository $applicationRepository
     * @param DeploymentRepository $deploymentRepository
     * @return Response
     */
    public function application(
        Request $request,
        int $id,
        ApplicationRepository $applicationRepository,
        DeploymentRepository $deploymentRepository
    ): Response {
        $application = $applicationRepository->findOneById($id);

        if (null === $application) {
            throw new NotFoundHttpException();
        }

        $page = $this->getPage($request);
        $filters = $deploymentRepository->getFiltersFromRequest($request);
        $paginator = $deploymentRepository->findByApplication($application, $page, $filters);

        $this->validatePagination($request, $paginator);

        return $this->render('dashboard/application.html.twig', [
            'application' => $application,
            'paginator' => $paginator,
            'filters' => $filters,
        ]);
    }

    /**
     * @param Request $request
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function deployerStats(Request $request, DeploymentRepository $repository): Response
    {
        $page = $this->getPage($request);
        $paginator = $repository->getDeployerStats($page);

        $this->validatePagination($request, $paginator);

        return $this->render('dashboard/deployer-stats.html.twig', ['paginator' => $paginator]);
    }
}
