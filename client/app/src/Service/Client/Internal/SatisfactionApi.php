<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Model\FeedbackReport;
use OPG\Digideps\Frontend\Service\Client\RestClientInterface;

class SatisfactionApi
{
    private const string CREATE_GENERAL_FEEDBACK_ENDPOINT = 'satisfaction/public';
    private const string CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT = 'satisfaction';

    private RestClientInterface $restClient;

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

    public function createPostSubmissionFeedback(FeedbackReport $formResponse, string $reportType, ?int $reportId = null): int
    {
        $feedbackData = [
            'score' => $formResponse->getSatisfactionLevel(),
            'comments' => empty($formResponse->getComments()) ? 'Not provided' : $formResponse->getComments(),
            'reportType' => $reportType,
            'reportId' => $reportId,
        ];

        /** @var int $satisfactionId */
        $satisfactionId = $this->restClient->post(self::CREATE_POST_SUBMISSION_FEEDBACK_ENDPOINT, $feedbackData);

        return $satisfactionId;
    }
}
