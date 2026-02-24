<?php

declare(strict_types=1);

namespace App\Factory;

use App\Service\LayUserDeputyService;

class MissingLayDeputyDataFactory implements DataFactoryInterface
{
    public function __construct(
        private readonly LayUserDeputyService $userDeputyService
    ) {
    }

    public function getName(): string
    {
        return 'MissingDeputy';
    }

    public function run(): DataFactoryResult
    {
        $messages = ['Adding deputies to users where they are missing'];

        // create deputies where missing, and associate users with deputies where they don't have one
        try {
            $numUserDeputyAssociations = $this->userDeputyService->addMissingLayUserDeputies();
            $messages[] = "Added $numUserDeputyAssociations user <-> deputy associations";
        } catch (\Exception $e) {
            $errors = ['Error' => ['Error encountered while adding user <-> deputy associations: ' . $e->getMessage()]];
            return new DataFactoryResult(errorMessages: $errors);
        }

        return new DataFactoryResult(messages: ['Success' => $messages]);
    }
}
