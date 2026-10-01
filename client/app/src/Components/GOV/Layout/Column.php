<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Column
{
    public int $n = 1;
    public int $d = 1;

    public string $width {
        get {
            if ($this->n < 1 || $this->d <= $this->n || $this->n > 3 || $this->d > 4) {
                return 'full';
            }
            $numerator = match ($this->n) {
                1 => 'one',
                2 => 'two',
                3 => 'three',
            };
            $plural = $this->n === 1 ? '' : 's';
            $denominator = match ($this->d) {
                2 => 'half',
                3 => "third{$plural}",
                4 => "quarter{$plural}",
            };
            return "{$numerator}-{$denominator}";
        }
    }
}
