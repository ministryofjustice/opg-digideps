<?php

declare(strict_types=1);

namespace App\Tests\Integration\Factory;

use App\Entity\Client;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ClientBenefitsCheckFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ?ClientBenefitsCheck $moneyClientBenefitsCheck;

    private ?float $moneyAmount;

    private ?int $reportId;

    private ?string $id;
    private ?string $created;
    private ?string $whenLastCheckedEntitlement;
    private ?string $dateLastCheckedEntitlement;
    private ?string $neverCheckedExplanation;
    private ?string $doOthersReceiveMoneyOnClientsBehalf;
    private ?string $dontKnowMoneyExplanation;
    private ?string $moneyId;
    private ?string $moneyCreated;
    private ?string $moneyType;
    private ?string $moneyAmountDontKnow;
    private ?string $whoReceivedMoney;

    public function setUp(): void
    {
        $this->reportId = 1436;
        $this->id = '8e3aaf2c-3145-4e07-b64b-37702323c6f9';
        $this->created = (new \DateTime())->format('Y-m-d');
        $this->whenLastCheckedEntitlement = 'haveChecked';
        $this->dateLastCheckedEntitlement = (new \DateTime())->format('Y-m-d');
        $this->neverCheckedExplanation = null;
        $this->doOthersReceiveMoneyOnClientsBehalf = 'yes';
        $this->dontKnowMoneyExplanation = null;

        $this->moneyId = '5d80a2f3-4f2c-4e0f-9709-2d201102cb13';
        $this->moneyCreated = (new \DateTime())->format('Y-m-d');
        $this->moneyClientBenefitsCheck = null;
        $this->moneyType = 'Universal Credit';
        $this->moneyAmount = 100.5;
        $this->moneyAmountDontKnow = null;
        $this->whoReceivedMoney = 'Some organisation';
    }

    /** @test */
    public function createFromFormDataExistingEntity()
    {
        $existingEntity = true;
        $validData = $this->generateValidFormData($existingEntity);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $this->set($report, $this->reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $ndrRepo = self::prophesize(NdrRepository::class);
        $em = self::prophesize(EntityManagerInterface::class);

        $reportRepo->find($this->reportId)->shouldBeCalled()->willReturn($report);

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal(), $em->reveal());

        $existingMoney = (new MoneyReceivedOnClientsBehalf())
            ->setId(Uuid::fromString($this->moneyId));

        $existingClientBenefitsCheck = (new ClientBenefitsCheck())
            ->addTypeOfMoneyReceivedOnClientsBehalf($existingMoney)
            ->setId(Uuid::fromString($this->id));

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            'report',
            $existingClientBenefitsCheck
        );

        self::assertEquals($this->reportId, $processedClientBenefitsCheck->getReport()->getId());
        self::assertEquals($this->id, $processedClientBenefitsCheck->getId()->toString());
        self::assertEquals($this->created, $processedClientBenefitsCheck->getCreated()->format('Y-m-d'));
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

    private function set($entity, $value, $propertyName = 'id')
    {
        $class = new \ReflectionClass($entity);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($entity, $value);
    }

    /** @test */
    public function createFromFormDataNewEntity()
    {
        $existingEntity = false;
        $validData = $this->generateValidFormData($existingEntity);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $this->set($report, $this->reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $reportRepo->find($this->reportId)->shouldBeCalled()->willReturn($report);

        $ndrRepo = self::prophesize(NdrRepository::class);

        $em = self::prophesize(EntityManagerInterface::class);

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal(), $em->reveal());

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            'report'
        );

        self::assertEquals($this->reportId, $processedClientBenefitsCheck->getReport()->getId());
        self::assertEquals(true, $processedClientBenefitsCheck->getId() instanceof UuidInterface);
        self::assertEquals($this->created, $processedClientBenefitsCheck->getCreated()->format('Y-m-d'));
        self::assertEquals($this->whenLastCheckedEntitlement, $processedClientBenefitsCheck->getWhenLastCheckedEntitlement());
        self::assertEquals($this->dateLastCheckedEntitlement, $processedClientBenefitsCheck->getDateLastCheckedEntitlement()->format('Y-m-d'));
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

    /** @test */
    public function createFromFormDataExistingEntityNonYesDoOthersGetMoneyRemovesAllMoneyTypes()
    {
        $validData = $this->generateValidFormDataRemoveMoneys();

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $this->set($report, $this->reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $reportRepo->find($this->reportId)->shouldBeCalled()->willReturn($report);

        $ndrRepo = self::prophesize(NdrRepository::class);

        $existingMoney = (new MoneyReceivedOnClientsBehalf())
            ->setId(Uuid::fromString($this->moneyId))
            ->setCreated(new \DateTime($this->moneyCreated))
            ->setAmount($this->moneyAmount)
            ->setMoneyType($this->moneyType);

        $existingClientBenefitsCheck = (new ClientBenefitsCheck())
            ->addTypeOfMoneyReceivedOnClientsBehalf($existingMoney)
            ->setId(Uuid::fromString($this->id));

        $existingMoney->setClientBenefitsCheck($existingClientBenefitsCheck);

        /** @var EntityManagerInterface|ObjectProphecy $em */
        $em = self::prophesize(EntityManagerInterface::class);
        $em->remove($existingMoney)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal(), $em->reveal());

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            'report',
            $existingClientBenefitsCheck
        );

        self::assertEquals(true, $processedClientBenefitsCheck instanceof ClientBenefitsCheck);
        self::assertEquals(0, $processedClientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->count());
    }

    private function generateValidFormDataRemoveMoneys()
    {
        $data = $this->generateValidFormData(true);
        $data['do_others_receive_money_on_clients_behalf'] = 'no';

        return $data;
    }
}
