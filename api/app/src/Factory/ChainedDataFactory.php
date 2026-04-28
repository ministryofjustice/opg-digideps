<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

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
     * @return DataFactoryResult Aggregated results from all data factories; the messages and error messages are
     * keyed by the name of the data factory that produced them.
     */
    public function run(): DataFactoryResult
    {
        $result = new DataFactoryResult();

        foreach ($this->dataFactories as $dataFactory) {
            $factoryResult = $dataFactory->run();

            $factoryName = $dataFactory->getName();

            foreach ($factoryResult->getMessages() as $key => $messages) {
                $result->addMessages(source: $factoryName . ':' . $key, messages: array_values($messages));
            }

            foreach ($factoryResult->getErrorMessages() as $key => $errorMessages) {
                $result->addErrorMessages(source: $factoryName . ':' . $key, errorMessages: array_values($errorMessages));
            }
        }

        return $result;
    }
}
