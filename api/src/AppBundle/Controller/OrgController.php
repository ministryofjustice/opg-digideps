<?php

namespace AppBundle\Controller;

use AppBundle\Service\CsvUploader;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        /** @var array $data */
        $data = CsvUploader::decompressData($request->getContent());
        $count = count($data);

        set_time_limit(0);

        if (!$count) {
            throw new \RuntimeException('No records received from the API');
        }

        $response = new StreamedResponse();

        $response->setCallback(function() use ($data) {
            $chunks = array_chunk($data, 10);
            $chunkCount = count($chunks);
            foreach ($chunks as $i => $chunk) {
                $pa = $this->get('org_service');
                $pa->addFromCasrecRows($chunk);
                $progress = $i + 1;
                echo ($progress / $chunkCount);
                flush();
            }
        });

        $response->setStatusCode(200);
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');

        return $response;
    }
}
