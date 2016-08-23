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

class ExpensesController extends AbstractController
{
    private static $odrJmsGroups = [
        'odr',
        'client',
        'client-cot',
        'odr-expenses',
    ];

    /**
     * @Route("/odr/{odrId}/finance/expenses", name="odr-expenses")
     *
     * @param Request $request
     * @param int $odrId
     * @Template("AppBundle:Odr/Finance/Expenses:index.html.twig")
     *
     * @return array
     */
    public function indexAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $odr->addExpense(new EntityDir\Odr\Expense());

        $form = $this->createForm(new FormDir\Odr\Expense\ExpensesType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odrId, $form->getData(), ['odr-expenses']);

            return $this->redirect($this->generateUrl('odr-expenses', ['odrId' => $odrId]));
        }


        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'subsection' => 'expenses'
        ];
    }
}
