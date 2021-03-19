<?php declare(strict_types=1);


namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;

class UserResearchApi
{
    private const CREATE_POST_SUBMISSION_USER_RESEARCH_ENDPOINT = 'user-research';

    /** @var RestClient */
    private $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    public function createPostSubmissionUserResearch(array $userResearchFormData)
    {
        $this->restClient->post(self::CREATE_POST_SUBMISSION_USER_RESEARCH_ENDPOINT, $userResearchFormData);
    }
}
