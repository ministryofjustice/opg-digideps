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
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

class ClientBenefitsCheckFactoryTest extends TestCase
{
    /** @test */
    public function createFromFormDataExistingEntity()
    {
        $reportId = 1436;
        $id = '8e3aaf2c-3145-4e07-b64b-37702323c6f9';
        $created = '2021-11-11';
        $whenLastCheckedEntitlement = 'haveChecked';
        $dateLastCheckedEntitlement = '2021-11-11';
        $neverCheckedExplanation = null;
        $doOthersReceiveIncomeOnClientsBehalf = 'yes';
        $dontKnowIncomeExplanation = null;

        $incomeId = '5d80a2f3-4f2c-4e0f-9709-2d201102cb13';
        $incomeCreated = '2021-11-11';
        $incomeClientBenefitsCheck = null;
        $incomeType = 'Universal Credit';
        $incomeAmount = 100.5;
        $incomeAmountDontKnow = null;

        $validData = [
            'report_id' => $reportId,
            'id' => $id,
            'created' => $created,
            'when_last_checked_entitlement' => $whenLastCheckedEntitlement,
            'date_last_checked_entitlement' => $dateLastCheckedEntitlement,
            'never_checked_explanation' => $neverCheckedExplanation,
            'do_others_receive_income_on_clients_behalf' => $doOthersReceiveIncomeOnClientsBehalf,
            'dont_know_income_explanation' => $dontKnowIncomeExplanation,
            'types_of_income_received_on_clients_behalf' => [
                0 => [
                    'id' => $incomeId,
                    'created' => $incomeCreated,
                    'client_benefits_check' => $incomeClientBenefitsCheck,
                    'income_type' => $incomeType,
                    'amount' => $incomeAmount,
                    'amount_dont_know' => $incomeAmountDontKnow,
                ],
            ],
            'report' => [],
        ];

        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new DateTime(), new DateTime());
        $this->set($report, $reportId);

        /** @var ObjectProphecy|ReportRepository $reportRepo */
        $reportRepo = self::prophesize(ReportRepository::class);
        $reportRepo->find($reportId)->shouldBeCalled()->willReturn($report);

        $ndrRepo = self::prophesize(NdrRepository::class);

        $sut = new ClientBenefitsCheckFactory($reportRepo->reveal(), $ndrRepo->reveal());

        $existingIncome = (new IncomeReceivedOnClientsBehalf())
            ->setId(Uuid::fromString($incomeId));

        $existingClientBenefitsCheck = (new ClientBenefitsCheck())
            ->addTypeOfIncomeReceivedOnClientsBehalf($existingIncome)
            ->setId(Uuid::fromString($id));

        $processedClientBenefitsCheck = $sut->createFromFormData(
            $validData,
            'report',
            $existingClientBenefitsCheck
        );

        self::assertEquals($reportId, $processedClientBenefitsCheck->getReport()->getId());
        self::assertEquals($id, $processedClientBenefitsCheck->getId()->toString());
        self::assertEquals($created, $processedClientBenefitsCheck->getCreated()->format('Y-m-d'));
        self::assertEquals($whenLastCheckedEntitlement, $processedClientBenefitsCheck->getWhenLastCheckedEntitlement());
        self::assertEquals($dateLastCheckedEntitlement, $processedClientBenefitsCheck->getDateLastCheckedEntitlement()->format('Y-m-d'));
        self::assertEquals($neverCheckedExplanation, $processedClientBenefitsCheck->getNeverCheckedExplanation());
        self::assertEquals($doOthersReceiveIncomeOnClientsBehalf, $processedClientBenefitsCheck->getDoOthersReceiveIncomeOnClientsBehalf());
        self::assertEquals($dontKnowIncomeExplanation, $processedClientBenefitsCheck->getDontKnowIncomeExplanation());

        /** @var IncomeReceivedOnClientsBehalf $income */
        $income = $processedClientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->first();
        self::assertEquals($incomeId, $income->getId()->toString());
        self::assertEquals($incomeCreated, $income->getCreated()->format('Y-m-d'));
        self::assertEquals($existingClientBenefitsCheck, $income->getClientBenefitsCheck());
        self::assertEquals($incomeType, $income->getIncomeType());
        self::assertEquals($incomeAmount, $income->getAmount());
    }

    private function set($entity, $value, $propertyName = 'id')
    {
        $class = new ReflectionClass($entity);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($entity, $value);
    }
}
