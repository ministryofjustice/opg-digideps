<?php

declare(strict_types=1);

namespace App\Entity;

interface ClientBenefitsCheckInterface
{
    const WHEN_CHECKED_I_HAVE_CHECKED = 'haveChecked';
    const WHEN_CHECKED_IM_CURRENTLY_CHECKING = 'currentlyChecking';
    const WHEN_CHECKED_IVE_NEVER_CHECKED = 'neverChecked';

    const OTHER_INCOME_YES = 'yes';
    const OTHER_INCOME_NO = 'no';
    const OTHER_INCOME_DONT_KNOW = 'dontKnow';
}
