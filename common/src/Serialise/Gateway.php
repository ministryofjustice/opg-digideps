<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Serialise;

final readonly class Gateway
{
    private const string OPG_DIGIDEPS_COMMON = "OPG\\Digideps\\Common\\";

    public function serialise(object $object): string
    {
        return \serialize($object);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    public function deserialise(string $class, string $serialised): ?object
    {
        if ($this->register()) {
            $object = \unserialize($serialised);
            if (!$this->unregister()) {
                throw new \LogicException("Failed to unregister intercept inside of the autoloader");
            }
            if (is_object($object) && $object::class === $class) {
                /**
                 * @var T $object
                 */
                return $object;
            }
        }
        return null;
    }

    private function callback(string $class): void
    {
        if (\str_starts_with($class, self::OPG_DIGIDEPS_COMMON)) {
            return;
        }
        throw new \LogicException("Attempting to unserialise untrusted class {$class}");
    }

    private function register(): bool
    {
        return \spl_autoload_register([$this, 'callback'], prepend: true);
    }

    private function unregister(): bool
    {
        return \spl_autoload_unregister([$this, 'callback']);
    }
}
