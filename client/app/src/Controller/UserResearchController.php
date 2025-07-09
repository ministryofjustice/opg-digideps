<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ReportNotSubmittedException;
use App\Form\UserResearchResponseType;
use App\Service\Client\Internal\NdrApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\Internal\UserResearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserResearchController extends AbstractController
{
    public function __construct(
        private readonly UserResearchApi $userResearchApi,
        private readonly ReportApi $reportApi,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory,
        private readonly NdrApi $ndrApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/post_submission_user_research", name="report_post_submission_user_research")
     * @Route("/ndr/{ndrId}/post_submission_user_research", name="ndr_post_submission_user_research")
     *
     * @Template("@App/UserResearch/postSubmissionUserResearch.html.twig")
     */
    public function postSubmissionUserResearch(Request $request, ?int $reportId = null, ?int $ndrId = null): array|RedirectResponse
    {
        $report = !is_null($reportId) ? $this->reportApi->getReport($reportId, ['report']) : $this->ndrApi->getNdr($ndrId, ['ndr']);

        // check status
        if (!$report->getSubmitted()) {
            $message = $this->translator->trans('report.submissionExceptions.submitted', [], 'validators');
            throw new ReportNotSubmittedException($message);
        }

        $form = $this->formFactory->create(UserResearchResponseType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $formData['satisfaction'] = $request->get('satisfactionId');
            $this->userResearchApi->createPostSubmissionUserResearch($formData);

            $routeName = sprintf('%s_user_research_submitted', $reportId ? 'report' : 'ndr');
            $routeParams = $reportId ? ['reportId' => $reportId] : ['ndrId' => $ndrId];

            return $this->redirect($this->generateUrl($routeName, $routeParams));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/post_submission_user_research/submitted", name="report_user_research_submitted")
     * @Route("/ndr/{ndrId}/post_submission_user_research/submitted", name="ndr_user_research_submitted")
     *
     * @Template("@App/UserResearch/userResearchSubmitted.html.twig")
     */
    public function userResearchSubmitted(?int $reportId = null, ?int $ndrId = null): array|RedirectResponse
    {
        $report = !is_null($reportId) ? $this->reportApi->getReport($reportId, ['report']) : $this->ndrApi->getNdr($ndrId, ['ndr-client', 'client-id']);

        // check status
        if (!$report->getSubmitted()) {
            $message = $this->translator->trans('report.submissionExceptions.submitted', [], 'validators');
            throw new ReportNotSubmittedException($message);
        }

        return [
            'report' => $report,
            'homePageName' => $this->getUser()->isLayDeputy() ? 'lay_home' : 'org_dashboard',
        ];
    }
}
