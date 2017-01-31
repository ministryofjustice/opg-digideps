<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BankAccountController extends AbstractController
{
    private static $jmsGroups = ['odr-account', 'client-cot'];

    /**
     * @Route("/odr/{odrId}/bank-accounts", name="odr_bank_accounts")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if (count($odr->getBankAccounts())) {
            return $this->redirectToRoute('odr_bank_accounts_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/bank-account/step{step}/{accountId}", name="odr_bank_accounts_step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $odrId, $step, $accountId = null)
    {
        $totalSteps = 3;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_bank_accounts_summary', ['odrId' => $odrId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('odr_bank_accounts', 'odr_bank_accounts_step', 'odr_bank_accounts_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId'=>$odrId, 'accountId' => $accountId]);


        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->getRestClient()->get('odr/account/' . $accountId, 'Odr\\BankAccount');
        } else {
            $account = new EntityDir\Odr\BankAccount();
            $account->setOdr($odr);
        }

        // add URL-data into model
        isset($dataFromUrl['type']) && $account->setAccountType($dataFromUrl['type']);
        isset($dataFromUrl['bank']) && $account->setBank($dataFromUrl['bank']);
        isset($dataFromUrl['number']) && $account->setAccountNumber($dataFromUrl['number']);
        isset($dataFromUrl['sort-code']) && $account->setSortCode($dataFromUrl['sort-code']);
        isset($dataFromUrl['is-joint']) && $account->setIsJointAccount($dataFromUrl['is-joint']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // crete and handle form
        $form = $this->createForm(new FormDir\Odr\BankAccountType($step), $account);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                $stepUrlData['type'] = $account->getAccountType();
            }

            if ($step == 2) {
                $stepUrlData['bank'] = $account->getBank();
                $stepUrlData['number'] = $account->getAccountNumber();
                $stepUrlData['sort-code'] = $account->getSortCode();
                $stepUrlData['is-joint'] = $account->getIsJointAccount();
            }

            // last step: save
            if ($step == $totalSteps) {
                if ($accountId) {
                    $this->getRestClient()->put('/odr/account/' . $accountId, $account, ['bank-account']);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );

                    return $this->redirectToRoute('odr_bank_accounts_summary', ['odrId' => $odrId]);
                } else {
                    $this->getRestClient()->post('odr/' . $odrId . '/account', $account, ['bank-account']);

                    return $this->redirectToRoute('odr_bank_accounts_add_another', ['odrId' => $odrId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'account' => $account,
            'odr' => $odr,
            'step' => $step,
            'odrStatus' => new OdrStatusService($odr),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/odr/{odrId}/bank-accounts/add_another", name="odr_bank_accounts_add_another")
     * @Template("AppBundle:Odr/BankAccount:add_another.html.twig")
     */
    public function addAnotherAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('odr-bank-accounts'), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('odr_bank_accounts_step', ['odrId' => $odrId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('odr_bank_accounts_summary', ['odrId' => $odrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/bank-accounts/summary", name="odr_bank_accounts_summary")
     *
     * @param int $odrId
     * @Template()
     *
     * @return array
     */
    public function summaryAction($odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if (count($odr->getBankAccounts()) === 0) {
            return $this->redirectToRoute('odr_bank_accounts', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/bank-account/{accountId}/delete", name="odr_bank_account_delete")
     *
     * @param int $odrId
     * @param int $accountId
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $odrId, $accountId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Bank account deleted'
        );

        if ($odr->hasBankAccountWithId($accountId)) {
            $this->getRestClient()->delete("/odr/account/{$accountId}");
        }

        return $this->redirect($this->generateUrl('odr_bank_accounts_summary', ['odrId' => $odrId]));
    }
}
