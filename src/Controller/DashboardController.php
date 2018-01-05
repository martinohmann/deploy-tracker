<?php

namespace Lesara\DeployTracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Lesara\DeployTracker\Repository\DeploymentRepository;
use Lesara\DeployTracker\Repository\ApplicationRepository;

class DashboardController extends Controller
{
    /**
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function index(DeploymentRepository $repository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'deployments' => $repository->findMostRecent(),
        ]);
    }

    /**
     * @param DeploymentRepository $repository
     * @return Response
     */
    public function history(DeploymentRepository $repository): Response
    {
        return $this->render('dashboard/history.html.twig', [
            'deployments' => $repository->findAll(),
        ]);
    }

    /**
     * @param int $id
     * @param ApplicationRepository $applicationRepository
     * @param DeploymentRepository $deploymentRepository
     * @return Response
     */
    public function application(
        int $id,
        ApplicationRepository $applicationRepository,
        DeploymentRepository $deploymentRepository
    ): Response {
        return $this->render('dashboard/application.html.twig', [
            'application' => $applicationRepository->findOneById($id),
            'deployments' => $deploymentRepository->findByApplicationId($id),
        ]);
    }
}
