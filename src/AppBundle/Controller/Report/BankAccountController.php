<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use AppBundle\Service\StepRedirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BankAccountController extends AbstractController
{
    const STEPS = 4;

    /**
     * @Route("/report/{reportId}/bank-accounts/start", name="bank_accounts")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        if (count($report->getAccounts())) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * //TODO refactor when assets is implemented too
     *
     * @Route("/report/{reportId}/bank-account/step{step}/{accountId}", name="bank_accounts_step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step, $accountId = null)
    {
        if ($step < 1 || $step > self::STEPS) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutePrefix('bank_accounts_')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps(self::STEPS)
            ->setRouteBaseParams(['reportId'=>$reportId, 'accountId' => $accountId]);


        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->getRestClient()->get('report/account/' . $accountId, 'Report\\Account');
        } else {
            $account = new EntityDir\Report\Account();
            $account->setReport($report);
        }

        // add URL-data into model
        isset($dataFromUrl['type']) && $account->setAccountType($dataFromUrl['type']);
        isset($dataFromUrl['bank']) && $account->setBank($dataFromUrl['bank']);
        isset($dataFromUrl['number']) && $account->setAccountNumber($dataFromUrl['number']);
        isset($dataFromUrl['sort-code']) && $account->setSortCode($dataFromUrl['sort-code']);
        isset($dataFromUrl['is-joint']) && $account->setIsJointAccount($dataFromUrl['is-joint']);
        isset($dataFromUrl['closing-balance']) && $account->setOpeningBalance($dataFromUrl['closing-balance']);
        isset($dataFromUrl['opening-balance']) && $account->setClosingBalance($dataFromUrl['opening-balance']);

        // crete and handle form
        $form = $this->createForm(new FormDir\Report\BankAccountType($step), $account);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // if closing balance is set to non-zero values, un-close the account
            /*if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }*/

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

            if ($step == 3) {
                $stepUrlData['closing-balance'] = $account->getOpeningBalance();
                $stepUrlData['opening-balance'] = $account->getClosingBalance();
            }

            // 4th step only if closing balance is equals to 0
            $isLastStep = $step == self::STEPS
                || ($step == (self::STEPS - 1) && !$account->isClosingBalanceZero());

            // last step: save
            if ($isLastStep) {
                if ($accountId) {
                    $this->getRestClient()->put('/account/' . $accountId, $account, ['account']);
                } else {
                    $this->getRestClient()->post('report/' . $reportId . '/account', $account, ['account']);
                }
            }

            if ($isLastStep) {
                return $this->redirectToRoute('bank_accounts_add_another', ['reportId' => $reportId]);
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'account' => $account,
            'report' => $report,
            'step' => $step,
            'reportStatus' => new ReportStatusService($report),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-accounts/add_another", name="bank_accounts_add_another")
     * @Template("AppBundle:Report/BankAccount:add_another.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\BankAccountAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('bank_accounts_step', ['reportId' => $reportId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-accounts/summary", name="bank_accounts_summary")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        if (count($report->getAccounts()) === 0) {
            return $this->redirectToRoute('bank_accounts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-account/{accountId}/delete", name="bank_account_delete")
     *
     * @param int $reportId
     * @param int $accountId
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $accountId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);

        if ($report->hasAccountWithId($accountId)) {
            $this->getRestClient()->delete("/account/{$accountId}");
        }

        return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
    }
}
