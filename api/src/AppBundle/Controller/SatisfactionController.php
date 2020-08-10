<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
    private function addSatisfactionScore($score,$comments)
    {
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($score);
        $satisfaction->setComments($comments);

        $this->persistAndFlush($satisfaction);

        return $satisfaction;
    }

    /**
     * @Route("", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'notEmpty',
            'reportType' => 'notEmpty',
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'],$data['comments']);

        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        $this->persistAndFlush($satisfaction);

        return $satisfaction->getId();
    }

    /**
     * @Route("/public", methods={"POST"})
     */
    public function publicAdd(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'notEmpty'
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'],$data['comments']);

        return $satisfaction->getId();
    }
}
