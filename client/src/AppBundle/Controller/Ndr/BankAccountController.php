<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\NdrStatusService;
use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BankAccountController extends AbstractController
{
    private static $jmsGroups = ['ndr-account'];

    /**
     * @var ReportApi
     */
    private $reportApi;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var StepRedirector
     */
    private $stepRedirector;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient,
        StepRedirector $stepRedirector
    )
    {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/ndr/{ndrId}/bank-accounts", name="ndr_bank_accounts")
     * @Template("AppBundle:Ndr/BankAccount:start.html.twig")
     *
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function startAction($ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getBankAccountsState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-account/step{step}/{accountId}", name="ndr_bank_accounts_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Ndr/BankAccount:step.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     * @param $step
     * @param null $accountId
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $ndrId, $step, $accountId = null)
    {
        $totalSteps = 3;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('ndr_bank_accounts', 'ndr_bank_accounts_step', 'ndr_bank_accounts_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId'=>$ndrId, 'accountId' => $accountId]);

        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->restClient->get('ndr/account/' . $accountId, 'Ndr\\BankAccount');
        } else {
            $account = new EntityDir\Ndr\BankAccount();
            $account->setNdr($ndr);
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
        $form = $this->createForm(FormDir\Ndr\BankAccountType::class, $account, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
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
                    $this->restClient->put('/ndr/account/' . $accountId, $account, ['bank-account']);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );

                    return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
                } else {
                    $this->restClient->post('ndr/' . $ndrId . '/account', $account, ['bank-account']);

                    return $this->redirectToRoute('ndr_bank_accounts_add_another', ['ndrId' => $ndrId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'account' => $account,
            'ndr' => $ndr,
            'step' => $step,
            'ndrStatus' => new NdrStatusService($ndr),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'gaCustomUrl' => $request->getPathInfo() // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-accounts/add_another", name="ndr_bank_accounts_add_another")
     * @Template("AppBundle:Ndr/BankAccount:add_another.html.twig")
     */
    public function addAnotherAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $ndr, ['translation_domain' => 'ndr-bank-accounts']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_bank_accounts_step', ['ndrId' => $ndrId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-accounts/summary", name="ndr_bank_accounts_summary")
     *
     * @param $ndrId
     * @Template("AppBundle:Ndr/BankAccount:summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getBankAccountsState()['state'] == NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_bank_accounts', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-account/{accountId}/delete", name="ndr_bank_account_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     * @param $accountId
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $ndrId, $accountId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Bank account deleted'
            );

            if ($ndr->hasBankAccountWithId($accountId)) {
                $this->restClient->delete("/ndr/account/{$accountId}");
            }

            return $this->redirect($this->generateUrl('ndr_bank_accounts_summary', ['ndrId' => $ndrId]));
        }

        $account = $this->restClient->get('ndr/account/' . $accountId, 'Ndr\\BankAccount');

        return [
            'translationDomain' => 'ndr-bank-accounts',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.accountType', 'value' => $account->getAccountTypeText()],
                ['label' => 'deletePage.summary.accountNumber', 'value' => '****' . $account->getAccountNumber()],
                ['label' => 'deletePage.summary.balance', 'value' => $account->getBalanceOnCourtOrderDate(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('ndr_bank_accounts_summary', ['ndrId' => $ndrId]),
        ];
    }
}
