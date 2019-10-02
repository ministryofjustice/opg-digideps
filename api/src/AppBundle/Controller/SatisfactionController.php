<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
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

        $satisfaction = new Satisfaction();
        $satisfaction->setScore($data['score']);
        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        if (isset($data['comments'])) {
            $satisfaction->setComments($data['comments']);
        }

        $this->persistAndFlush($satisfaction);

        return $satisfaction->getId();
    }
}
