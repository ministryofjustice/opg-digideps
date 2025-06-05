<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Model\FeedbackReport;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;

class SatisfactionApi
{
    private const CREATE_GENERAL_FEEDBACK_ENDPOINT = 'satisfaction/public';
    private const CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT = 'satisfaction';

    /** @var RestClient */
    private $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    public function createGeneralFeedback(array $formResponse): void
    {
        $this->restClient->post(
            self::CREATE_GENERAL_FEEDBACK_ENDPOINT,
            ['score' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );
    }

    public function createPostSubmissionFeedback(FeedbackReport $formResponse, string $reportType, User $submittedByUser, ?int $reportId = null, ?int $ndrId = null): int
    {
        $feedbackData = [
            'score' => $formResponse->getSatisfactionLevel(),
            'comments' => empty($formResponse->getComments()) ? 'Not provided' : $formResponse->getComments(),
            'reportType' => $reportType,
            'reportId' => $reportId,
            'ndrId' => $ndrId,
        ];

        /** @var int $satisfactionId */
        $satisfactionId = $this->restClient->post(self::CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT, $feedbackData);

        return $satisfactionId;
    }
}
