<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Account;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\EmailInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetRequestType extends AbstractFormType
{
    protected string $translationDomain {get => 'twig-components';}
    protected string $translationPrefix {get => 'opg.account.reset.email';}

    /**
     * @inheritDoc
     */
    protected function defineInputs(callable $t): array
    {
        return [
            new EmailInput(
                'email',
                $t('label'),
                constraints: new Constraints(
                    new NotBlank(message: 'notBlank'),
                    new Length(max: 60, maxMessage: 'tooLong'),
                    new Email(message: 'invalidFormat')
                )
            ),
            new SubmitButton('submit', $t('submit'))
        ];
    }
}
