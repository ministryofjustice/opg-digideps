<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CsvUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * @Route("/truncate")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function truncateTable(Request $request)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->query('TRUNCATE TABLE casrec');

        return ['truncated'=>true];
    }

    /**
     * Verify Deputy & Client last names, Postcode, and Case Number
     *
     * @Route("/verify")
     * @Method({"POST"})
     */
    public function verify(Request $request)
    {
        $clientData = $this->deserializeBodyContent($request);
        $user = $this->getUser();

        $casrecVerified = $this->container->get('opg_digideps.casrec_verification_service')
            ->validate($clientData['case_number'], $clientData['lastname'], $user->getLastname(), $user->getAddressPostcode()
            );

        return ['verified' => $casrecVerified];
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function userCount()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('AppBundle\Entity\CasRec', 'c');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }
}
