<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T of object
 * @extends AbstractFormType<T>
 */
abstract class AbstractParametrisedFormType extends AbstractFormType
{
    /**
     * @var ?T $parameterSource
     */
    private static ?object $parameterSource = null;
    /**
     * @var T $parameter
     */
    private readonly object $parameter;

    /**
     * @param T $parameter
     */
    final public static function setParameter(object $parameter): void
    {
        self::$parameterSource ??= $parameter;
    }

    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
        if (self::$parameterSource !== null) {
            $this->parameter = self::$parameterSource;
            self::$parameterSource = null;
        } else {
            throw new \LogicException('You must call setParameters before construction.');
        }
    }

    final protected function defineInputs(callable $t): array
    {
        return $this->defineParametrisedInputs($t, $this->parameter);
    }

    /**
     * @param callable(?string, array<string, string>=): string $t
     * @param T $parameter
     * @return array<AbstractInput>
     */
    abstract protected function defineParametrisedInputs(callable $t, object $parameter): array;
}
