<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Repository\IncomeReceivedOnClientsBehalfRepository;
use App\Repository\NdrIncomeReceivedOnClientsBehalfRepository;
use App\Service\Formatter\RestFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IncomeReceivedOnClientsBehalfController extends RestController
{
    private IncomeReceivedOnClientsBehalfRepository $reportIncomeRepository;
    private NdrIncomeReceivedOnClientsBehalfRepository $ndrIncomeRepository;
    private RestFormatter $formatter;

    public function __construct(
        IncomeReceivedOnClientsBehalfRepository $reportIncomeRepository,
        NdrIncomeReceivedOnClientsBehalfRepository $ndrIncomeRepository,
        RestFormatter $formatter
    ) {
        $this->reportIncomeRepository = $reportIncomeRepository;
        $this->ndrIncomeRepository = $ndrIncomeRepository;
        $this->formatter = $formatter;
    }

    /**
     * @Route("{reportOrNdr}/income-type/delete/{incomeTypeId}", methods={"DELETE"}, name="delete_income_type")
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function delete(Request $request, string $reportOrNdr, string $incomeTypeId)
    {
        $groups = $request->get('groups') ? $request->get('groups') : ['client-benefits-check', 'report', 'ndr'];
        $this->formatter->setJmsSerialiserGroups($groups);
        'ndr' === $reportOrNdr ? $this->ndrIncomeRepository->delete($incomeTypeId) : $this->reportIncomeRepository->delete($incomeTypeId);

        return [];
    }
}
