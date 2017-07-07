<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/ajax")
 */
class AdminAjaxController extends AbstractController
{
    /**
     * @Route("/casrec-truncate", name="casrec_truncate_ajax")
     * @Template
     */
    public function truncateUsersAjaxAction(Request $request)
    {
        try {
            $before = $this->getRestClient()->get('casrec/count', 'array');
            $this->getRestClient()->delete('casrec/truncate');
            $after = $this->getRestClient()->get('casrec/count', 'array');

            return new JsonResponse(['before'=>$before, 'after'=>$after]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/casrec-add", name="casrec_add_ajax")
     * @Template
     */
    public function uploadUsersAjaxAction(Request $request)
    {
        $chunkId = 'chunk' . $request->get('chunk');
        $redis = $this->get('snc_redis.default');

        try {
            $compressedData = $redis->get($chunkId);
            if ($compressedData) {
                $ret = $this->getRestClient()->setTimeout(600)->post('casrec/bulk-add', $compressedData);
            } else {
                $ret['added'] = 0;
            }

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/pa-add", name="pa_add_ajax")
     * @Method({"POST"})
     * @Template
     */
    public function uploadPaAjaxAction(Request $request)
    {
        $chunkId = 'pa_chunk' . $request->get('chunk');
        $redis = $this->get('snc_redis.default');

        try {
            $compressedData = $redis->get($chunkId);
            if (!$compressedData) {
                new JsonResponse('Chunk not found', 500);
            }

            $ret = $this->get('pa_service')->uploadAndSetFlashMessages($compressedData, $request->getSession()->getFlashBag());

            $redis->del($chunkId);

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }
}
