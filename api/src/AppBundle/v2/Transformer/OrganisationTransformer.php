<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class OrganisationTransformer
{
    /** @var DeputyTransformer */
    private $deputyTransformer;

    /**
     * @param DeputyTransformer $deputyTransformer
     */
    public function __construct(DeputyTransformer $deputyTransformer)
    {
        $this->deputyTransformer = $deputyTransformer;
    }

    /**
     * @param OrganisationDto $dto
     * @return array
     */
    public function transform(OrganisationDto $dto): array
    {
        return [
            'id' => $dto->getId(),
            'name' => $dto->getName(),
            'email_identifier' => $dto->getEmailIdentifier(),
            'is_activated' => $dto->isActivated(),
            'users' => $this->transformUsers($dto->getUsers())
        ];
    }

    /**
     * @param array $users
     * @return array
     */
    private function transformUsers(array $users): array
    {
        if (empty($users)) {
            return [];
        }

        $transformed = [];

        foreach ($users as $user) {
            if ($user instanceof DeputyDto) {
                $transformed[] = $this->deputyTransformer->transform($user, ['clients']);
            }
        }

        return $transformed;
    }
}
