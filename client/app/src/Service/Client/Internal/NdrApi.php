<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Exception\RestClientException;
use App\Service\Client\RestClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NdrApi
{
    private const GET_NDR_ENDPOINT = 'ndr/%s';

    public function __construct(private readonly RestClient $restClient)
    {
    }

    public function submit(Ndr $ndrToSubmit, Document $ndrPdfDocument): void
    {
    }

    public function getNdr(int $ndrId, array $groups = []): Ndr
    {
        $groups[] = 'ndr';

        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching

        try {
            $ndr = $this->restClient->get(
                sprintf(self::GET_NDR_ENDPOINT, $ndrId),
                'Ndr\\Ndr',
                $groups
            );
        } catch (RestClientException $e) {
            if (403 === $e->getStatusCode() || 404 === $e->getStatusCode()) {
                throw new NotFoundHttpException($e->getData()['message']);
            } else {
                throw $e;
            }
        }

        return $ndr;
    }
}
