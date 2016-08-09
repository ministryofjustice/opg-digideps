<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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

        $form = $this->createForm(new FormDir\Odr\IncomeBenefitsType(), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odrId, $form->getData(), [
                'deserialise_groups' => [
                    'odr-income-benefit',
                    'odr-income-pension',
                    'odr-income-damages',
                    'odr-one-off',
                ],
            ]);

            return $this->redirect($this->generateUrl('odr-income-benefits', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'subsection' => 'incomeBenefits',
            'form' => $form->createView(),
        ];
    }
}
