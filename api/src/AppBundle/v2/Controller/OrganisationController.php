<?php

namespace AppBundle\v2\Controller;

use AppBundle\Service\RestHandler\OrganisationRestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/organisation")
 */
class OrganisationController
{
    use ControllerTrait;

    /** @var OrganisationRestHandler */
    private $restHandler;

    /**
     * @param OrganisationRestHandler $restHandler
     */
    public function __construct(OrganisationRestHandler $restHandler)
    {
        $this->restHandler = $restHandler;
    }

    /**
     * @Route("/list")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function getAllAction()
    {
        return $this->buildSuccessResponse(['foo' => 'bar']);
    }

    /**
     * @Route("")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $entity = $this->restHandler->create($data);

        return $this->buildSuccessResponse(['id' => $entity->getId()], 'Organisation created', 201);
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"PUT"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function updateAction($id)
    {
        return $this->buildSuccessResponse([], 'Organisation updated' . $id, 204);
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"DELETE"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        return $this->buildSuccessResponse([], 'Organisation deleted' . $id);
    }
}
