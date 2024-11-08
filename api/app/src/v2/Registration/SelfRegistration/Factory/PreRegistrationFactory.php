<?php

namespace App\v2\Registration\SelfRegistration\Factory;

use App\Entity\PreRegistration;
use App\Service\DateTimeProvider;
use App\v2\Registration\DTO\LayPreRegistrationDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PreRegistrationFactory
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /**
     * @return PreRegistration
     */
    public function createFromDto(LayPreRegistrationDto $dto): PreRegistration
    {
        $entity = new PreRegistration($this->convertDtoToArray($dto));

        $this->throwExceptionOnInvalidEntity($entity);

        return $entity;
    }

    private function convertDtoToArray(LayPreRegistrationDto $dto): array
    {
        return [
            'Case' => $dto->getCaseNumber(),
            'ClientSurname' => $dto->getClientSurname(),
            'DeputyUid' => $dto->getDeputyUid(),
            'DeputyFirstname' => $dto->getDeputyFirstname(),
            'DeputySurname' => $dto->getDeputySurname(),
            'DeputyAddress1' => $dto->getDeputyAddress1(),
            'DeputyAddress2' => $dto->getDeputyAddress2(),
            'DeputyAddress3' => $dto->getDeputyAddress3(),
            'DeputyAddress4' => $dto->getDeputyAddress4(),
            'DeputyAddress5' => $dto->getDeputyAddress5(),
            'DeputyPostcode' => $dto->getDeputyPostcode(),
            'ReportType' => $dto->getTypeOfReport(),
            'NDR' => $dto->isNdrEnabled() ? 'yes' : 'no',
            'CourtMadeDate' => $dto->getCourtOrderDate()->format('Y-m-d'),
            'CourtOrderType' => $dto->getCourtOrderType(),
            'CoDeputy' => $dto->getIsCoDeputy() ? 'yes' : 'no',
            'Hybrid' => $dto->getHybrid(),
        ];
    }

    private function throwExceptionOnInvalidEntity(PreRegistration $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new PreRegistrationCreationException(str_replace('Object(App\Entity\PreRegistration).', '', (string) $errors));
        }
    }
}
