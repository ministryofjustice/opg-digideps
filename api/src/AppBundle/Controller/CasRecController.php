<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\CasRec;
use AppBundle\Service\CsvUploader;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * @Route("/truncate", methods={"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function truncateTable(Request $request)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->query('TRUNCATE TABLE casrec');

        return ['truncated'=>true];
    }

    /**
     * @Route("/delete-by-source/{source}", methods={"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param $source
     * @return array|JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteBySource($source)
    {
        if (!in_array($source, CasRec::validSources())) {
            return new JsonResponse(['Invalid source'], 400);
        }

        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->delete('AppBundle\Entity\CasRec', 'cr')
            ->where('cr.source = :source')
            ->setParameter('source', $source);

        $result = $qb->getQuery()->getOneOrNullResult();

        return ['deletion-count' => $result === null ? 0 : $result];
    }

    /**
     * Verify Deputy & Client last names, Postcode, and Case Number
     *
     * @Route("/verify", methods={"POST"})
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
     * @Route("/count", methods={"GET"})
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
