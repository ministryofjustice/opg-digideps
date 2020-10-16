<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
    private function addSatisfactionScore($score, $comments)
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
            'comments' => 'mustExist',
            'reportType' => 'notEmpty',
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);

        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        $this->persistAndFlush($satisfaction);

        return $satisfaction->getId();
    }

    /**
     * @Route("/satisfaction_data", name="satisfaction_data", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function getSatisfactionData(Request $request)
    {
        /* @var $repo EntityDir\Repository\SatisfactionRepository */
        $repo = $this->getRepository(EntityDir\Satisfaction::class);

        return $repo->findAllSatisfactionSubmissions(
            $this->convertDateArrayToDateTime($request->get('fromDate', [])),
            $this->convertDateArrayToDateTime($request->get('toDate', [])),
            $request->get('orderBy', 'createdAt'),
            $request->get('order', 'ASC')
        );
    }

    /**
     * @Route("/public", methods={"POST"})
     */
    public function publicAdd(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'satisfactionLevel' => 'notEmpty',
            'comments' => 'notEmpty'
        ]);

        $satisfaction = $this->addSatisfactionScore($data['satisfactionLevel'], $data['comments']);

        return $satisfaction->getId();
    }

    /**
     * @param array $date
     * @return \DateTime|null
     * @throws \Exception
     */
    private function convertDateArrayToDateTime(array $date)
    {
        return (isset($date['date'])) ? new \DateTime($date['date']) : null;
    }
}
