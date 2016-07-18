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
     * //TODO merge into update action and update client.
     *
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

        $odr->setSubmitted(true);
        $odr->setSubmitDate(new \DateTime($data['submit_date']));
        $this->getEntityManager()->flush($odr);

        return [];
    }

    /**
     * @Route("/odr/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $id, 'Odr not found');
        /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $data = $this->deserializeBodyContent($request);

        if (array_key_exists('has_debts', $data) && in_array($data['has_debts'], ['yes', 'no'])) {
            $odr->setHasDebts($data['has_debts']);
            // null debts
            foreach ($odr->getDebts() as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
                $this->getEntityManager()->flush($debt);
            }
            // set debts as per "debts" key
            foreach ($data['debts'] as $row) {
                $debt = $odr->getDebtByTypeId($row['debt_type_id']);
                if (!$debt instanceof EntityDir\Odr\Debt) {
                    continue; //not clear when that might happen. kept similar to transaction below
                }
                $debt->setAmount($row['amount']);
                $debt->setMoreDetails($debt->getHasMoreDetails() ? $row['more_details'] : null);
                $this->getEntityManager()->flush($debt);
                $this->setJmsSerialiserGroups(['debts']); //returns saved data (AJAX operations)
            }
        }

        $this->getEntityManager()->flush($odr);

        return ['id' => $odr->getId()];
    }
}
