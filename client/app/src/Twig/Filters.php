<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Twig;

class Filters
{
    public static function statusToTagCss(string $status): string
    {
        return match ($status) {
            'notStarted', 'not-started' => 'govuk-tag--grey',
            'notFinished', 'active', 'incomplete' => 'govuk-tag--yellow',
            'needs-attention', 'unsubmitted', 'closed' => 'govuk-tag--red',
            'not-matching', 'explained' => 'govuk-tag--blue',
            'done', 'low-assets-done', 'submitted', 'readyToSubmit' => 'govuk-tag--green',
            default => '',
        };
    }
}
