<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\ReportInterface;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionsLinkService
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * ReportSectionsLinkService constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param  ReportInterface $report
     * @return array
     */
    private function getOptions(ReportInterface $report)
    {
        if ($report instanceof Ndr) {
            $routeParams = ['ndrId' => $report->getId()];
            return [
                ['section' => 'visitsCare', 'link' => $this->router->generate('ndr_visits_care', $routeParams)],
                ['section' => 'deputyExpenses', 'link' => $this->router->generate('ndr_deputy_expenses', $routeParams)],
                ['section' => 'incomeBenefits', 'link' => $this->router->generate('ndr_income_benefits', $routeParams)],
                ['section' => 'bankAccounts', 'link' => $this->router->generate('ndr_bank_accounts', $routeParams)],
                ['section' => 'assets', 'link' => $this->router->generate('ndr_assets', $routeParams)],
                ['section' => 'debts', 'link' => $this->router->generate('ndr_debts', $routeParams)],
                ['section' => 'actions', 'link' => $this->router->generate('ndr_actions', $routeParams)],
                ['section' => 'otherInfo', 'link' => $this->router->generate('ndr_other_info', $routeParams)],
            ];
        }

        $routeParams = ['reportId' => $report->getId()];

        // define sections and links (not following the order with which are presented in the dashboards)
        $allSectionsAvailable = [
            'actions' => ['section' => 'actions', 'link' => $this->router->generate('actions', $routeParams)],
            'assets' => ['section' => 'assets', 'link' => $this->router->generate('assets', $routeParams)],
            'bankAccounts' => ['section' => 'bankAccounts', 'link' => $this->router->generate('bank_accounts', $routeParams)],
            'contacts' => ['section' => 'contacts', 'link' => $this->router->generate('contacts', $routeParams)],
            'debts' => ['section' => 'debts', 'link' => $this->router->generate('debts', $routeParams)],
            'deputyExpenses' => ['section' => 'deputyExpenses', 'link' => $this->router->generate('deputy_expenses', $routeParams)],
            'decisions' => ['section' => 'decisions', 'link' => $this->router->generate('decisions', $routeParams)],
            'documents' => ['section' => 'documents', 'link' => $this->router->generate('documents', $routeParams)],
            'gifts' => ['section' => 'gifts', 'link' => $this->router->generate('gifts', $routeParams)],
            'lifestyle' => ['section' => 'lifestyle', 'link' => $this->router->generate('lifestyle', $routeParams)],
            'moneyTransfers' => ['section' => 'moneyTransfers', 'link' => $this->router->generate('money_transfers', $routeParams)],
            'moneyIn' => ['section' => 'moneyIn', 'link' => $this->router->generate('money_in', $routeParams)],
            'moneyOut' => ['section' => 'moneyOut', 'link' => $this->router->generate('money_out', $routeParams)],
            'moneyInShort' => ['section' => 'moneyInShort', 'link' => $this->router->generate('money_in_short', $routeParams)],
            'moneyOutShort' => ['section' => 'moneyOutShort', 'link' => $this->router->generate('money_out_short', $routeParams)],
            'otherInfo' => ['section' => 'otherInfo', 'link' => $this->router->generate('other_info', $routeParams)],
            'profDeputyCosts' => ['section' => 'profDeputyCosts', 'link' => $this->router->generate('prof_deputy_costs', $routeParams)],
            'profDeputyCostsEstimate' => ['section' => 'profDeputyCostsEstimate', 'link' => $this->router->generate('prof_deputy_costs_estimate', $routeParams)],
            'paDeputyExpenses' => ['section' => 'paFeeExpense', 'link' => $this->router->generate('pa_fee_expense', $routeParams)],
            'profCurrentFees' => ['section' => 'profCurrentFees', 'link' => $this->router->generate('prof_current_fees', $routeParams)],
            'visitsCare' => ['section' => 'visitsCare', 'link' => $this->router->generate('visits_care', $routeParams)],
        ];

        //TODO ask the business if links can follow a single order


        // defined order for Client profile page (PROF or PA)
        if ($report->hasSection('paDeputyExpenses')) { // PAs
            // PAs
            $sectionIdOrder = [
                'decisions', 'contacts', 'visitsCare', 'lifestyle',
                'paDeputyExpenses',
                'gifts',
                'actions', 'otherInfo',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'bankAccounts',
                'moneyTransfers', 'moneyIn', 'moneyOut',
                'moneyInShort', 'moneyOutShort',
                'assets', 'debts',
                'documents'
            ];
        } elseif ($report->hasSection('profDeputyCosts')) { // Professionals
            $sectionIdOrder = [
                'decisions', 'contacts', 'visitsCare',
                'bankAccounts',
                'moneyIn', 'moneyInShort', 'moneyTransfers', 'moneyOut','moneyOutShort',
                'lifestyle',
                'gifts',
                'assets', 'debts',
                'profCurrentFees',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions', 'otherInfo',
                'documents'
                ];
        } else { // Lay
            $sectionIdOrder = [
                'decisions', 'contacts', 'visitsCare', 'lifestyle',
                'bankAccounts',
                'deputyExpenses', // Lay
                'gifts',
                'moneyTransfers', 'moneyIn', 'moneyOut', 'moneyInShort', 'moneyOutShort',
                'assets', 'debts',
                'actions', 'otherInfo',
                'documents'
            ];
        }

        $config = [];

        // cycle order and add config for each one
        foreach ($sectionIdOrder as $sectionId) {
            if ($report->hasSection($sectionId)) {
                $config[] = $allSectionsAvailable[$sectionId];
            }
        }

        return $config;
    }

    /**
     * @param ReportInterface $report
     * @param $sectionId
     * @param  int   $offset
     * @return array empty if it's the last or first section
     */
    public function getSectionParams(ReportInterface $report, $sectionId, $offset = 0)
    {
        $config = $this->getOptions($report);

        foreach ($config as $index => $currentSectionParams) {
            if ($currentSectionParams['section'] == $sectionId) {
                return isset($config[$index + $offset]) ? $config[$index + $offset] : [];
            }
        }

        return [];
    }
}
