<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Event\GeneralFeedbackSubmittedEvent;
use App\Event\PostSubmissionFeedbackSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\FeedbackReport;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;

class SatisfactionApi
{
    private const CREATE_GENERAL_FEEDBACK_ENDPOINT = 'satisfaction/public';
    private const CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT = 'satisfaction';

    /** @var RestClient */
    private $restClient;

    /** @var ObservableEventDispatcher */
    private $eventDispatcher;

    public function __construct(RestClientInterface $restClient, ObservableEventDispatcher $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createGeneralFeedback(array $formResponse): void
    {
        $this->restClient->post(
            self::CREATE_GENERAL_FEEDBACK_ENDPOINT,
            ['score' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formResponse);
        $this->eventDispatcher->dispatch($event, GeneralFeedbackSubmittedEvent::NAME);
    }

    /**
     * @param int $reportId
     */
    public function createPostSubmissionFeedback(FeedbackReport $formResponse, string $reportType, User $submittedByUser, ?int $reportId = null, ?int $ndrId = null): int
    {
        $feedbackData = [
            'score' => $formResponse->getSatisfactionLevel(),
            'comments' => empty($formResponse->getComments()) ? 'Not provided' : $formResponse->getComments(),
            'reportType' => $reportType,
            'reportId' => $reportId,
            'ndrId' => $ndrId,
        ];

        $satisfactionId = $this->restClient->post(self::CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT, $feedbackData);

        $event = (new PostSubmissionFeedbackSubmittedEvent($formResponse, $submittedByUser));
        $this->eventDispatcher->dispatch($event, PostSubmissionFeedbackSubmittedEvent::NAME);

        return $satisfactionId;
    }
}
