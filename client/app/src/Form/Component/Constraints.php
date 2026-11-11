<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use Symfony\Component\Validator\Constraint;

final readonly class Constraints
{
    /**
     * @var array<Constraint> $constraints
     */
    public array $constraints;

    public function __construct(Constraint ...$constraints)
    {
        $this->constraints = $constraints;
    }

    public function notEmpty(): bool
    {
        return !empty($this->constraints);
    }
}
