<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\SelfRegistration\Factory;

use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PreRegistrationFactory
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function createFromDto(LayDeputyshipDto $dto): PreRegistration
    {
        $entity = new PreRegistration($this->convertDtoToArray($dto));

        $this->throwExceptionOnInvalidEntity($entity);

        return $entity;
    }

    private function convertDtoToArray(LayDeputyshipDto $dto): array
    {
        return [
            'Case' => $dto->getCaseNumber(),
            'ClientFirstname' => $dto->getClientFirstname(),
            'ClientSurname' => $dto->getClientSurname(),
            'ClientAddress1' => $dto->getClientAddress1(),
            'ClientAddress2' => $dto->getClientAddress2(),
            'ClientAddress3' => $dto->getClientAddress3(),
            'ClientAddress4' => $dto->getClientAddress4(),
            'ClientAddress5' => $dto->getClientAddress5(),
            'ClientPostcode' => trim($dto->getClientPostcode() ?? ''),
            'DeputyUid' => $dto->getDeputyUid(),
            'DeputyFirstname' => $dto->getDeputyFirstname(),
            'DeputySurname' => $dto->getDeputySurname(),
            'DeputyAddress1' => $dto->getDeputyAddress1(),
            'DeputyAddress2' => $dto->getDeputyAddress2(),
            'DeputyAddress3' => $dto->getDeputyAddress3(),
            'DeputyAddress4' => $dto->getDeputyAddress4(),
            'DeputyAddress5' => $dto->getDeputyAddress5(),
            'DeputyPostcode' => trim($dto->getDeputyPostcode()),
            'ReportType' => $dto->getTypeOfReport(),
            'MadeDate' => $dto->getOrderDate()->format('Y-m-d'),
            'OrderType' => $dto->getOrderType(),
            'CoDeputy' => $dto->getIsCoDeputy() ? 'yes' : 'no',
            'Hybrid' => $dto->getHybrid(),
        ];
    }

    private function throwExceptionOnInvalidEntity(PreRegistration $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new PreRegistrationCreationException(str_replace('Object(OPG\Digideps\Backend\Entity\PreRegistration).', '', (string) $errors));
        }
    }
}
