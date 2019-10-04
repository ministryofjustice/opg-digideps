<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
    private function addSatisfactionScore($score)
    {
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($score);

        $this->persistAndFlush($satisfaction);

        return $satisfaction;
    }

    /**
     * @Route("")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'reportType' => 'notEmpty',
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score']);

        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        $this->persistAndFlush($satisfaction);

        return $satisfaction->getId();
    }

    /**
     * @Route("/public")
     * @Method({"POST"})
     */
    public function publicAdd(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'score' => 'notEmpty'
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score']);

        return $satisfaction->getId();
    }
}
