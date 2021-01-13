<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    private array $sectionIds = [
        EntityDir\Report\Report::SECTION_MONEY_IN,
        EntityDir\Report\Report::SECTION_MONEY_OUT
    ];

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/report/{reportId}/money-transaction", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
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

        $this->em->persist($t);
        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->formatter->deserializeBodyContent($request);
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
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}", methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $this->em->remove($t);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }
}
