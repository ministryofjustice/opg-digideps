<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Mapper\DateRangeQuery;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;
use DateTime;

class UserResearchApi
{
    private const CREATE_POST_SUBMISSION_USER_RESEARCH_ENDPOINT = 'user-research';
    private const GET_USER_RESEARCH_RESPONSES = 'user-research';

    public function __construct(private RestClientInterface $restClient)
    {
    }

    public function createPostSubmissionUserResearch(array $userResearchFormData)
    {
        $this->restClient->post(self::CREATE_POST_SUBMISSION_USER_RESEARCH_ENDPOINT, $userResearchFormData);
    }

    public function getUserResearchResponseData(?DateRangeQuery $query)
    {
        $url = self::GET_USER_RESEARCH_RESPONSES;

        if ($query) {
            $fromDate = $query->getStartDate() ? $query->getStartDate()->format('Y-m-d') : (new DateTime('-5 years'))->format('Y-m-d');
            $toDate = $query->getEndDate() ? $query->getEndDate()->format('Y-m-d') : (new DateTime('today'))->format('Y-m-d');

            $queryArray = [
                'orderBy' => $query->getOrderBy(),
                'order' => $query->getSortOrder(),
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'groups' => ['satisfaction', 'user', 'user-research'],
            ];

            $url = sprintf(
                '%s?%s',
                $url,
                http_build_query($queryArray)
            );
        }

        return $this->restClient->get($url, 'App\Entity\UserResearch\UserResearchResponse[]');
    }
}
