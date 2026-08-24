<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Admin\Cleanup;

use GuzzleHttp\Psr7\Stream;
use OPG\Digideps\Common\Cleanup\CleanupModel;
use OPG\Digideps\Frontend\Controller\AbstractController;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CleanupController extends AbstractController
{
    public function __construct(private readonly RestClient $restClient)
    {
    }

    #[Route(path: '/admin/cleanup/reports', methods: ['GET', 'POST'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function report(Request $request): Response
    {
        $model = new CleanupModel(null, false, false);
        $form = $this->createForm(CleanupType::class, $model);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $model = $form->getData();
                if ($model instanceof CleanupModel) {
                    $csv = $this->restClient->post('/admin/cleanup/clients/reports', serialize($model), expectedResponseType: 'raw');
                    if ($csv instanceof Stream) {
                        $csv = $csv->getContents();
                    }
                    if (!is_string($csv)) {
                        throw new \LogicException();
                    }
                    return new Response($csv, headers: [
                        'Cache-Control' => 'private',
                        'Content-type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="cleanup-report.csv";',
                        'Content-length' => strlen($csv),
                    ]);
                }
            }
        }

        return $this->render('@App/Admin/Cleanup/index.html.twig', [
            'form' => $form,
        ]);
    }
}
