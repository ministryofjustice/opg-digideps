<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Repository\MoneyReceivedOnClientsBehalfRepository;
use App\Repository\NdrMoneyReceivedOnClientsBehalfRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MoneyReceivedOnClientsBehalfController extends RestController
{
    public function __construct(
        private readonly MoneyReceivedOnClientsBehalfRepository $reportMoneyRepository,
        private readonly NdrMoneyReceivedOnClientsBehalfRepository $ndrMoneyRepository,
        private readonly RestFormatter $formatter,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    #[Route(path: '{reportOrNdr}/money-type/delete/{moneyTypeId}', methods: ['DELETE'], name: 'delete_money_type')]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(Request $request, string $reportOrNdr, string $moneyTypeId): array
    {
        $groups = $request->request->has('groups') ? $request->request->all('groups') : ['client-benefits-check', 'report', 'ndr'];
        $this->formatter->setJmsSerialiserGroups($groups);
        'ndr' === $reportOrNdr ? $this->ndrMoneyRepository->delete($moneyTypeId) : $this->reportMoneyRepository->delete($moneyTypeId);

        return [];
    }
}
