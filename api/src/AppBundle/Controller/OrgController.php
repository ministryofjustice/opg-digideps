<?php

namespace AppBundle\Controller;

use AppBundle\Service\CsvUploader;
use AppBundle\Service\OrgService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/org")
 */
class OrgController extends RestController
{
    /**
     * @var OrgService
     */
    private $orgService;

    public function __construct(OrgService $orgService)
    {
        $this->orgService = $orgService;
    }

    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M).
     *
     * @Route("/bulk-add", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addBulk(Request $request)
    {
        $maxRecords = 10000;

        $data = CsvUploader::decompressData($request->getContent());
        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No records received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        try {
            $ret = $this->orgService->addFromCasrecRows($data);
            return $ret;
        } catch (\Throwable $e) {
            $added = ['prof_users' => [], 'pa_users' => [], 'named-deputies' => [], 'clients' => [], 'discharged_clients' => [], 'reports' => []];
            return ['added'=>$added, 'errors' => [$e->getMessage(), 'warnings'=>[]]];
        }
    }
}
