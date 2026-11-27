<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Account;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\CheckBoxInput;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use Symfony\Component\Validator\Constraints\NotBlank;

class AcceptRequestType extends AbstractFormType
{
    /**
     * @inheritDoc
     */
    protected function defineInputs(callable $t): array
    {
        return [
            new CheckBoxInput(
                'accept',
                $t('label'),
                constraints: new Constraints(new NotBlank(message: $t('required')))
            ),
            new SubmitButton(
                'submit',
                $t('submit'),
            )
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.account.accept.checkbox'; }
}
