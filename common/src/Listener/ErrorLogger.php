<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Listener;

use OPG\Digideps\Common\Validating\ValidatingArray;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final readonly class ErrorLogger
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function onKernelException(ExceptionEvent $event, bool $alwaysLog = false): void
    {
        $throwable = $event->getThrowable();
        $class = $throwable::class;
        if ($alwaysLog || $this->doLog($throwable)) {
            $log = $this->extractLog($throwable);
            $this->logger->error(json_encode($log) ?: "Error during json_encode. {$class} message was: {$throwable->getMessage()}");
        }
    }

    private function extractLog(?\Throwable $throwable): array
    {
        if ($throwable === null) {
            return [];
        }
        return [
            'class' => $throwable::class,
            'file' => basename($throwable->getFile()),
            'line' => $throwable->getLine(),
            'message' => $throwable->getMessage(),
            'previous' => $this->extractLog($throwable->getPrevious()),
            'trace' => $this->extractTrace($throwable),
        ];
    }

    private function extractTrace(\Throwable $throwable): array
    {
        $trace = [];
        foreach ($throwable->getTrace() as $i => $entry) {
            if (is_array($entry)) {
                $array = new ValidatingArray($entry);
                $trace[] = [
                    'file' => basename($array->getStringOrDefault('file', '/missing')),
                    'class' => $array->getStringOrNull('class'),
                    'function' => $array->getStringOrNull('function'),
                    'line' => $array->getIntegerOrNull('line'),
                    'types' => array_map(
                        fn (mixed $value): string => \get_debug_type($value),
                        $array->getArrayOrDefault('args', [])
                    ),
                ];
            }
            if ($i >= 5) {
                break;
            }
        }
        return $trace;
    }

    private function doLog(\Throwable $throwable): bool
    {
        $code = (int)$throwable->getCode();
        return match (true) {
            substr_count($throwable::class, '\\') === 0, $code < 400, $code > 599, $code === 500 => true,
            default => false
        };
    }
}
