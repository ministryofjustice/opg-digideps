<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\List;

enum ListTag: string
{
    case Ordered = 'ol';
    case Unordered = 'ul';
}
