<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\ClientContact;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Note;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Action;
use OPG\Digideps\Backend\Entity\Report\AssetOther;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Checklist;
use OPG\Digideps\Backend\Entity\Report\ChecklistInformation;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Debt;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\Lifestyle;
use OPG\Digideps\Backend\Entity\Report\MentalCapacity;
use OPG\Digideps\Backend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Backend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;
use OPG\Digideps\Backend\Entity\Report\MoneyTransfer;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyInterimCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyOtherCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\Report\ReviewChecklist;
use OPG\Digideps\Backend\Entity\Report\VisitsCare;
use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Entity\Setting;
use OPG\Digideps\Backend\Entity\User;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testDeputyValidOnConstruction(): void
    {
        $deputy = new Deputy('', DeputyType::LAY, '', '');
        $this->testEntity($deputy);
    }

    public function testReportValidOnConstruction(): void
    {
        $report = $this->makeReport();
        $this->testEntity($report);
    }

    public function testClientValidOnConstruction(): void
    {
        $client = new Client();
        $this->testEntity($client);
    }

    public function testContactValidOnConstruction(): void
    {
        $contact = new Contact($this->makeReport());
        $this->testEntity($contact);
    }

    public function testAssetOtherValidOnConstruction(): void
    {
        $assetOther = new AssetOther($this->makeReport());
        $this->testEntity($assetOther);
    }

    public function testAssetPropertyValidOnConstruction(): void
    {
        $assetProperty = new AssetProperty($this->makeReport());
        $this->testEntity($assetProperty);
    }

    public function testBankAccountValidOnConstruction(): void
    {
        $bankAccount = new BankAccount($this->makeReport());
        $this->testEntity($bankAccount);
    }

    public function testClientContactValidOnConstruction(): void
    {
        $clientContact = new ClientContact(new Client(), '', '');
        $this->testEntity($clientContact);
    }

    public function testNoteValidOnConstruction(): void
    {
        $note = new Note(new Client(), '', '', '');
        $this->testEntity($note);
    }

    public function testOrganisationValidOnConstruction(): void
    {
        $organisation = new Organisation('', '');
        $this->testEntity($organisation);
    }

    public function testCourtOrderValidOnConstruction(): void
    {
        $courtOrder = $this->makeCourtOrder();
        $this->testEntity($courtOrder);
    }

    public function testSatisfactionValidOnConstruction(): void
    {
        $satisfaction = new Satisfaction(0);
        $this->testEntity($satisfaction);
    }

    public function testSettingValidOnConstruction(): void
    {
        $setting = new Setting('', '', true);
        $this->testEntity($setting);
    }

    public function testUserValidOnConstruction(): void
    {
        $user = new User('', '', '');
        $this->testEntity($user);
    }

    public function testActionValidOnConstruction(): void
    {
        $action = new Action($this->makeReport());
        $this->testEntity($action);
    }

    public function testChecklistValidOnConstruction(): void
    {
        $checklist = new Checklist($this->makeReport());
        $this->testEntity($checklist);
    }

    public function testChecklistInformationValidOnConstruction(): void
    {
        $checklistInformation = new ChecklistInformation(new Checklist($this->makeReport()), '');
        $this->testEntity($checklistInformation);
    }

    public function testDebtValidOnConstruction(): void
    {
        $debt = new Debt($this->makeReport(), '', false);
        $this->testEntity($debt);
    }

    public function testDecisionValidOnConstruction(): void
    {
        $decision = new Decision($this->makeReport());
        $this->testEntity($decision);
    }

    public function testDocumentsValidOnConstruction(): void
    {
        $document = new Document($this->makeReport(), '');
        $this->testEntity($document);
    }

    public function testExpenseValidOnConstruction(): void
    {
        $expense = new Expense($this->makeReport(), '');
        $this->testEntity($expense);
    }

    public function testFeeValidOnConstruction(): void
    {
        $fee = new Fee($this->makeReport(), '');
        $this->testEntity($fee);
    }

    public function testGiftValidOnConstruction(): void
    {
        $gift = new Gift($this->makeReport(), '');
        $this->testEntity($gift);
    }

    public function testLifestyleValidOnConstruction(): void
    {
        $lifestyle = new Lifestyle($this->makeReport());
        $this->testEntity($lifestyle);
    }

    public function testMentalCapacityValidOnConstruction(): void
    {
        $mentalCapacity = new MentalCapacity($this->makeReport());
        $this->testEntity($mentalCapacity);
    }

    public function testReportSubmissionValidOnConstruction(): void
    {
        $reportSubmission = new ReportSubmission($this->makeReport(), null);
        $this->testEntity($reportSubmission);
    }

    public function testReviewChecklistValidOnConstruction(): void
    {
        $reviewChecklist = new ReviewChecklist($this->makeReport());
        $this->testEntity($reviewChecklist);
    }

    public function testVisitsCareValidOnConstruction(): void
    {
        $visitsCare = new VisitsCare($this->makeReport());
        $this->testEntity($visitsCare);
    }

    public function testProfDeputyEstimateCostValidOnConstruction(): void
    {
        $profDeputyEstimateCost = new ProfDeputyEstimateCost($this->makeReport(), '');
        $this->testEntity($profDeputyEstimateCost);
    }

    public function testProfDeputyInterimCostValidOnConstruction(): void
    {
        $profDeputyInterimCost = new ProfDeputyInterimCost($this->makeReport(), new \DateTime(), null);
        $this->testEntity($profDeputyInterimCost);
    }

    public function testProfDeputyOtherCostValidOnConstruction(): void
    {
        $profDeputyOtherCost = new ProfDeputyOtherCost($this->makeReport(), '', false, null);
        $this->testEntity($profDeputyOtherCost);
    }

    public function testProfDeputyPreviousCostValidOnConstruction(): void
    {
        $profDeputyPreviousCost = new ProfDeputyPreviousCost($this->makeReport(), null);
        $this->testEntity($profDeputyPreviousCost);
    }

    public function testValidOnConstruction(): void
    {
        $moneyShortCategory = new MoneyShortCategory($this->makeReport(), '', false);
        $this->testEntity($moneyShortCategory);
    }

    public function testMoneyTransactionValidOnConstruction(): void
    {
        $moneyTransaction = new MoneyTransaction($this->makeReport(), '');
        $this->testEntity($moneyTransaction);
    }

    public function testMoneyTransactionShortInValidOnConstruction(): void
    {
        $moneyTransactionShortIn = new MoneyTransactionShortIn($this->makeReport());
        $this->testEntity($moneyTransactionShortIn);
    }

    public function testMoneyTransactionShortOutValidOnConstruction(): void
    {
        $moneyTransactionShortOut = new MoneyTransactionShortOut($this->makeReport());
        $this->testEntity($moneyTransactionShortOut);
    }

    public function testMoneyTransferValidOnConstruction(): void
    {
        $moneyTransfer = new MoneyTransfer($this->makeReport());
        $this->testEntity($moneyTransfer);
    }

    public function testClientBenefitsCheckValidOnConstruction(): void
    {
        $clientBenefitsCheck = new ClientBenefitsCheck();
        $this->testEntity($clientBenefitsCheck);
    }

    private function testEntity(object $entity): void
    {
        $this->assertSame([], $this->testEntityWithReflection($entity, new \ReflectionClass($entity)));
    }

    private function makeCourtOrder(): CourtOrder
    {
        return new CourtOrder(
            '',
            CourtOrderType::PFA,
            CourtOrderReportType::OPG102,
            CourtOrderKind::Single,
            new \DateTime(),
            new Client()
        );
    }

    private function makeReport(): Report
    {
        return new Report(new Client(), '102', new \DateTime(), new \DateTime(), false);
    }

    /**
     * @template T of object
     * @param T $entity
     * @param \ReflectionClass<T> $reflection
     */
    private function testEntityWithReflection(object $entity, \ReflectionClass $reflection): array
    {
        $errors = [];
        foreach ($reflection->getProperties() as $property) {
            try {
                $_ = $property->getValue($entity);
                if ($property->getType() === null) {
                    $errors[] = [
                        $property->getName(),
                        $property->getDeclaringClass()->getName(),
                        ''
                    ];
                }
            } catch (\Throwable $_) {
                $errors[] = [
                    $property->getName(),
                    $property->getDeclaringClass()->getName(),
                    "{$property->getType()}"
                ];
            }
        }

        if ($reflection->getParentClass()) {
            $errors = [...$this->testEntityWithReflection($entity, $reflection->getParentClass()), ...$errors];
        }

        return $errors;
    }
}
