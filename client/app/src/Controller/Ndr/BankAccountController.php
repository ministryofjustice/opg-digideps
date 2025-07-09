<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BankAccountController extends AbstractController
{
    private static $jmsGroups = ['ndr-account'];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly RestClient $restClient,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    /**
     * @Route("/ndr/{ndrId}/bank-accounts", name="ndr_bank_accounts")
     *
     * @Template("@App/Ndr/BankAccount/start.html.twig")
     */
    public function startAction(int $ndrId): array|RedirectResponse
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getBankAccountsState()['state']) {
            return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-account/step{step}/{accountId}", name="ndr_bank_accounts_step", requirements={"step":"\d+"})
     *
     * @Template("@App/Ndr/BankAccount/step.html.twig")
     */
    public function stepAction(Request $request, int $ndrId, int $step, ?int $accountId = null): array|RedirectResponse
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
            ->setRouteBaseParams(['ndrId' => $ndrId, 'accountId' => $accountId]);

        // create (add mode) or load account (edit mode)
        if ($accountId) {
            /** @var EntityDir\Ndr\BankAccount $account */
            $account = $this->restClient->get('ndr/account/'.$accountId, 'Ndr\\BankAccount');
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
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(FormDir\Ndr\BankAccountType::class, $account, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if (1 == $step) {
                $stepUrlData['type'] = $account->getAccountType();
            }

            if (2 == $step) {
                $stepUrlData['bank'] = $account->getBank();
                $stepUrlData['number'] = $account->getAccountNumber();
                $stepUrlData['sort-code'] = $account->getSortCode();
                $stepUrlData['is-joint'] = $account->getIsJointAccount();
            }

            // last step: save
            if ($step == $totalSteps) {
                if ($accountId) {
                    $this->restClient->put('/ndr/account/'.$accountId, $account, ['bank-account']);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );

                    return $this->redirectToRoute('ndr_bank_accounts_summary', ['ndrId' => $ndrId]);
                } else {
                    $this->restClient->post('ndr/'.$ndrId.'/account', $account, ['bank-account']);

                    return $this->redirectToRoute('ndr_bank_accounts_add_another', ['ndrId' => $ndrId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData,
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
            'gaCustomUrl' => $request->getPathInfo(), // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-accounts/add_another", name="ndr_bank_accounts_add_another")
     *
     * @Template("@App/Ndr/BankAccount/add_another.html.twig")
     */
    public function addAnotherAction(Request $request, int $ndrId): array|RedirectResponse
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
     * @Template("@App/Ndr/BankAccount/summary.html.twig")
     */
    public function summaryAction(int $ndrId): array|RedirectResponse
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getBankAccountsState()['state']) {
            return $this->redirectToRoute('ndr_bank_accounts', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/bank-account/{accountId}/delete", name="ndr_bank_account_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     */
    public function deleteAction(Request $request, int $ndrId, int $accountId): array|RedirectResponse
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

        $account = $this->restClient->get('ndr/account/'.$accountId, 'Ndr\\BankAccount');

        return [
            'translationDomain' => 'ndr-bank-accounts',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.accountType', 'value' => $account->getAccountTypeText()],
                ['label' => 'deletePage.summary.accountNumber', 'value' => '****'.$account->getAccountNumber()],
                ['label' => 'deletePage.summary.balance', 'value' => $account->getBalanceOnCourtOrderDate(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('ndr_bank_accounts_summary', ['ndrId' => $ndrId]),
        ];
    }
}
