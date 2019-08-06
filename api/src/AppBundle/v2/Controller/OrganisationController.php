<?php

namespace AppBundle\v2\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/organisation")
 */
class OrganisationController
{
    use ControllerTrait;

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
     * @return JsonResponse
     */
    public function createAction()
    {
        return $this->buildSuccessResponse([], 'Organisation created', 201);
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
