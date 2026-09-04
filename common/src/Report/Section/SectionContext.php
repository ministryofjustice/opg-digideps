<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

/**
 * Context in which a section may be used (displayed, referenced etc.)
 */
enum SectionContext
{
    // admin checklist
    case ADMIN_MANAGE_CHECKLIST;

    // render of the whole report
    case REPORT;
}
