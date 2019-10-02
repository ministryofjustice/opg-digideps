<?php

namespace AppBundle\Controller;

use AppBundle\Service\CsvUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/org")
 */
class OrgController extends RestController
{
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

        ini_set('memory_limit', '1024M');

        $data = CsvUploader::decompressData($request->getContent());
        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No records received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $pa = $this->get('org_service');

        try {
            $ret = $pa->addFromCasrecRows($data);
            return $ret;
        } catch (\Throwable $e) {
            $added = ['prof_users' => [], 'pa_users' => [], 'clients' => [], 'reports' => []];
            return ['added'=>$added, 'errors' => [$e->getMessage(), 'warnings'=>[]]];
        }
    }
}
