<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

            // MOVE TO SERVICE
            $ret = $this->getRestClient()->setTimeout(600)->post('pa/bulk-add', $compressedData);
            // MOVE TO SERVICE
            $request->getSession()->getFlashBag()->add(
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
                $request->getSession()->getFlashBag()->add(
                    'error',
                    implode('<br/>', $errors)
                );
            }

            if (!empty($warnings)) {
                $request->getSession()->getFlashBag()->add(
                    'warning',
                    implode('<br/>', $warnings)
                );
            }
            // END MOVE TO SERVICE

            $redis->del($chunkId);

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
        }
    }
}
