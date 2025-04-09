<?php

namespace App\Controller;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\DeputyRepository;
use App\Service\Auth\AuthService;
use App\Service\DeputyService;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// TODO
// http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/deputy")
 */
class DeputyController extends RestController
{
    public function __construct(
        private readonly DeputyService $deputyService,
        private readonly RestFormatter $formatter,
        private readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    /**
     * @Route("/add", methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     */
    public function add(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request);
        $newDeputy = $this->populateDeputy(new Deputy(), $data);
        $currentUser = $this->getUser();
        $deputyId = $this->deputyService->addDeputy($newDeputy, $currentUser);

        return ['id' => $deputyId];
    }

    /**
     * call setters on User when $data contains values.
     * //TODO move to service.
     */
    private function populateDeputy(Deputy $deputy, array $data)
    {
        $this->hydrateEntityWithArrayData($deputy, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address4' => 'setAddress4',
            'address5' => 'setAddress5',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'phone_alternative' => 'setPhoneAlternative',
            'phone_main' => 'setPhoneMain',
        ]);

        if (array_key_exists('email', $data) && !empty($data['email'])) {
            $deputy->setEmail1($data['email']);
        }

        if (array_key_exists('deputy_uid', $data) && !empty($data['deputy_uid'])) {
            $deputy->setDeputyUid($data['deputy_uid']);
        }

        return $deputy;
    }

    /**
     * @Route("/{id}", name="deputy_find_by_id", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     *
     * @return object|null
     */
    public function findByIdAction(Request $request, int $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['deputy'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $deputy = $this->findEntityBy(Deputy::class, $id);

        return $deputy;
    }

    /**
     * @Route("/{uid}/reports", name="deputy_find_by_uid", requirements={"uid":"\d+"}, methods={"GET"})
     * 
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     *
     * @return array<string>
     */
    public function getAllDeputyReports(Request $request, int $uid): array
    {
        $inactive = $request->query->has('inactive') ? $request->query->get('inactive') : null;

        try {
            $this->formatter->setJmsSerialiserGroups(['deputy-court-order-basic']);
            $results = $this->em->getRepository(Deputy::class)->findReportsInfoByUid($uid, (bool) $inactive);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
        return $results;
    }
}
