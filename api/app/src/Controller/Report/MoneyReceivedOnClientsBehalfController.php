<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Repository\MoneyReceivedOnClientsBehalfRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MoneyReceivedOnClientsBehalfController extends RestController
{
    public function __construct(
        private readonly MoneyReceivedOnClientsBehalfRepository $reportMoneyRepository,
        private readonly RestFormatter $formatter,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/report/money-type/delete/{moneyTypeId}', name: 'delete_money_type', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(Request $request, string $moneyTypeId): array
    {
        $groups = $request->request->has('groups') ? $request->request->all('groups') : ['client-benefits-check', 'report'];
        $this->formatter->setJmsSerialiserGroups($groups);
        $this->reportMoneyRepository->delete($moneyTypeId);

        return [];
    }
}
