<?php

declare(strict_types=1);

namespace App\v2\Registration\SelfRegistration\Factory;

use App\Entity\CourtOrder;
use App\Service\DateTimeProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CourtOrderFactory
{
    public function __construct(private ValidatorInterface $validator, private DateTimeProvider $dateProvider)
    {
    }
    public function createFromDto(CourtOrderDto $dto): CourtOrder
    {
        $entity = new CourtOrder($this->convertDtoToArray($dto));

        $this->throwExceptionOnInvalidEntity($entity);

        return $entity;
    }

    private function convertDtoToArray(CourtOrderDto $dto): array
    {
        return [
            'CourtOrderUid' => $dto->getCaseNumber(),
            'Type' => $dto->getClientSurname(),
            'Active' => $dto->getDeputyUid(),
        ];
    }

    private function throwExceptionOnInvalidEntity(CourtOrder $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new PreRegistrationCreationException(str_replace('Object(App\Entity\PreRegistration).', '', (string) $errors));
        }
    }
}
