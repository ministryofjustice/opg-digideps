<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CsvUploader;
use App\Service\Formatter\RestFormatter;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/codeputy/')]
class CoDeputyController extends RestController
{
    public function __construct(private readonly UserService $userService, private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '{count}', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function countMld(Request $request)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('count(u.id)')
            ->from('App\Entity\User', 'u')
            ->where('u.coDeputyClientConfirmed = ?1')
            ->setParameter(1, true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    #[Route(path: 'add/{clientId}', methods: ['POST'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function add(Request $request, int $clientId)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'email' => 'notEmpty',
            'firstname' => 'notEmpty',
            'lastname' => 'notEmpty',
        ]);

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $newUser = new User();

        $newUser->setFirstname($data['firstname']);
        $newUser->setLastname($data['lastname']);
        $newUser->setEmail($data['email']);
        $newUser->recreateRegistrationToken();
        $newUser->setRoleName(User::ROLE_LAY_DEPUTY);

        $this->userService->addUser($loggedInUser, $newUser, $clientId);

        $this->formatter->setJmsSerialiserGroups(['user']);

        return $newUser;
    }

    #[Route(path: '{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(User::class, $id, 'User not found'); /* @var $user User */

        if (
            !$user->isCoDeputy()
            || !$this->getUser()->isCoDeputy()
            || ($this->getUser()->getIdOfClientWithDetails() != $user->getIdOfClientWithDetails())
        ) {
            throw $this->createAccessDeniedException("User not authorised to update other user's data");
        }

        $data = $this->formatter->deserializeBodyContent($request, ['email' => 'notEmpty']);
        if (!empty($data['email'])) {
            $originalUser = clone $user;
            $user->setEmail($data['email']);
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $this->userService->editUser($originalUser, $user);
        }

        return [];
    }

    /**
     * Bulk upgrade of codeputy_client_confirmed flag
     * Max 10k otherwise failing (memory reach 128M).
     * Borrows heavily from CasRecController:addBulk.
     *
     *
     */
    #[Route(path: '{mldupgrade}', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function upgradeToMld(Request $request)
    {
        $maxRecords = 10000;

        ini_set('memory_limit', '1024M');

        $retErrors = [];
        $data = CsvUploader::decompressData($request->getContent());
        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No record received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $deputyNumbers = [];
        foreach ($data as $deputy) {
            if (array_key_exists('Deputy No', $deputy)) {
                $deputyNumbers[] = User::padDeputyNumber($deputy['Deputy No']);
            }
        }

        $conn = $this->em->getConnection();
        $affected = 0;
        foreach (array_chunk($deputyNumbers, 500) as $chunk) {
            $sql = "UPDATE dd_user SET codeputy_client_confirmed = TRUE WHERE deputy_no IN ('".implode("','", $chunk)."')";
            $affected += $conn->executeStatement($sql);
        }

        return ['requested_mld_upgrades' => count($deputyNumbers), 'updated' => $affected, 'errors' => $retErrors];
    }
}
