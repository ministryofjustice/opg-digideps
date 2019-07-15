<?php

namespace AppBundle\v2\Registration\SelfRegistration\Factory;

use AppBundle\Entity\CasRec;
use AppBundle\Service\DateTimeProvider;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasRecFactory
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var DateTimeProvider */
    private $dateProvider;

    /**
     * @param ValidatorInterface $validator
     * @param DateTimeProvider $dateProvider
     */
    public function __construct(ValidatorInterface $validator, DateTimeProvider $dateProvider)
    {
        $this->validator = $validator;
        $this->dateProvider = $dateProvider;
    }

    /**
     * @param LayDeputyshipDto $dto
     * @return CasRec
     */
    public function createFromDto(LayDeputyshipDto $dto)
    {
        $entity = new CasRec($this->convertDtoToArray($dto));
        $entity->setUpdatedAt($this->dateProvider->getDateTime());

        $this->throwExceptionOnInvalidEntity($entity);

        return $entity;
    }

    /**
     * @param LayDeputyshipDto $dto
     * @return array
     */
    private function convertDtoToArray(LayDeputyshipDto $dto): array
    {
        return [
            'Case' => $dto->getCaseNumber(),
            'Surname' => $dto->getClientSurname(),
            'Deputy No' => $dto->getDeputyNumber(),
            'Dep Surname' => $dto->getDeputySurname(),
            'Dep Postcode' => $dto->getDeputyPostcode(),
            'Typeofrep' => $dto->getTypeOfReport(),
            'Corref' => $dto->getCorref(),
            'NDR' => $dto->isNdrEnabled()
        ];
    }

    /** @param CasRec $entity */
    private function throwExceptionOnInvalidEntity(CasRec $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new CasRecCreationException(
                str_replace('Object(AppBundle\Entity\CasRec).', '', (string) $errors)
            );
        }
    }
}
