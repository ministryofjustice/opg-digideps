<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CsvUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/codeputy/")
 */
class CoDeputyController extends RestController
{
    /**
     * @route("{count}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function countMld(Request $request)
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('count(u.id)')
            ->from('AppBundle\Entity\User', 'u')
            ->where('u.coDeputyClientConfirmed = ?1')
            ->setParameter(1, true);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @Route("add")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'email' => 'notEmpty',
        ]);

        $loggedInUser = $this->getUser();
        $newUser = new EntityDir\User();

        $newUser->setFirstname('');
        $newUser->setLastname('');
        $newUser->setEmail($data['email']);
        $newUser->recreateRegistrationToken();
        $newUser->setRoleName(EntityDir\User::ROLE_LAY_DEPUTY);

        $this->get('user_service')->addUser($loggedInUser, $newUser, $data);

        $this->setJmsSerialiserGroups(['user']);

        return $newUser;
    }

    /**
     * @Route("{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */

        if (!$user->isCoDeputy()
            || !$this->getUser()->isCoDeputy()
            || ($this->getUser()->getIdOfClientWithDetails() != $user->getIdOfClientWithDetails())) {
            throw $this->createAccessDeniedException("User not authorised to update other user's data");
        }

        $data = $this->deserializeBodyContent($request, ['email' => 'notEmpty']);
        if (!empty($data['email'])) {
            $originalUser = clone $user;
            $user->setEmail($data['email']);
            $userService = $this->get('user_service');
            $userService->editUser($originalUser, $user);
        }

        return [];
    }

    /**
     * Bulk upgrade of codeputy_client_confirmed flag
     * Max 10k otherwise failing (memory reach 128M).
     * Borrows heavily from CasRecController:addBulk
     *
     * @Route("{mldupgrade}")
     * @Method({"POST"})
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
                $deputyNumbers[] = EntityDir\User::padDeputyNumber($deputy['Deputy No']);
            }
        }

        $conn = $this->getEntityManager()->getConnection();
        $affected = 0;
        foreach (array_chunk($deputyNumbers, 500) as $chunk) {
            $sql = "UPDATE dd_user SET codeputy_client_confirmed = TRUE WHERE deputy_no IN ('" . implode("','", $chunk) . "')";
            $affected += $conn->exec($sql);
        }

        $this->get('logger')->info('Received ' . count($data) . ' records, of which ' . $affected . ' were updated');
        return ['requested_mld_upgrades' => count($deputyNumbers), 'updated' => $affected, 'errors' => $retErrors];
    }
}
