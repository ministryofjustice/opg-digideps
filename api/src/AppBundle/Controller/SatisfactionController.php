<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use DateTime;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
    private function addSatisfactionScore($satisfactionLevel, $comments)
    {
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($satisfactionLevel);
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
            $this->convertDateStringToDateTime($request->get('fromDate', '')),
            $this->convertDateStringToDateTime($request->get('toDate', '')),
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
            'score' => 'notEmpty',
            'comments' => 'notEmpty'
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);

        return $satisfaction->getId();
    }

    /**
     * @param array $date
     * @return DateTime|null
     * @throws Exception
     */
    private function convertDateStringToDateTime(string $date)
    {
        return empty($date) ? null : new DateTime($date);
    }
}
