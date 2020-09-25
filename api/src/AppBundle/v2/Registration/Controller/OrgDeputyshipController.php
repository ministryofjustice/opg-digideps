<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Controller;

use AppBundle\v2\Controller\ControllerTrait;
use AppBundle\v2\Registration\Uploader\OrgDeputyshipUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrgDeputyshipController extends AbstractController
{
    use ControllerTrait;

    /** @var OrgDeputyshipUploader */
    private $orgDeputyshipUploader;

    public function __construct(OrgDeputyshipUploader $orgDeputyshipUploader)
    {
        $this->orgDeputyshipUploader = $orgDeputyshipUploader;
    }

    /**
     * @Route("/org-deputyships", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function create(Request $request)
    {
        $deputyshipCount = ['added' => 0, 'errors' => 0];

        $orgDeputyshipData = json_decode($request->getContent(), true);

        foreach ($orgDeputyshipData as $deputyshipDatum) {
            if (empty($deputyshipDatum['Email'])) {
                $deputyshipCount['errors']++;
            } else {
                $deputyshipCount['added']++;
            }
        }

        return new JsonResponse($deputyshipCount, Response::HTTP_CREATED);
    }
}
