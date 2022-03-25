<?php

namespace App\v2\Registration\SelfRegistration\Factory;

use App\Entity\CasRec;
use App\Service\DateTimeProvider;
use App\v2\Registration\DTO\LayDeputyshipDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasRecFactory
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var DateTimeProvider */
    private $dateProvider;

    public function __construct(ValidatorInterface $validator, DateTimeProvider $dateProvider)
    {
        $this->validator = $validator;
        $this->dateProvider = $dateProvider;
    }

    /**
     * @return CasRec
     */
    public function createFromDto(LayDeputyshipDto $dto)
    {
        $entity = new CasRec($this->convertDtoToArray($dto));
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

    /** @param CasRec $entity */
    private function throwExceptionOnInvalidEntity(CasRec $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new CasRecCreationException(str_replace('Object(App\Entity\CasRec).', '', (string) $errors));
        }
    }
}
