<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Frontend\Exception\ReportNotSubmittedException;
use OPG\Digideps\Frontend\Form\UserResearchResponseType;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\Internal\UserResearchApi;
use Symfony\Bridge\Twig\Attribute\Template;
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
    ) {
    }

    #[Route(path: '/report/{reportId}/post_submission_user_research', name: 'report_post_submission_user_research')]
    #[Template('@App/UserResearch/postSubmissionUserResearch.html.twig')]
    public function postSubmissionUserResearch(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReport($reportId, ['report']);

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

            $routeParams = ['reportId' => $reportId];

            return $this->redirect($this->generateUrl('report_user_research_submitted', $routeParams));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/report/{reportId}/post_submission_user_research/submitted', name: 'report_user_research_submitted')]
    #[Template('@App/UserResearch/userResearchSubmitted.html.twig')]
    public function userResearchSubmitted(int $reportId): array
    {
        $report = $this->reportApi->getReport($reportId, ['report']);

        // check status
        if (!$report->getSubmitted()) {
            $message = $this->translator->trans('report.submissionExceptions.submitted', [], 'validators');
            throw new ReportNotSubmittedException($message);
        }

        return [
            'report' => $report,
            'homePageName' => $this->getUser()->isLayDeputy() ? 'courtorders_for_deputy' : 'org_dashboard',
        ];
    }
}
