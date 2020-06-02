<?php declare(strict_types=1);


namespace Tests\AppBundle\Entity\Report;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use DigidepsTests\Helpers\ValidatorTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChecklistTest extends KernelTestCase
{
    /** @var ValidatorInterface */
    private $validator;

    public function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$kernel->getContainer()->get('validator.builder')->enableAnnotationMapping()->getValidator();
    }

    /**
     * @test
     * @dataProvider submit_profDeputyCosts_checklist_valuesProvider
     */
    public function validations_submit_profDeputyCosts_checklist(
        $profCostsReasonableAndProportionate,
        $paymentsMatchCostCertificate,
        $hasDeputyOverchargedFromPreviousEstimates,
        $expectedCountErrors
    )
    {
        $report = new Report();

        $checklist = new Checklist($report);
        $checklist->setProfCostsReasonableAndProportionate($profCostsReasonableAndProportionate);
        $checklist->setPaymentsMatchCostCertificate($paymentsMatchCostCertificate);
        $checklist->setHasDeputyOverchargedFromPreviousEstimates($hasDeputyOverchargedFromPreviousEstimates);

        $errors = $this->validator->validate($checklist, null, ['submit-profDeputyCosts-checklist']);
        self::assertEquals($expectedCountErrors, count($errors));
    }

    public function submit_profDeputyCosts_checklist_valuesProvider()
    {
        return [
            'valid values' => [null, 'yes', 'no', 0],
            'invalid values' => ['definitely', 'negative', 'nope', 3]
        ];
    }
}
