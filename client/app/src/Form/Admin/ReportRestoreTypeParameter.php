<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Admin;

final readonly class ReportRestoreTypeParameter
{
    public function __construct(public string $cancelUrl)
    {
    }
}
