<?php

namespace AppBundle\Controller\Odr\Finance;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class IncomeBenefitsController extends AbstractController
{
    private static $odrJmsGroups = [
        'odr',
        'client',
        'client-cot',
        'odr-income-benefits',
        'odr-income-state-benefits',
        'odr-income-pension',
        'odr-income-damages',
        'odr-income-one-off',
    ];

    /**
     * @Route("/odr/{odrId}/finance/income-benefits", name="odr-income-benefits")
     *
     * @param int $odrId
     * @Template("AppBundle:Odr/Finance/IncomeBenefits:index.html.twig")
     *
     * @return array
     */
    public function indexAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $formStateBenefit = $this->createForm(new FormDir\Odr\IncomeBenefit\StateBenefitType(), $odr);
        $this->handleForm($formStateBenefit, ['odr-state-benefits'], $odrId);

        $formPension = $this->createForm(new FormDir\Odr\IncomeBenefit\PensionType(), $odr);
        $this->handleForm($formPension, ['odr-income-pension'], $odrId);

        $formDamage = $this->createForm(new FormDir\Odr\IncomeBenefit\DamageType(), $odr);
        $this->handleForm($formDamage, ['odr-income-damages'], $odrId);

        $formOneOff = $this->createForm(new FormDir\Odr\IncomeBenefit\OneOffType(), $odr);
        $this->handleForm($formOneOff, ['odr-one-off'], $odrId);

        return [
            'odr' => $odr,
            'subsection' => 'incomeBenefits',
            'formStateBenefit' => $formStateBenefit->createView(),
            'formPension' => $formPension->createView(),
            'formDamage' => $formDamage->createView(),
            'formOneOff' => $formOneOff->createView(),
        ];
    }

    private function handleForm(Form $form, array $jmsGroups, $odrId)
    {
        $request = $this->getRequest();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odrId, $form->getData(), $jmsGroups);

            return $this->redirect($this->generateUrl('odr-income-benefits', ['odrId' => $odrId]));
        }
    }
}
