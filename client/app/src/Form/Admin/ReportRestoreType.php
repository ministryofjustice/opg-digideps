<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Admin;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\HiddenInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;

class ReportRestoreType extends AbstractFormType
{
    public const string RESTORE_FROM_REPORT_ID = 'restoreFromReportId';
    public const string CLIENT_ID = 'clientId';

    protected function defineInputs(callable $t): array
    {
        return [
            new HiddenInput(self::RESTORE_FROM_REPORT_ID),
            new HiddenInput(self::CLIENT_ID),
            new SubmitButton('submit', $t('confirm.label'))
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.admin.clientDetails.restoreConfirmation'; }
}
