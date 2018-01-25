<?php

namespace DeployTracker\Controller;

use DeployTracker\Manager\DeploymentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchController extends Controller
{
    /**
     * @param Request $request
     * @param DeploymentManager $manager
     * @return Response
     */
    public function index(Request $request, DeploymentManager $manager): Response
    {
        $searchQuery = trim((string) $request->query->get('q'));

        if (!$searchQuery) {
            throw new NotFoundHttpException();
        }

        $result = $manager->search($searchQuery);

        return $this->render('search/index.html.twig', [
            'paginator' => $result->getPaginator(),
            'filters' => $result->getFilters(),
            'searchQuery' => $searchQuery,
        ]);
    }
}
