<?php

namespace OPG\Digideps\Frontend\Form\Account;

use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\Constraints;
use OPG\Digideps\Frontend\Form\Component\HintRecipe;
use OPG\Digideps\Frontend\Form\Component\RepeatedEmailInput;
use OPG\Digideps\Frontend\Form\Component\SubmitButton;
use OPG\Digideps\Frontend\Form\Component\TextInput;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountRequestType extends AbstractFormType
{
    /**
     * @inheritDoc
     */
    protected function defineInputs(callable $t): array
    {
        return [
            new TextInput(
                'deputyFirstName',
                $t('deputyFirstName.label'),
                constraints: new Constraints(
                    new NotBlank(message: $t('deputyFirstName.notBlank')),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: $t('deputyFirstName.tooShort'),
                        maxMessage: $t('deputyFirstName.tooLong')
                    )
                )
            ),
            new TextInput(
                'deputyLastName',
                $t('deputyLastName.label'),
                constraints: new Constraints(
                    new NotBlank(message: $t('deputyLastName.notBlank')),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: $t('deputyLastName.tooShort'),
                        maxMessage: $t('deputyLastName.tooLong')
                    )
                )
            ),
            new RepeatedEmailInput(
                'deputyEmail',
                $t('deputyEmail.first.label'),
                $t('deputyEmail.second.label'),
                $t('deputyEmail.doesNotMatch'),
                constraints: new Constraints(
                    new NotBlank(message: $t('deputyEmail.notBlank')),
                    new Length(max: 60, maxMessage: $t('deputyEmail.tooLong')),
                    new Email(message: $t('deputyEmail.invalidFormat'))
                )
            ),
            new TextInput(
                'deputyPostCode',
                $t('deputyPostCode.label'),
                characters: 10,
                constraints: new Constraints(
                    new NotBlank(message: $t('deputyPostCode.notBlank')),
                    new Length(max: 10, maxMessage: $t('deputyPostCode.tooLong'))
                )
            ),
            new TextInput(
                'clientLastName',
                $t('clientLastName.label'),
                constraints: new Constraints(
                    new NotBlank(message: $t('clientLastName.notBlank')),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: $t('clientLastName.tooShort'),
                        maxMessage: $t('clientLastName.tooLong')
                    )
                )
            ),
            new TextInput(
                'caseNumber',
                $t('caseNumber.label'),
                characters: 10,
                hint: new HintRecipe($t('caseNumber.help')),
                constraints: new Constraints(
                    new NotBlank(message: $t('caseNumber.wrongLength')),
                    new AtLeastOneOf([
                        new Length(8), new Length(10)
                    ], message: $t('caseNumber.wrongLength'), includeInternalMessages: false)
                )
            ),
            new SubmitButton(
                'signUp',
                $t('signUp.label'),
            )
        ];
    }

    protected string $translationDomain { get => 'twig-components'; }
    protected string $translationPrefix { get => 'opg.account.request'; }
}
