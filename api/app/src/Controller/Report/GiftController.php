<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GiftController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_GIFTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/gift/{giftId}', requirements: ['reportId' => '\d+', 'giftId' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $reportId, int $giftId): EntityDir\Report\Gift
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['gifts'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $gift;
    }

    #[Route(path: '/report/{reportId}/gift', requirements: ['reportId' => '\d+'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request, int $reportId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->formatter->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $gift = new EntityDir\Report\Gift($report);

        $this->updateEntityWithData($report, $gift, $data);
        $report->setGiftsExist('yes');

        $this->em->persist($gift);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $gift->getId()];
    }

    #[Route(path: '/report/{reportId}/gift/{giftId}', requirements: ['reportId' => '\d+', 'giftId' => '\d+'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function edit(Request $request, int $reportId, int $giftId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);

        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());

        $this->updateEntityWithData($report, $gift, $data);

        if (array_key_exists('bank_account_id', $data)) {
            if (is_numeric($data['bank_account_id'])) {
                $gift->setBankAccount($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['bank_account_id']));
            } else {
                $gift->setBankAccount(null);
            }
        }
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $gift->getId()];
    }

    #[Route(path: '/report/{reportId}/gift/{giftId}', requirements: ['reportId' => '\d+', 'giftId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(int $reportId, int $giftId): array
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());
        $this->em->remove($gift);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Report\Report $report, EntityDir\Report\Gift $gift, array $data): void
    {
        // common props
        $this->hydrateEntityWithArrayData($gift, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);

        // update bank account
        $gift->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->em->getRepository(
                EntityDir\Report\BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId(),
                ]
            );
            if ($bankAccount instanceof EntityDir\Report\BankAccount) {
                $gift->setBankAccount($bankAccount);
            }
        }
    }
}
