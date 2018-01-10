<?php

namespace DeployTracker\Controller;

use DeployTracker\Repository\DeploymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchController extends Controller
{
    use PageAwareTrait;

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request, DeploymentRepository $repository): Response
    {
        $searchQuery = $request->query->get('q', '');

        if (!$searchQuery) {
            throw new NotFoundHttpException();
        }

        $page = $this->getPage($request);
        $filters = $repository->getFiltersFromRequest($request);
        $results = $repository->search($searchQuery, $page, $filters);
        $maxPage = $repository->getMaxPage($results);

        $this->validatePaging($request, $maxPage);

        return $this->render('search/index.html.twig', [
            'results' => $results->getIterator(),
            'totalResults' => $results->count(),
            'page' => $page,
            'maxPage' => $maxPage,
            'searchQuery' => $searchQuery,
            'filters' => $filters,
        ]);
    }
}
