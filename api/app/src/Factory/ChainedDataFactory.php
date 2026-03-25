<?php

declare(strict_types=1);

namespace App\Factory;

use App\v2\Registration\DeputyshipProcessing\BuilderResult;

/**
 * Run multiple data factories in sequence and aggregate their outputs.
 */
class ChainedDataFactory implements DataFactoryInterface
{
    public function __construct(
        private readonly string $name = 'Chained',
        /** @var array<DataFactoryInterface> $dataFactories */
        private readonly array $dataFactories = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<DataFactoryResult, array<BuilderResult>> Aggregated results from all data factories; the messages and error messages are
     * keyed by the name of the data factory that produced them.
     */
    public function run(): array
    {
        $result = new DataFactoryResult();
        $builderResults = [];

        foreach ($this->dataFactories as $dataFactory) {
            [$factoryResult, $builderResult] = $dataFactory->run();

            if (!is_null($builderResult)) {
                $builderResults[] = $builderResult;
            }

            $factoryName = $dataFactory->getName();

            foreach ($factoryResult->getMessages() as $key => $messages) {
                $result->addMessages(source: $factoryName . ':' . $key, messages: array_values($messages));
            }

            foreach ($factoryResult->getErrorMessages() as $key => $errorMessages) {
                $result->addErrorMessages(source: $factoryName . ':' . $key, errorMessages: array_values($errorMessages));
            }
        }

        return [$result, $builderResults];
    }
}
