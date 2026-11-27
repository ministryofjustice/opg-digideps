<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Admin;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\HiddenInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButtonWithCancel;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportRestoreType extends AbstractFormType
{
    public const string RESTORE_FROM_REPORT_ID = 'restoreFromReportId';
    public const string CLIENT_ID = 'clientId';
    public const string CANCEL_URL = 'cancelUrl';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(self::CANCEL_URL)
            ->setAllowedTypes(self::CANCEL_URL, 'string')
            ->setInfo(self::CANCEL_URL, 'URL the cancel link alongside the submit button points to');
    }

    protected function defineInputs(callable $t, ValidatingArray $options): array
    {
        return [
            new HiddenInput(self::RESTORE_FROM_REPORT_ID),
            new HiddenInput(self::CLIENT_ID),
            new SubmitButtonWithCancel(
                'submit',
                $t('confirm.label'),
                $t('cancel.label'),
                $options->getStringOrThrow(self::CANCEL_URL)
            ),
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.admin.clientDetails.restoreConfirmation'; }
}
