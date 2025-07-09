<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\Internal\LayDeputyshipApi;
use App\Service\Client\Internal\PreRegistrationApi;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/ajax")
 */
class AjaxController extends AbstractController
{
    public function __construct(
        private readonly PreRegistrationApi $preRegistrationApi,
        private readonly LayDeputyshipApi $layDeputyshipApi,
        private readonly LoggerInterface $verboseLogger,
    ) {
    }

    /**
     * @Route("/pre-registration-delete", name="pre_registration_delete_ajax")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function deletePreRegistrationAjaxAction(): JsonResponse
    {
        try {
            $before = $this->preRegistrationApi->count();
            $this->preRegistrationApi->deleteAll();
            $after = $this->preRegistrationApi->count();

            return new JsonResponse(['before' => $before, 'after' => $after]);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage());
        }
    }

    /**
     * @Route("/pre-registration-add", name="pre_registration_add_ajax")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function uploadUsersAjaxAction(Request $request, ClientInterface $redisClient): JsonResponse
    {
        $chunkId = 'chunk'.$request->get('chunk');
        $this->verboseLogger->notice(sprintf('AJAX: Processing chunk with chunkId: %s', $chunkId));

        try {
            $compressedData = $redisClient->get($chunkId);
            if ($compressedData) {
                $ret = $this->layDeputyshipApi->uploadLayDeputyShip($compressedData, $chunkId);
                $this->verboseLogger->notice(sprintf('AJAX: Successfully processed chunkId: %s', $chunkId));
                $redisClient->del($chunkId); // cleanup for next execution
            } else {
                $ret['added'] = 0;
                $this->verboseLogger->error(sprintf('AJAX: Unable to process chunkId: %s', $chunkId));
            }

            return new JsonResponse($ret);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage());
        }
    }
}
