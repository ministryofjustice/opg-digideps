<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Twig;

use OPG\Digideps\Frontend\Components\GOV\Tag;

class Filters
{
    public static function statusToTagColour(string $status): string
    {
        return match ($status) {
            'notStarted', 'not-started' => Tag::GREY,
            'notFinished', 'active', 'incomplete' => Tag::YELLOW,
            'needs-attention', 'unsubmitted' => Tag::RED,
            'not-matching', 'explained' => Tag::BLUE,
            'done', 'low-assets-done', 'submitted', 'readyToSubmit' => Tag::GREEN,
            default => '',
        };
    }
}
