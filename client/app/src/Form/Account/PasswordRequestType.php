<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\RepeatedPasswordInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use OPG\Digideps\Frontend\Validator\Constraints\CommonPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PasswordRequestType extends AbstractFormType
{
    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.account.password.form'; }

    protected function defineInputs(callable $t, ValidatingArray $options): array
    {
        return [
            new RepeatedPasswordInput(
                'password',
                $t('label.first'),
                $t('label.second'),
                $t('doesNotMatch'),
                constraints: new Constraints(
                    new NotBlank(message: $t('notBlank')),
                    new Length(min: 14, max: 50, minMessage: $t('tooShort'), maxMessage: $t('tooLong')),
                    new Regex(pattern: '/[a-z]/', message: $t('noLowercase')),
                    new Regex(pattern: '/[A-Z]/', message: $t('noUppercase')),
                    new Regex(pattern: '/[0-9]/', message: $t('noNumber')),
                    new CommonPassword(message: $t('tooCommon')),
                )
            ),
            new SubmitButton('submit', $t('submit')),
        ];
    }
}
