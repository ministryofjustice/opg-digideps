<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CsvUploader;
use App\Service\Formatter\RestFormatter;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/codeputy/")
 */
class CoDeputyController extends RestController
{
    private UserService $userService;
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    public function __construct(UserService $userService, EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->userService = $userService;
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("{count}", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function countMld(Request $request)
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('count(u.id)')
            ->from('App\Entity\User', 'u')
            ->where('u.coDeputyClientConfirmed = ?1')
            ->setParameter(1, true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @Route("add", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'email' => 'notEmpty',
        ]);

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $newUser = new User();

        $newUser->setFirstname('');
        $newUser->setLastname('');
        $newUser->setEmail($data['email']);
        $newUser->recreateRegistrationToken();
        $newUser->setRoleName(User::ROLE_LAY_DEPUTY);

        $this->userService->addUser($loggedInUser, $newUser, $data);

        $this->formatter->setJmsSerialiserGroups(['user']);

        return $newUser;
    }

    /**
     * @Route("{id}", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(User::class, $id, 'User not found'); /* @var $user User */

        if (!$user->isCoDeputy()
            || !$this->getUser()->isCoDeputy()
            || ($this->getUser()->getIdOfClientWithDetails() != $user->getIdOfClientWithDetails())) {
            throw $this->createAccessDeniedException("User not authorised to update other user's data");
        }

        $data = $this->formatter->deserializeBodyContent($request, ['email' => 'notEmpty']);
        if (!empty($data['email'])) {
            $originalUser = clone $user;
            $user->setEmail($data['email']);
            $this->userService->editUser($originalUser, $user);
        }

        return [];
    }

    /**
     * Bulk upgrade of codeputy_client_confirmed flag
     * Max 10k otherwise failing (memory reach 128M).
     * Borrows heavily from CasRecController:addBulk
     *
     * @Route("{mldupgrade}", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
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
            $sql = "UPDATE dd_user SET codeputy_client_confirmed = TRUE WHERE deputy_no IN ('" . implode("','", $chunk) . "')";
            $affected += $conn->exec($sql);
        }

        return ['requested_mld_upgrades' => count($deputyNumbers), 'updated' => $affected, 'errors' => $retErrors];
    }
}
