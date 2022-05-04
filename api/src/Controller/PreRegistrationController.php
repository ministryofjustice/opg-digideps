<?php

namespace App\Controller;

use App\Repository\PreRegistrationRepository;
use App\Service\Formatter\RestFormatter;
use App\Service\PreRegistrationVerificationService;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pre-registration")
 */
class PreRegistrationController extends RestController
{
    public function __construct(
        private PreRegistrationVerificationService $preRegistrationVerificationService,
        private RestFormatter $formatter,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @Route("/delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param $source
     *
     * @return array|JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function delete(PreRegistrationRepository $preRegistrationRepository)
    {
        $this->logger->warning('Deleteing all pre-registration entities');

        $result = $preRegistrationRepository->deleteAll();

        return ['deletion-count' => null === $result ? 0 : $result];
    }

    /**
     * Verify Deputy & Client last names, Postcode, and Case Number.
     *
     * @Route("/verify", methods={"POST"})
     */
    public function verify(Request $request, PreRegistrationVerificationService $verificationService)
    {
        $clientData = $this->formatter->deserializeBodyContent($request);
        $user = $this->getUser();

        $verified = $verificationService->validate(
            $clientData['case_number'],
            $clientData['lastname'],
            $user->getLastname(),
            $user->getAddressPostcode()
        );

        return ['verified' => $verified];
    }

    /**
     * @Route("/count", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userCount()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(p.id)');
        $qb->from('App\Entity\PreRegistration', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @Route("/clientHasCoDeputies/{caseNumber}", methods={"GET"})
     *
     * @return array|JsonResponse
     */
    public function clientHasCoDeputies(string $caseNumber)
    {
        return $this->preRegistrationVerificationService->isMultiDeputyCase($caseNumber);
    }
}
