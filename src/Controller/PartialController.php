<?php

namespace Lesara\DeployTracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lesara\DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\HttpFoundation\Response;

class PartialController extends Controller
{
    /**
     * @param ApplicationRepository $repository
     * @return Response
     */
    public function applicationDropdown(ApplicationRepository $repository): Response
    {
        return $this->render('partial/application-dropdown.html.twig', [
            'applications' => $repository->findAll(),
        ]);
    }
}
