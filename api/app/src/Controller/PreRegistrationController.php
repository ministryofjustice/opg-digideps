<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use App\Service\Formatter\RestFormatter;
use App\Service\PreRegistrationVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/pre-registration')]
class PreRegistrationController extends RestController
{
    public function __construct(
        private readonly PreRegistrationVerificationService $preRegistrationVerificationService,
        private readonly RestFormatter $formatter,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/delete', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function delete(PreRegistrationRepository $preRegistrationRepository): array
    {
        $result = $preRegistrationRepository->deleteAll();

        return ['deletion-count' => null === $result ? 0 : $result];
    }

    /**
     * Verify Deputy first and last names, Client last name, Postcode, and Case Number.
     */
    #[Route(path: '/verify', methods: ['POST'])]
    public function verify(Request $request, PreRegistrationVerificationService $verificationService): array
    {
        $clientData = $this->formatter->deserializeBodyContent($request);
        /** @var User $user */
        $user = $this->getUser();

        // truncate case number if length is 10 digits long
        $clientData['case_number'] = 10 == strlen($clientData['case_number']) ? substr($clientData['case_number'], 0, -2) : $clientData['case_number'];

        $isMultiDeputyCase = $verificationService->isMultiDeputyCase($clientData['case_number']);
        $existingClient = $this->em->getRepository('App\Entity\Client')->findByCaseNumber($clientData['case_number']);

        // ward off non-fee-paying codeps trying to self-register
        if ($isMultiDeputyCase && ($existingClient instanceof Client) && $existingClient->hasDeputies()) {
            // if client exists with case number, the first codep already registered.
            throw new \RuntimeException(json_encode('Co-deputy cannot self register.'), 403);
        }

        // Check the client is unique and has no deputies attached
        if ($existingClient instanceof Client) {
            if ($existingClient->hasDeputies() || $existingClient->getOrganisation() instanceof Organisation) {
                throw new \RuntimeException(json_encode(sprintf('User registration: Case number %s already used', $existingClient->getCaseNumber())), 425);
            } else {
                // soft delete client
                $this->em->remove($existingClient);
                $this->em->flush();
            }
        }

        // this will throw a runtime exception if validation failed, which is how we control the response from
        // this controller
        $preregMatches = $verificationService->validate(
            $clientData['case_number'],
            $clientData['lastname'],
            $user->getFirstname(),
            $user->getLastname(),
            $user->getAddressPostcode()
        );

        if (1 !== count($preregMatches)) {
            // a deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            throw new \RuntimeException(json_encode(sprintf('A unique deputy record for case number %s could not be identified', $clientData['case_number'])), 462);
        }

        $user->setDeputyNo($preregMatches[0]->getDeputyUid());
        $user->setDeputyUid(intval($preregMatches[0]->getDeputyUid()));
        $user->setPreRegisterValidatedDate(new \DateTime());
        $user->setIsPrimary(true);
        $this->em->persist($user);
        $this->em->flush();

        return ['verified' => true];
    }

    #[Route(path: '/count', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function userCount(): int
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(p.id)');
        $qb->from('App\Entity\PreRegistration', 'p');

        /** @var int $result */
        $result = $qb->getQuery()->getSingleScalarResult();

        return $result;
    }

    #[Route(path: '/clientHasCoDeputies/{caseNumber}', methods: ['GET'])]
    public function clientHasCoDeputies(string $caseNumber): bool
    {
        return $this->preRegistrationVerificationService->isMultiDeputyCase($caseNumber);
    }
}
