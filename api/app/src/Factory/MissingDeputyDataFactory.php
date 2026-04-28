<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Service\UserDeputyService;

/**
 * Adds deputy records to users where they don't have one.
 * This requires the dd_user record to have a deputy UID for matching purposes.
 */
class MissingDeputyDataFactory implements DataFactoryInterface
{
    public function __construct(
        private readonly UserDeputyService $userDeputyService
    ) {
    }

    public function getName(): string
    {
        return 'MissingDeputy';
    }

    public function run(): DataFactoryResult
    {
        // create deputies where missing, and associate users with deputies where they don't have one
        try {
            $numUserDeputyAssociations = $this->userDeputyService->addMissingUserDeputies();
            $messages = ['Success' => ["Added $numUserDeputyAssociations user <-> deputy associations"]];
        } catch (\Exception $e) {
            $errors = ['Error' => ['Error encountered while adding user <-> deputy associations: ' . $e->getMessage()]];
            return new DataFactoryResult(errorMessages: $errors);
        }

        return new DataFactoryResult(messages: $messages);
    }
}
