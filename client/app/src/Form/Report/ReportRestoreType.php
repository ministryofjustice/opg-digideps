<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Report;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\HiddenInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportRestoreType extends AbstractFormType
{
    public const string REPLACEABLE_REPORT_ID = 'replaceableReportId';
    public const string RESTORE_FROM_REPORT_ID = 'restoreFromReportId';

    protected function defineInputs(callable $t): array
    {
        return [
            new HiddenInput(self::REPLACEABLE_REPORT_ID, new Constraints(new NotBlank())),
            new HiddenInput(self::RESTORE_FROM_REPORT_ID, new Constraints(new NotBlank())),
            new SubmitButton('submit', $t('confirm.label'))
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.report.restore'; }
}
