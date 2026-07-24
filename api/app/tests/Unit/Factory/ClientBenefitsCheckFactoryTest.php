<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Factory;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\MoneyReceivedOnClientsBehalf;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Factory\ClientBenefitsCheckFactory;
use OPG\Digideps\Backend\Repository\ReportRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ClientBenefitsCheckFactoryTest extends TestCase
{
    private float $moneyAmount = 100.5;
    private int $reportId = 1436;
    private string $id = '8e3aaf2c-3145-4e07-b64b-37702323c6f9';
    private string $whenLastCheckedEntitlement = 'haveChecked';
    private string $doOthersReceiveMoneyOnClientsBehalf = 'yes';
    private string $moneyId = '5d80a2f3-4f2c-4e0f-9709-2d201102cb13';
    private string $moneyType = 'Universal Credit';
    private string $whoReceivedMoney = 'Some organisation';
    private ?ClientBenefitsCheck $moneyClientBenefitsCheck = null;
    private ?string $moneyAmountDontKnow = null;
    private ?string $dontKnowMoneyExplanation = null;
    private ?string $dateLastCheckedEntitlement = null;
    private ?string $neverCheckedExplanation = null;
    private ?string $moneyCreated = null;
    private ?string $created = null;

    public function setUp(): void
    {
        $this->created = new \DateTime()->format('Y-m-d');
        $this->dateLastCheckedEntitlement = new \DateTime()->format('Y-m-d');
        $this->moneyCreated = new \DateTime()->format('Y-m-d');
    }

    #[Test]
    public function createFromFormDataExistingEntity(): void
    {
        $validData = $this->generateValidFormData(true);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $report->setId($this->reportId);

        $reportRepo = self::createMock(ReportRepository::class);
        $em = self::createMock(EntityManagerInterface::class);

        $reportRepo->expects(self::once())
            ->method('find')
            ->with($this->reportId)
            ->willReturn($report);

        $sut = new ClientBenefitsCheckFactory($reportRepo, $em);

        $existingMoney = new MoneyReceivedOnClientsBehalf()
            ->setId(Uuid::fromString($this->moneyId));

        $existingClientBenefitsCheck = new ClientBenefitsCheck()
            ->addTypeOfMoneyReceivedOnClientsBehalf($existingMoney)
            ->setId(Uuid::fromString($this->id));

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            $existingClientBenefitsCheck
        );

        self::assertEquals($this->reportId, $processedClientBenefitsCheck->getReport()->getId());
        self::assertEquals($this->id, $processedClientBenefitsCheck->getId()->toString());
        self::assertEquals($this->created, $processedClientBenefitsCheck->getCreated()?->format('Y-m-d'));
        self::assertEquals($this->whenLastCheckedEntitlement, $processedClientBenefitsCheck->getWhenLastCheckedEntitlement());
        self::assertEquals($this->dateLastCheckedEntitlement, $processedClientBenefitsCheck->getDateLastCheckedEntitlement()->format('Y-m-d'));
        self::assertEquals($this->neverCheckedExplanation, $processedClientBenefitsCheck->getNeverCheckedExplanation());
        self::assertEquals($this->doOthersReceiveMoneyOnClientsBehalf, $processedClientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf());
        self::assertEquals($this->dontKnowMoneyExplanation, $processedClientBenefitsCheck->getDontKnowMoneyExplanation());

        /** @var MoneyReceivedOnClientsBehalf $money */
        $money = $processedClientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->first();

        self::assertEquals($this->moneyId, $money->getId()->toString());
        self::assertEquals($this->moneyCreated, $money->getCreated()->format('Y-m-d'));
        self::assertEquals($existingClientBenefitsCheck, $money->getClientBenefitsCheck());
        self::assertEquals($this->moneyType, $money->getMoneyType());
        self::assertEquals($this->moneyAmount, $money->getAmount());
    }

    private function generateValidFormData(bool $existingEntity): array
    {
        return [
            'report_id' => $this->reportId,
            'id' => $existingEntity ? $this->id : null,
            'created' => $this->created,
            'when_last_checked_entitlement' => $this->whenLastCheckedEntitlement,
            'date_last_checked_entitlement' => $this->dateLastCheckedEntitlement,
            'never_checked_explanation' => $this->neverCheckedExplanation,
            'do_others_receive_money_on_clients_behalf' => $this->doOthersReceiveMoneyOnClientsBehalf,
            'dont_know_money_explanation' => $this->dontKnowMoneyExplanation,
            'types_of_money_received_on_clients_behalf' => [
                0 => [
                    'id' => $existingEntity ? $this->moneyId : null,
                    'created' => $this->moneyCreated,
                    'client_benefits_check' => $this->moneyClientBenefitsCheck,
                    'money_type' => $this->moneyType,
                    'amount' => $this->moneyAmount,
                    'amount_dont_know' => $this->moneyAmountDontKnow,
                    'who_received_money' => $this->whoReceivedMoney,
                ],
            ],
            'report' => [],
        ];
    }

    #[Test]
    public function createFromFormDataNewEntity(): void
    {
        $validData = $this->generateValidFormData(false);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $report->setId($this->reportId);

        $reportRepo = self::createMock(ReportRepository::class);
        $reportRepo->expects(self::once())
            ->method('find')
            ->with($this->reportId)
            ->willReturn($report);

        $em = self::createMock(EntityManagerInterface::class);

        $sut = new ClientBenefitsCheckFactory($reportRepo, $em);

        $processedClientBenefitsCheck = $sut->createFromFormData($validData);

        self::assertEquals($this->reportId, $processedClientBenefitsCheck->getReport()->getId());
        self::assertEquals(true, $processedClientBenefitsCheck->getId() instanceof UuidInterface);
        self::assertEquals($this->created, $processedClientBenefitsCheck->getCreated()?->format('Y-m-d'));
        self::assertEquals($this->whenLastCheckedEntitlement, $processedClientBenefitsCheck->getWhenLastCheckedEntitlement());
        self::assertEquals($this->dateLastCheckedEntitlement, $processedClientBenefitsCheck->getDateLastCheckedEntitlement()?->format('Y-m-d'));
        self::assertEquals($this->neverCheckedExplanation, $processedClientBenefitsCheck->getNeverCheckedExplanation());
        self::assertEquals($this->doOthersReceiveMoneyOnClientsBehalf, $processedClientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf());
        self::assertEquals($this->dontKnowMoneyExplanation, $processedClientBenefitsCheck->getDontKnowMoneyExplanation());

        /** @var MoneyReceivedOnClientsBehalf $money */
        $money = $processedClientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->first();

        self::assertEquals(true, $money->getId() instanceof UuidInterface);
        self::assertEquals($this->moneyCreated, $money->getCreated()->format('Y-m-d'));
        self::assertEquals(true, $money->getClientBenefitsCheck() instanceof ClientBenefitsCheck);
        self::assertEquals($this->moneyType, $money->getMoneyType());
        self::assertEquals($this->moneyAmount, $money->getAmount());
    }

    #[Test]
    public function createFromFormDataExistingEntityNonYesDoOthersGetMoneyRemovesAllMoneyTypes(): void
    {
        $validData = $this->generateValidFormDataRemoveMoneys();

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $report->setId($this->reportId);

        $reportRepo = self::createMock(ReportRepository::class);
        $reportRepo->expects(self::once())
            ->method('find')
            ->with($this->reportId)
            ->willReturn($report);

        $existingMoney = new MoneyReceivedOnClientsBehalf()
            ->setId(Uuid::fromString($this->moneyId))
            ->setCreated(new \DateTime($this->moneyCreated))
            ->setAmount($this->moneyAmount)
            ->setMoneyType($this->moneyType);

        $existingClientBenefitsCheck = new ClientBenefitsCheck()
            ->addTypeOfMoneyReceivedOnClientsBehalf($existingMoney)
            ->setId(Uuid::fromString($this->id));

        $existingMoney->setClientBenefitsCheck($existingClientBenefitsCheck);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('remove')
            ->with($existingMoney);
        $em->expects(self::once())
            ->method('flush');

        $sut = new ClientBenefitsCheckFactory($reportRepo, $em);

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            $existingClientBenefitsCheck
        );

        self::assertEquals(0, $processedClientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->count());
    }

    private function generateValidFormDataRemoveMoneys(): array
    {
        $data = $this->generateValidFormData(true);
        $data['do_others_receive_money_on_clients_behalf'] = 'no';

        return $data;
    }
}
