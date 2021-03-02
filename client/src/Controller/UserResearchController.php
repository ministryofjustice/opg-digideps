<?php declare(strict_types=1);


namespace App\Controller;

use App\Entity\UserResearch\UserResearchResponse;
use App\Exception\ReportNotSubmittedException;
use App\Form\UserResearchResponseType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\Internal\UserResearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class UserResearchController extends AbstractController
{
    private UserResearchApi $userResearchApi;
    private ReportApi $reportApi;
    private TranslatorInterface $translator;
    private FormFactoryInterface $formFactory;

    public function __construct(
        UserResearchApi $userResearchApi,
        ReportApi $reportApi,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory
    ) {
        $this->userResearchApi = $userResearchApi;
        $this->reportApi = $reportApi;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/report/{reportId}/post_submission_user_research", name="report_post_submission_user_research")
     * @Template("@App/UserResearch/postSubmissionUserResearch.html.twig")
     * @param $reportId
     * @return array
     */
    public function postSubmissionUserResearch(Request $request, int $reportId)
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
            $this->userResearchApi->createPostSubmissionUserResearch($form->getData());

            // change to thank you page
            return $this->redirect($this->generateUrl('report_post_submission_user_research', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
