<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GOV\List;

enum ListTag: string
{
    case Ordered = 'ol';
    case Unordered = 'ul';
}
