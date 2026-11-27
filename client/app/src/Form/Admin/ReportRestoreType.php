<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Admin;

use OPG\Digideps\Frontend\Form\Component\AbstractParametrisedFormType;
use OPG\Digideps\Frontend\Form\Component\HiddenInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButtonWithCancel;

/**
 * @extends AbstractParametrisedFormType<ReportRestoreTypeParameter>
 */
class ReportRestoreType extends AbstractParametrisedFormType
{
    public const string RESTORE_FROM_REPORT_ID = 'restoreFromReportId';
    public const string CLIENT_ID = 'clientId';

    /**
     * @inheritDoc
     */
    protected function defineParametrisedInputs(callable $t, object $parameter): array
    {
        return [
            new HiddenInput(self::RESTORE_FROM_REPORT_ID),
            new HiddenInput(self::CLIENT_ID),
            new SubmitButtonWithCancel(
                'submit',
                $t('confirm.label'),
                $t('cancel.label'),
                $parameter->cancelUrl
            ),
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.admin.clientDetails.restoreConfirmation'; }
}
