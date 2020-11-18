<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Event\PostSubmissionFeedbackSubmittedEvent;
use AppBundle\Model\FeedbackReport;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SatisfactionApi
{
    private const CREATE_GENERAL_FEEDBACK_ENDPOINT = 'satisfaction/public';
    private const CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT = 'satisfaction';

    /** @var RestClient */
    private $restClient;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(RestClientInterface $restClient, EventDispatcherInterface $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $formResponse
     */
    public function createGeneralFeedback(array $formResponse): void
    {
        $this->restClient->post(
            self::CREATE_GENERAL_FEEDBACK_ENDPOINT,
            ['score' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formResponse);
        $this->eventDispatcher->dispatch(GeneralFeedbackSubmittedEvent::NAME, $event);
    }

    /**
     * @param array $formResponse
     */
    public function createPostSubmissionFeedback(FeedbackReport $formResponse, string $reportType, User $submittedByUser): void
    {
        $feedbackData = [
            'score' => $formResponse->getSatisfactionLevel(),
            'comments' => empty($formResponse->getComments()) ? 'Not provided' : $formResponse->getComments(),
            'reportType' => $reportType,
        ];

        $this->restClient->post(self::CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT, $feedbackData);

        $event = (new PostSubmissionFeedbackSubmittedEvent($formResponse, $submittedByUser));
        $this->eventDispatcher->dispatch(PostSubmissionFeedbackSubmittedEvent::NAME, $event);
    }
}
