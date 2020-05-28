<?php declare(strict_types=1);

namespace AppBundle\Form\Admin;


use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
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
            'report' => $report
        ];

        $form = $this->factory->create(ReportChecklistType::class, $formDataObject, $options);

        $expected = new Checklist($report);
        $expected->setPaymentsMatchCostCertificate($costValues);
        $expected->setProfCostsReasonableAndProportionate($costValues);
        $expected->setHasDeputyOverchargedFromPreviousEstimates($costValues);
        $expected->setContactDetailsUptoDate($deputyDetails);
        $expected->setDeputyFullNameAccurateInCasrec($deputyDetails);

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
            'Null' => [null, false]
        ];
    }
}
