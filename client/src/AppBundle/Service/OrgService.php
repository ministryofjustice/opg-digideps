<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class OrgService
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param  string            $compressedData
     * @param  FlashBagInterface $flashBag
     * @return string
     */
    public function uploadAndSetFlashMessages($compressedData, FlashBagInterface $flashBag)
    {
        $ret = $this->restClient->setTimeout(600)->post('org/bulk-add', $compressedData);
        // MOVE TO SERVICE
        $flashBag->add(
            'notice',
            sprintf('Added %d clients, %d named deputies and %d reports.',
                count($ret['added']['clients']),
                count($ret['added']['named_deputies']),
                count($ret['added']['reports'])
            )
        );

        $errors = isset($ret['errors']) ? $ret['errors'] : [];
        $warnings = isset($ret['warnings']) ? $ret['warnings'] : [];
        if (!empty($errors)) {
            $flashBag->add(
                'error',
                implode('<br/>', $errors)
            );
        }

        if (!empty($warnings)) {
            $flashBag->add(
                'warning',
                implode('<br/>', $warnings)
            );
        }

        return $ret;
    }
}
