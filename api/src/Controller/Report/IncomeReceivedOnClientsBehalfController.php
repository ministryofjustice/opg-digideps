<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Repository\IncomeReceivedOnClientsBehalfRepository;
use App\Service\Formatter\RestFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IncomeReceivedOnClientsBehalfController extends RestController
{
    private IncomeReceivedOnClientsBehalfRepository $repository;

    private RestFormatter $formatter;

    public function __construct(
        IncomeReceivedOnClientsBehalfRepository $repository,
        RestFormatter $formatter
    ) {
        $this->repository = $repository;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/income-type/delete/{incomeTypeId}", methods={"DELETE"}, name="delete_income_type")
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function delete(Request $request, string $incomeTypeId)
    {
        $groups = $request->get('groups') ? $request->get('groups') : ['client-benefits-check', 'report'];
        $this->formatter->setJmsSerialiserGroups($groups);
        $this->repository->delete($incomeTypeId);

        return [];
    }
}
