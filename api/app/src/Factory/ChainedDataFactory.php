<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

/**
 * Run multiple data factories in sequence and aggregate their outputs.
 */
final readonly class ChainedDataFactory implements DataFactoryInterface
{
    public function __construct(
        private string $name = 'Chained',
        /** @var array<array{DataFactoryInterface, string}> $dataFactories */
        private array $dataFactories = [],
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
    public function run(bool $dryRun): DataFactoryResult
    {
        $result = new DataFactoryResult();

        foreach ($this->dataFactories as [$dataFactory, $flag]) {
            $dry = $dryRun;
            $flag = FactoryExecutionFlag::tryFrom($flag) ?? FactoryExecutionFlag::Inactive;
            if ($flag === FactoryExecutionFlag::Inactive) {
                continue;
            } elseif ($flag === FactoryExecutionFlag::DryRunOnly) {
                $dry = true;
            }

            $factoryResult = $dataFactory->run($dry);
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
