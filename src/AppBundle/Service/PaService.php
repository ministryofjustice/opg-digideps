<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PaService
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * PaService constructor.
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param string $compressedData
     * @param FlashBagInterface $flashBag
     * @return string
     */
    public function uploadAndSetFlashMessages($compressedData, FlashBagInterface $flashBag)
    {
        $ret = $this->restClient->setTimeout(600)->post('pa/bulk-add', $compressedData);
        // MOVE TO SERVICE
        $flashBag->add(
            'notice',
            sprintf('Added %d PA users, %d clients, %d reports. Go to users tab to enable them',
                count($ret['added']['users']),
                count($ret['added']['clients']),
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
