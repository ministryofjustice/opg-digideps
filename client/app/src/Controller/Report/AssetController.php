<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\Status;
use App\Form;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController
{
    private static array $jmsGroups = [
        'asset',
        'asset-state',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    #[Route(path: '/report/{reportId}/assets', name: 'assets')]
    #[Template('@App/Report/Asset/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED != $report->getStatus()->getAssetsState()['state']) {
            return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/assets/exist', name: 'assets_exist')]
    #[Template('@App/Report/Asset/exist.html.twig')]
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ('GET' == $request->getMethod() && $report->getAssets()) { // if assets are added, set form default to "Yes"
            $report->setNoAssetToAdd(0);
        }
        $form = $this->createForm(Form\YesNoType::class, $report, [
            'field' => 'noAssetToAdd',
            'translation_domain' => 'report-assets',
            'choices' => ['Yes' => 0, 'No' => 1],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($report->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId]);
                case 1: // no
                    $this->restClient->put('report/' . $reportId, $report, ['noAssetsToAdd']);

                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('assets', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('assets_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/assets/step-type', name: 'assets_type')]
    #[Template('@App/Report/Asset/type.html.twig')]
    public function typeAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        /** @var FormInterface $form */
        $form = $this->createForm(Form\Report\Asset\AssetTypeTitle::class, new AssetOther());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->getData()->getTitle();
            return match ($title) {
                'Property' => $this->redirect($this->generateUrl('assets_property_step', ['reportId' => $reportId])),
                default => $this->redirect($this->generateUrl('asset_other_add', ['reportId' => $reportId, 'title' => $title])),
            };
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('assets', ['reportId' => $report->getId()]),
            'skipLink' => null,
        ];
    }


    #[Route(path: '/report/{reportId}/assets/other/{title}/add', name: 'asset_other_add')]
    #[Template('@App/Report/Asset/Other/add.html.twig')]
    public function otherAddAction(Request $request, int $reportId, string $title): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);
        $asset = new AssetOther();
        $asset->setTitle($title);
        $asset->setReport($report);

        /** @var FormInterface $form */
        $form = $this->createForm(Form\Report\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            $this->restClient->post("report/$reportId/asset", $asset);

            /** @var FormInterface $addAnother */
            $addAnother = $form['addAnother'];
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId, 'from' => 'another']);
                case 'no':
                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('assets_type', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
            // avoid sending query string to GA containing user's data
            'gaCustomUrl' => $this->generateUrl('asset_other_add', ['reportId' => $reportId, 'title' => 'type']),
        ];
    }

    #[Route(path: '/report/{reportId}/assets/other/edit/{assetId}', name: 'asset_other_edit')]
    #[Template('@App/Report/Asset/Other/edit.html.twig')]
    public function otherEditAction(Request $request, int $reportId, ?int $assetId = null): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);
        if ($assetId) {
            $asset = $this->restClient->get("report/$reportId/asset/$assetId", 'Report\\Asset');
        } else {
            $asset = new AssetOther();
            $asset->setReport($report);
        }

        /** @var FormInterface $form */
        $form = $this->createForm(Form\Report\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            $this->restClient->put("report/$reportId/asset/$assetId", $asset);
            $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('assets_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/assets/property/{assetId}', name: 'assets_property_step')]
    #[Template('@App/Report/Asset/Property/step.html.twig')]
    public function propertyStepAction(Request $request, int $reportId, ?int $assetId = null): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($assetId) { // edit asset
            $assets = array_filter($report->getAssets(), fn($t): bool => $t->getId() == $assetId);
            $asset = array_shift($assets);
        } else { // add new asset
            $asset = new AssetProperty();
        }

        /** @var FormInterface $form */
        $form = $this->createForm(Form\Report\Asset\AssetTypeProperty::class, $asset);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            /* @var AssetProperty $asset */
            $asset = $form->getData();

            // edit mode: save immediately and go back to summary page
            if ($assetId) {
                $this->restClient->put("report/$reportId/asset/$assetId", $asset);
                $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

                /** @var FormInterface $addAnother */
                $addAnother = $form['addAnother'];
                switch ($addAnother->getData()) {
                    case 'yes':
                        return $this->redirectToRoute('assets_type', ['reportId' => $reportId, 'from' => 'another']);
                    case 'no':
                        return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
                }
            }

            $this->restClient->post("report/$reportId/asset", $asset);

            /** @var FormInterface $addAnother */
            $addAnother = $form['addAnother'];
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId, 'from' => 'another']);
                case 'no':
                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'asset' => $asset,
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('assets_summary', ['reportId' => $reportId]),
            'skipLink' => null,
            'gaCustomUrl' => $request->getPathInfo(), // avoid sending query string to GA containing user's data
        ];
    }

    #[Route(path: '/report/{reportId}/assets/summary', name: 'assets_summary')]
    #[Template('@App/Report/Asset/summary.html.twig')]
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getAssetsState()['state']) {
            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/assets/{assetId}/delete', name: 'asset_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, int $assetId): array|RedirectResponse
    {
        $form = $this->createForm(Form\ConfirmDeleteType::class);
        $form->handleRequest($request);
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($report->hasAssetWithId($assetId)) {
                $this->restClient->delete("/report/$reportId/asset/$assetId");
                $request->getSession()->getFlashBag()->add('notice', 'Asset removed');
            }

            return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
        }

        $asset = $this->restClient->get("report/$reportId/asset/$assetId", 'Report\\Asset');

        if ($asset instanceof AssetProperty) {
            $summary = [
                ['label' => 'deletePage.summary.type', 'value' => 'deletePage.summary.property', 'format' => 'translate'],
                ['label' => 'deletePage.summary.address', 'value' => implode(', ', $asset->getAddressValidLines())],
                ['label' => 'deletePage.summary.value', 'value' => $asset->getValue(), 'format' => 'money'],
            ];
        } else {
            $summary = [
                ['label' => 'deletePage.summary.type', 'value' => $asset->getTitle()],
                ['label' => 'deletePage.summary.description', 'value' => $asset->getDescription()],
                ['label' => 'deletePage.summary.value', 'value' => $asset->getValue(), 'format' => 'money'],
                ['label' => 'deletePage.summary.valuationDate', 'value' => $asset->getValuationDate(), 'format' => 'date'],
            ];
        }

        return [
            'translationDomain' => 'report-assets',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('assets_summary', ['reportId' => $reportId]),
        ];
    }
}
