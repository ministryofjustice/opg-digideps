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

    #[Route(path: '/admin/cleanup/reports/download/{kind}', name: 'admin_cleanup_reports_download', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function reportGenerateActions(string $kind): Response
    {
        if (!in_array($kind, ['actions', 'problems'])) {
            return new Response(status: 404);
        }
        $csv = $this->restClient->setTimeout(3600)->get("/admin/cleanup/reports/download/{$kind}", 'raw');
        if ($csv instanceof Stream) {
            $csv = $csv->getContents();
        }
        return new Response($csv, headers: [
            'Cache-Control' => 'private',
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"cleanup-report-{$kind}.csv\";",
            'Content-length' => strlen($csv),
        ]);
    }

    #[Route(path: '/admin/cleanup/reports/plan', methods: ['GET', 'POST'])]
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
                    if (!$model->notDryRun && $model->caseNumber === null) {
                        $this->addFlash('error', 'Via web page planning is only possible for a specific list of cases.');
                    } else {
                        $result = $this->restClient->setTimeout(3600)->post('/admin/cleanup/reports/plan', serialize($model), expectedResponseType: 'raw');
                        if ($result instanceof Stream) {
                            $result = $result->getContents();
                        }
                        $result = json_decode($result, true);
                        if ($model->notDryRun) {
                            $this->addFlash('notice', "Affected {$result['count']} rows in `court_order_report`");
                        }
                        if ($model->caseNumber !== null) {
                            $this->addFlash($result['ok'] ? 'notice' : 'error', $result['ok'] ? 'Planning successful' : "Planning error: {$result['message']}");
                        }
                        $model = new CleanupModel(null, false, false);
                        $form = $this->createForm(CleanupType::class, $model);
                    }
                }
            }
        }

        return $this->render('@App/Admin/Cleanup/index.html.twig', [
            'form' => $form,
        ]);
    }
}
