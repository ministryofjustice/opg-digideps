<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Account;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\RepeatedPasswordInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use OPG\Digideps\Frontend\Validator\Constraints\CommonPassword;
use OPG\Digideps\Frontend\Validator\Constraints\StrongPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordRequestType extends AbstractFormType
{
    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.account.password'; }

    protected function defineInputs(callable $t): array
    {
        return [
            new RepeatedPasswordInput(
                'password',
                $t('label.first'),
                $t('label.second'),
                $t('doesNotMatch'),
                constraints: new Constraints(
                    new NotBlank(message: $t('required')),
                    new CommonPassword(),
                    new StrongPassword()
                )
            ),
            new SubmitButton('submit', $t('submit')),
        ];
    }
}
