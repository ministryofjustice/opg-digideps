<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use Symfony\Component\Form\Test\TypeTestCase;

class ReportChecklistTypeTest extends TypeTestCase
{
    /**
     * @dataProvider formValuesProvider
     */
    public function testSubmitValidData(?string $costValues, bool $deputyDetails)
    {
        $report = new Report();
        $report->setAvailableSections(['profDeputyCosts']);

        $formDataObject = new Checklist($report);

        $options = [
            'report' => $report,
        ];

        $form = $this->factory->create(ReportChecklistType::class, $formDataObject, $options);

        $expected = new Checklist($report);
        $expected->setPaymentsMatchCostCertificate($costValues);
        $expected->setProfCostsReasonableAndProportionate($costValues);
        $expected->setHasDeputyOverchargedFromPreviousEstimates($costValues);
        $expected->setContactDetailsUptoDate($deputyDetails);
        $expected->setDeputyFullNameAccurateInSirius($deputyDetails);

        $formData = [
            'paymentsMatchCostCertificate' => $costValues,
            'profCostsReasonableAndProportionate' => $costValues,
            'hasDeputyOverchargedFromPreviousEstimates' => $costValues,
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected, $formDataObject);
    }

    public function formValuesProvider()
    {
        return [
            'Yes' => ['yes', false],
            'No' => ['no', false],
            'Not Applicable' => ['na', false],
        ];
    }
}
