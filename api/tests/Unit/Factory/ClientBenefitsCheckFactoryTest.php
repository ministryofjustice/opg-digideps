<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Client;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;

class ClientBenefitsCheckFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ?ClientBenefitsCheck $incomeClientBenefitsCheck;

    private ?float $incomeAmount;

    private ?int $reportId;

    private ?string $id;
    private ?string $created;
    private ?string $whenLastCheckedEntitlement;
    private ?string $dateLastCheckedEntitlement;
    private ?string $neverCheckedExplanation;
    private ?string $doOthersReceiveIncomeOnClientsBehalf;
    private ?string $dontKnowIncomeExplanation;
    private ?string $incomeId;
    private ?string $incomeCreated;
    private ?string $incomeType;
    private ?string $incomeAmountDontKnow;

    public function setUp(): void
    {
        $this->reportId = 1436;
        $this->id = '8e3aaf2c-3145-4e07-b64b-37702323c6f9';
        $this->created = (new DateTime())->format('Y-m-d');
        $this->whenLastCheckedEntitlement = 'haveChecked';
        $this->dateLastCheckedEntitlement = (new DateTime())->format('Y-m-d');
        $this->neverCheckedExplanation = null;
        $this->doOthersReceiveIncomeOnClientsBehalf = 'yes';
        $this->dontKnowIncomeExplanation = null;

        $this->incomeId = '5d80a2f3-4f2c-4e0f-9709-2d201102cb13';
        $this->incomeCreated = (new DateTime())->format('Y-m-d');
        $this->incomeClientBenefitsCheck = null;
        $this->incomeType = 'Universal Credit';
        $this->incomeAmount = 100.5;
        $this->incomeAmountDontKnow = null;
    }

    /** @test */
    public function createFromFormDataExistingEntity()
    {
        $existingEntity = true;
        $validData = $this->generateValidFormData($existingEntity);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new DateTime(), new DateTime());
        $this->set($report, $this->reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $ndrRepo = self::prophesize(NdrRepository::class);
        $em = self::prophesize(EntityManagerInterface::class);

        $reportRepo->find($this->reportId)->shouldBeCalled()->willReturn($report);

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal(), $em->reveal());

        $existingIncome = (new IncomeReceivedOnClientsBehalf())
            ->setId(Uuid::fromString($this->incomeId));

        $existingClientBenefitsCheck = (new ClientBenefitsCheck())
            ->addTypeOfIncomeReceivedOnClientsBehalf($existingIncome)
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
        self::assertEquals($this->doOthersReceiveIncomeOnClientsBehalf, $processedClientBenefitsCheck->getDoOthersReceiveIncomeOnClientsBehalf());
        self::assertEquals($this->dontKnowIncomeExplanation, $processedClientBenefitsCheck->getDontKnowIncomeExplanation());

        /** @var IncomeReceivedOnClientsBehalf $income */
        $income = $processedClientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->first();

        self::assertEquals($this->incomeId, $income->getId()->toString());
        self::assertEquals($this->incomeCreated, $income->getCreated()->format('Y-m-d'));
        self::assertEquals($existingClientBenefitsCheck, $income->getClientBenefitsCheck());
        self::assertEquals($this->incomeType, $income->getIncomeType());
        self::assertEquals($this->incomeAmount, $income->getAmount());
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
            'do_others_receive_income_on_clients_behalf' => $this->doOthersReceiveIncomeOnClientsBehalf,
            'dont_know_income_explanation' => $this->dontKnowIncomeExplanation,
            'types_of_income_received_on_clients_behalf' => [
                0 => [
                    'id' => $existingEntity ? $this->incomeId : null,
                    'created' => $this->incomeCreated,
                    'client_benefits_check' => $this->incomeClientBenefitsCheck,
                    'income_type' => $this->incomeType,
                    'amount' => $this->incomeAmount,
                    'amount_dont_know' => $this->incomeAmountDontKnow,
                ],
            ],
            'report' => [],
        ];
    }

    private function set($entity, $value, $propertyName = 'id')
    {
        $class = new ReflectionClass($entity);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($entity, $value);
    }

    /** @test */
    public function createFromFormDataNewEntity()
    {
        $existingEntity = false;
        $validData = $this->generateValidFormData($existingEntity);

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new DateTime(), new DateTime());
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
        self::assertEquals($this->doOthersReceiveIncomeOnClientsBehalf, $processedClientBenefitsCheck->getDoOthersReceiveIncomeOnClientsBehalf());
        self::assertEquals($this->dontKnowIncomeExplanation, $processedClientBenefitsCheck->getDontKnowIncomeExplanation());

        /** @var IncomeReceivedOnClientsBehalf $income */
        $income = $processedClientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->first();

        self::assertEquals(true, $income->getId() instanceof UuidInterface);
        self::assertEquals($this->incomeCreated, $income->getCreated()->format('Y-m-d'));
        self::assertEquals(true, $income->getClientBenefitsCheck() instanceof ClientBenefitsCheck);
        self::assertEquals($this->incomeType, $income->getIncomeType());
        self::assertEquals($this->incomeAmount, $income->getAmount());
    }

    /** @test */
    public function createFromFormDataExistingEntityNonYesDoOthersGetIncomeRemovesAllIncomeTypes()
    {
        $validData = $this->generateValidFormDataRemoveIncomes();

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new DateTime(), new DateTime());
        $this->set($report, $this->reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $reportRepo->find($this->reportId)->shouldBeCalled()->willReturn($report);

        $ndrRepo = self::prophesize(NdrRepository::class);

        $existingIncome = (new IncomeReceivedOnClientsBehalf())
            ->setId(Uuid::fromString($this->incomeId))
            ->setCreated(new DateTime($this->incomeCreated))
            ->setAmount($this->incomeAmount)
            ->setIncomeType($this->incomeType);

        $existingClientBenefitsCheck = (new ClientBenefitsCheck())
            ->addTypeOfIncomeReceivedOnClientsBehalf($existingIncome)
            ->setId(Uuid::fromString($this->id));

        $existingIncome->setClientBenefitsCheck($existingClientBenefitsCheck);

        /** @var EntityManagerInterface|ObjectProphecy $em */
        $em = self::prophesize(EntityManagerInterface::class);
        $em->remove($existingIncome)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal(), $em->reveal());

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            'report',
            $existingClientBenefitsCheck
        );

        self::assertEquals(true, $processedClientBenefitsCheck instanceof ClientBenefitsCheck);
        self::assertEquals(0, $processedClientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->count());
    }

    private function generateValidFormDataRemoveIncomes()
    {
        $data = $this->generateValidFormData(true);
        $data['do_others_receive_income_on_clients_behalf'] = 'no';

        return $data;
    }
}
