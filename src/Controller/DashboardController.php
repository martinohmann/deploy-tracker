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
    use FilterAwareTrait;

    const FILTER_PARAMS = ['deployer', 'stage', 'status'];

    /**
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function index(DeploymentRepository $repository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'success' => $repository->findByStatus(Deployment::STATUS_SUCCESS),
            'failed' => $repository->findByStatus(Deployment::STATUS_FAILED),
            'rollback' => $repository->findByStatus(Deployment::STATUS_ROLLBACK),
            'countsSuccess' => $repository->findCountsByStatus(Deployment::STATUS_SUCCESS),
            'countsFailed' => $repository->findCountsByStatus(Deployment::STATUS_FAILED),
            'countsRollback' => $repository->findCountsByStatus(Deployment::STATUS_ROLLBACK),
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
        $filters = $this->getFilters($request, self::FILTER_PARAMS);
        $deployments = $repository->findMostRecent($page, $filters);
        $maxPage = $this->getMaxPage($deployments, $repository);

        if ($this->shouldRedirectToMaxPage($page, $maxPage)) {
            return $this->redirectToMaxPage($request, $maxPage);
        }

        return $this->render('dashboard/recent.html.twig', [
            'deployments' => $deployments->getIterator(),
            'page' => $page,
            'maxPage' => $maxPage,
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
        $filters = $this->getFilters($request, self::FILTER_PARAMS);
        $deployments = $repository->findAll($page, $filters);
        $maxPage = $this->getMaxPage($deployments, $repository);

        if ($this->shouldRedirectToMaxPage($page, $maxPage)) {
            return $this->redirectToMaxPage($request, $maxPage);
        }

        return $this->render('dashboard/history.html.twig', [
            'deployments' => $deployments->getIterator(),
            'page' => $page,
            'maxPage' => $maxPage,
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
        $applications = $repository->findAll($page);
        $maxPage = $this->getMaxPage($applications, $repository);

        if ($this->shouldRedirectToMaxPage($page, $maxPage)) {
            return $this->redirectToMaxPage($request, $maxPage);
        }

        return $this->render('dashboard/applications.html.twig', [
            'applications' => $applications->getIterator(),
            'page' => $page,
            'maxPage' => $maxPage,
        ]);
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
        $filters = $this->getFilters($request, self::FILTER_PARAMS);
        $deployments = $deploymentRepository->findAllForApplication($application, $page, $filters);
        $maxPage = $this->getMaxPage($applications, $deploymentRepository);

        if ($this->shouldRedirectToMaxPage($page, $maxPage)) {
            return $this->redirectToMaxPage($request, $maxPage);
        }

        return $this->render('dashboard/application.html.twig', [
            'application' => $application,
            'deployments' => $deployments->getIterator(),
            'page' => $page,
            'maxPage' => $maxPage,
            'filters' => $filters,
        ]);
    }
}
