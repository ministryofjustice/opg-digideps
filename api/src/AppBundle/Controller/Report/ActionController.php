<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ActionController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    private array $sectionIds = [EntityDir\Report\Report::SECTION_ACTIONS];

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/report/{reportId}/action", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $action = $report->getAction();
        if (!$action) {
            $action = new EntityDir\Report\Action($report);
            $this->em->persist($action);
        }

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $action);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $action->getId()];
    }

    /**
     * @Route("/report/{reportId}/action", methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $action = $this->findEntityBy(EntityDir\Report\Action::class, $id, 'Action with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($action->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['action'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $action;
    }

    /**
     * @param array                   $data
     * @param EntityDir\Report\Action $action
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\Action $action)
    {
        if (array_key_exists('do_you_expect_financial_decisions', $data)) {
            $action->setDoYouExpectFinancialDecisions($data['do_you_expect_financial_decisions']);
        }

        if (array_key_exists('do_you_expect_financial_decisions_details', $data)) {
            $action->setDoYouExpectFinancialDecisionsDetails($data['do_you_expect_financial_decisions_details']);
        }

        if (array_key_exists('do_you_have_concerns', $data)) {
            $action->setDoYouHaveConcerns($data['do_you_have_concerns']);
        }

        if (array_key_exists('do_you_have_concerns_details', $data)) {
            $action->setDoYouHaveConcernsDetails($data['do_you_have_concerns_details']);
        }

        $action->cleanUpUnusedData();

        return $action;
    }
}
