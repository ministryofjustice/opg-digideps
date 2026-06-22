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
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Debt;
use OPG\Digideps\Backend\Entity\Report\Report;
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
