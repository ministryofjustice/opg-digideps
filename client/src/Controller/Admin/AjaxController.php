<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\RestClient;
use Predis\ClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/ajax")
 *
 */
class AjaxController extends AbstractController
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        RestClient $restClient
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @Route("/casrec-delete-by-source/{source}", name="casrec_delete_by_source_ajax")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param $source
     * @return JsonResponse
     */
    public function deleteUsersBySourceAjaxAction($source)
    {
        try {
            $before = $this->restClient->get('casrec/count', 'array');
            $this->restClient->delete('casrec/delete-by-source/'.$source);
            $after = $this->restClient->get('casrec/count', 'array');

            return new JsonResponse(['before'=>$before, 'after'=>$after]);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/casrec-add", name="casrec_add_ajax")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param Request $request
     * @param ClientInterface $redisClient
     * @return JsonResponse
     */
    public function uploadUsersAjaxAction(Request $request, ClientInterface $redisClient)
    {
        $chunkId = 'chunk' . $request->get('chunk');

        try {
            $compressedData = $redisClient->get($chunkId);
            if ($compressedData) {
                $ret = $this->restClient->setTimeout(600)->post('v2/lay-deputyship/upload', $compressedData);
                $redisClient->del($chunkId); //cleanup for next execution
            } else {
                $ret['added'] = 0;
            }

            return new JsonResponse($ret);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage());
        }
    }
}
