<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Checklist;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChecklistTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();
    }

    /**
     * @test
     *
     * @dataProvider submitProfDeputyCostsChecklistValuesProvider
     */
    public function validationsSubmitProfDeputyCostsChecklist(
        $profCostsReasonableAndProportionate,
        $paymentsMatchCostCertificate,
        $hasDeputyOverchargedFromPreviousEstimates,
        $expectedCountErrors
    ): void {
        $report = new Report();

        $checklist = new Checklist($report);
        $checklist->setProfCostsReasonableAndProportionate($profCostsReasonableAndProportionate);
        $checklist->setPaymentsMatchCostCertificate($paymentsMatchCostCertificate);
        $checklist->setHasDeputyOverchargedFromPreviousEstimates($hasDeputyOverchargedFromPreviousEstimates);

        $errors = $this->validator->validate($checklist, null, ['submit-profDeputyCosts-checklist']);
        self::assertEquals($expectedCountErrors, count($errors));
    }

    public function submitProfDeputyCostsChecklistValuesProvider(): array
    {
        return [
            'one missing value' => [null, 'yes', 'no', 1],
            'all missing values' => [null, null, null, 3],
        ];
    }
}
