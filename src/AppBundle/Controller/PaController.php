<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\PaService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/pa")
 */
class PaController extends RestController
{
    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M).
     *
     * @Route("/bulk-add")
     * @Method({"POST"})
     */
    public function addBulk(Request $request)
    {
        $maxRecords = 10000;
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        ini_set('memory_limit', '1024M');

        $data = CsvUploader::decompressData($request->getContent());
        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No records received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $pa = new PaService($this->get('em'), $this->get('logger'));

        try {
            $ret = $pa->addFromCasrecRows($data);
            return $ret;
        } catch (\Exception $e) {
            $added = ['users' => [], 'clients' => [], 'reports' => []];
            return ['added'=>$added, 'errors' => [$e->getMessage(), 'warnings'=>[]]];
        }
    }
}
