<?php

namespace App\v2\Registration\SelfRegistration\Factory;

use App\Entity\PreRegistration;
use App\Service\DateTimeProvider;
use App\v2\Registration\DTO\LayDeputyshipDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PreRegistrationFactory
{
    public function __construct(private ValidatorInterface $validator, private DateTimeProvider $dateProvider)
    {
    }

    /**
     * @return PreRegistration
     */
    public function createFromDto(LayDeputyshipDto $dto)
    {
        $entity = new PreRegistration($this->convertDtoToArray($dto));
        $entity->setUpdatedAt($this->dateProvider->getDateTime());

        $this->throwExceptionOnInvalidEntity($entity);

        return $entity;
    }

    private function convertDtoToArray(LayDeputyshipDto $dto): array
    {
        return [
            'Case' => $dto->getCaseNumber(),
            'ClientSurname' => $dto->getClientSurname(),
            'DeputyUid' => $dto->getDeputyUid(),
            'DeputySurname' => $dto->getDeputySurname(),
            'DeputyAddress1' => $dto->getDeputyAddress1(),
            'DeputyAddress2' => $dto->getDeputyAddress2(),
            'DeputyAddress3' => $dto->getDeputyAddress3(),
            'DeputyAddress4' => $dto->getDeputyAddress4(),
            'DeputyAddress5' => $dto->getDeputyAddress5(),
            'DeputyPostcode' => $dto->getDeputyPostcode(),
            'ReportType' => $dto->getTypeOfReport(),
            'NDR' => $dto->isNdrEnabled() ? 'yes' : 'no',
            'MadeDate' => $dto->getOrderDate()->format('Y-m-d'),
            'OrderType' => $dto->getOrderType(),
            'CoDeputy' => $dto->getIsCoDeputy() ? 'yes' : 'no',
        ];
    }

    /** @param PreRegistration $entity */
    private function throwExceptionOnInvalidEntity(PreRegistration $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new PreRegistrationCreationException(str_replace('Object(App\Entity\PreRegistration).', '', (string) $errors));
        }
    }
}
