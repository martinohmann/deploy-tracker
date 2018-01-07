<?php

namespace DeployTracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use DeployTracker\Form\Util\FormErrorCollector;
use DeployTracker\Entity\PublishRequest;
use DeployTracker\Form\Type\PublishRequestType;
use DeployTracker\Handler\PublishRequestHandler;

class ApiController extends Controller
{
    /**
     * @param Request $request
     * @param FormErrorCollector $formErrorCollector
     * @param PublishRequestHandler $publishRequestHandler
     * @return Response
     */
    public function publish(
        Request $request,
        FormErrorCollector $formErrorCollector,
        PublishRequestHandler $publishRequestHandler
    ): Response {
        $publishRequest = new PublishRequest();
        $form = $this->createForm(PublishRequestType::class, $publishRequest);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            $data = ['errors' => $formErrorCollector->collectErrors($form)];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        try {
            $publishRequestHandler->handle($publishRequest);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
    }
}
