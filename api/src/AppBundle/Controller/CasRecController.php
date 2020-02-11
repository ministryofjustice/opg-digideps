<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Repository\CasRecRepository;
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
     * @Route("/delete-by-source/{source}", methods={"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param CasRecRepository $casRecRepository
     * @param $source
     * @return array|JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteBySource(CasRecRepository $casRecRepository, $source)
    {
        if (!in_array($source, CasRec::validSources())) {
            throw new \InvalidArgumentException(sprintf('Invalid source: %s', $source));
        }

        $result = $casRecRepository->deleteAllBySource($source);

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
