<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\SerializerInterface;

class JWTController extends SymfonyAbstractController
{
    private JWTEncoderInterface $JWTEncoder;
    private UserRepository $userRepository;
    private EncoderFactoryInterface $encoderFactory;
    private SerializerInterface $serializer;

    public function __construct(
        JWTEncoderInterface $JWTEncoder,
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        SerializerInterface $serializer,
    ) {
        $this->JWTEncoder = $JWTEncoder;
        $this->userRepository = $userRepository;
        $this->encoderFactory = $encoderFactory;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/v3/jwt", methods={"POST"})
     */
    public function newTokenAction(Request $request): JsonResponse
    {
        $formData = json_decode($request->getContent(), true)['form-data'];
        $email = $formData['email'];
        $password = $formData['password'];

        /** @var User $user */
        $user = $this->userRepository->findUserByEmail($email);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $passwordEncoder = $this->encoderFactory->getEncoder($user);

        $encodedPass = $passwordEncoder->encodePassword($password, $user->getSalt());
        $isValid = $passwordEncoder->isPasswordValid(
            $encodedPass,
            $password,
            $user->getSalt()
        );

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->JWTEncoder->encode(['username' => $email]);

        return new JsonResponse(
            [
                'token' => $token,
                'user' => $this->serializer->serialize($user, 'json'),
            ],
            Response::HTTP_CREATED
        );
    }
}
