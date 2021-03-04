<?php

namespace App\Controller;

use App\Entity\CasRec;
use App\Repository\CasRecRepository;
use App\Service\CasrecVerificationService;
use App\Service\Formatter\RestFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    private CasrecVerificationService $casrecVerification;
    private RestFormatter $formatter;

    public function __construct(CasrecVerificationService $casrecVerification, RestFormatter $formatter)
    {
        $this->casrecVerification = $casrecVerification;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/delete-by-source/{source}", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
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
    public function verify(Request $request, CasrecVerificationService $verificationService)
    {
        $clientData = $this->formatter->deserializeBodyContent($request);
        $user = $this->getUser();

        $casrecVerified = $verificationService->validate(
            $clientData['case_number'],
            $clientData['lastname'],
            $user->getLastname(),
            $user->getAddressPostcode()
        );

        return ['verified' => $casrecVerified];
    }

    /**
     * @Route("/count", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userCount()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('App\Entity\CasRec', 'c');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @Route("/clientHasCoDeputies/{caseNumber}", methods={"GET"})
     * @param string $caseNumber
     * @return array|JsonResponse
     */
    public function clientHasCoDeputies(string $caseNumber)
    {
        return $this->casrecVerification->isMultiDeputyCase($caseNumber);
    }
}
