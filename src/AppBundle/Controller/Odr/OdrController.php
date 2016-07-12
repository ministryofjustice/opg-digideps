<?php

namespace AppBundle\Controller\Odr;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Controller\RestController;

class OdrController extends RestController
{
    /**
     * @Route("/odr/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $groups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['odr'];
        $this->setJmsSerialiserGroups($groups);

        //$this->getRepository('Odr\Odr')->warmUpArrayCacheTransactionTypes();

        $report = $this->findEntityBy('Odr\Odr', $id);
        /* @var $report EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($report);

        return $report;
    }

    /**
     * @Route("/odr/{id}/submit")
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $id, 'Odr not found');
        /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $data = $this->deserializeBodyContent($request);

        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException('Missing submit_date');
        }

//        if (empty($data['agreed_behalf_deputy'])) {
//            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
//        }

//        $currentReport->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
//        if ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf') {
//            $currentReport->setAgreedBehalfDeputyExplanation($data['agreed_behalf_deputy_explanation']);
//        } else {
//            $currentReport->setAgreedBehalfDeputyExplanation(null);
//        }

        $odr->setSubmitted(true);
        $odr->setSubmitDate(new \DateTime($data['submit_date']));
        $this->getEntityManager()->flush($odr);

        return [];
    }
}
