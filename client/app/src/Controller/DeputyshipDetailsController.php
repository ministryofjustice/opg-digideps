<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\Internal\UserApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DeputyshipDetailsController extends AbstractController
{
    public function __construct(
        private readonly UserApi $userApi,
    ) {
    }

    /**
     * @Route("/deputyship-details/clients", name="deputyship_details_clients", methods={"GET"})
     *
     * @Template("@App/DeputyshipDetails/clients.html.twig")
     */
    public function clientsAction(Request $request): array
    {
        return [];
    }
}
