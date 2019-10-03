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
    /**
     * @Route("")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'score' => 'notEmpty',
        ]);

        $satisfaction = new Satisfaction();
        $satisfaction->setScore($data['score']);

        if (isset($data['reportType'])) {
            $satisfaction->setReportType($data['reportType']);
            $satisfaction->setDeputyRole($this->getUser()->getRoleName());
        }

        $this->persistAndFlush($satisfaction);

        return $satisfaction->getId();
    }

    /**
     * @Route("/public")
     * @Method({"POST"})
     */
    public function publicAdd(Request $request)
    {
        return $this->add($request);
    }
}
