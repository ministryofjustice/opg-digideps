<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionController extends RestController
{
    private $sectionIds = [
        EntityDir\Report\Report::SECTION_MONEY_IN,
        EntityDir\Report\Report::SECTION_MONEY_OUT
    ];

    /**
     * @Route("/report/{reportId}/money-transaction")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'category' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = new EntityDir\Report\MoneyTransaction($report);
        $t->setCategory($data['category']);
        $t->setAmount($data['amount']);
        if (array_key_exists('description', $data)) {
            $t->setDescription($data['description']);
        }

        // update bank account
        $t->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->getRepository(
                EntityDir\Report\BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId()
                ]
            );
            if ($bankAccount instanceof EntityDir\Report\BankAccount) {
                $t->setBankAccount($bankAccount);
            }
        }

        $t->setReport($report);
        $this->persistAndFlush($t);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->deserializeBodyContent($request);
        if (isset($data['description'])) {
            $t->setDescription($data['description']);
        }
        if (isset($data['amount'])) {
            $t->setAmount($data['amount']);
        }

        if (array_key_exists('bank_account_id', $data)) {
            if (is_numeric($data['bank_account_id'])) {
                $t->setBankAccount($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['bank_account_id']));
            } else {
                $t->setBankAccount(null);
            }
        }
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $this->getEntityManager()->remove($t);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }
}
