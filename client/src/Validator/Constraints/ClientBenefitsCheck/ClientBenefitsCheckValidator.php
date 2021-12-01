<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Report\ClientBenefitsCheck;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientBenefitsCheckValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;
    private string $translationDomain = 'report-client-benefits-check';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ClientBenefitsCheckConstraint) {
            throw new UnexpectedTypeException($constraint, ClientBenefitsCheckConstraint::class);
        }

        $object = $this->context->getObject();

        if ('whenLastCheckedEntitlement' === $this->context->getPropertyName()) {
            $this->whenLastCheckedEntitlementValid($value, $object, $constraint);
        }

        if ('dateLastCheckedEntitlement' === $this->context->getPropertyName()) {
            $this->dateLastCheckedEntitlementValid($value, $object, $constraint);
        }

        if ('neverCheckedExplanation' === $this->context->getPropertyName()) {
            $this->neverCheckedExplanationValid($value, $object, $constraint);
        }

        if ('dontKnowIncomeExplanation' === $this->context->getPropertyName()) {
            $this->dontKnowIncomeExplanationValid($value, $object, $constraint);
        }

        if ('typesOfIncomeReceivedOnClientsBehalf' === $this->context->getPropertyName()) {
            $this->typesOfIncomeReceivedOnClientsBehalfValid($value, $object, $constraint);
        }
    }

    private function whenLastCheckedEntitlementValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        $expectedValues = [
            ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED,
            ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING,
            ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED,
        ];

        if (!in_array($value, $expectedValues)) {
            throw new \RuntimeException($this->translator->trans($constraint->whenLastCheckedNoOptionSelected, ['%client%' => $object->getReport()->getClient()->getFirstName()], $this->translationDomain));
        }
    }

    private function dateLastCheckedEntitlementValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $errorMessage = $this->translator->trans(
                $constraint->whenLastCheckedMissingDate,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }

        if (!is_null($value) && $value > new DateTime()) {
            $errorMessage = $this->translator->trans(
                $constraint->whenLastCheckedFutureDate,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }
    }

    private function neverCheckedExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $errorMessage = $this->translator->trans(
                $constraint->whenLastCheckedNeverCheckedEntitlementMissingExplanation,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $errorMessage = $this->translator->trans(
                $constraint->whenLastCheckedNeverCheckedEntitlementExplanationTooShort,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }
    }

    // Add check for selecting an option to use noOptionSelected

    private function dontKnowIncomeExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW === $object->getDoOthersReceiveIncomeOnClientsBehalf()) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeOnClientsBehalfNeverCheckedIncomeMissingExplanation,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeOnClientsBehalfNeverCheckedIncomeExplanationTooShort,
                ['%client%' => $object->getReport()->getClient()->getFirstName()],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }
    }

    private function typesOfIncomeReceivedOnClientsBehalfValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if ($object->getTypesOfIncomeReceivedOnClientsBehalf() instanceof ArrayCollection && 1 === $object->getTypesOfIncomeReceivedOnClientsBehalf()->count()) {
            $income = $object->getTypesOfIncomeReceivedOnClientsBehalf()->first();

            if (is_null($income->getAmount()) && is_null($income->getIncomeType()) && false === $income->getAmountDontKnow()) {
                $errorMessage = $this->translator->trans(
                    $constraint->incomeOnClientsBehalfMissingIncome,
                    ['%client%' => $object->getReport()->getClient()->getFirstName()],
                    $this->translationDomain
                );

                $this->context
                    ->buildViolation($errorMessage)
                    ->addViolation();
            }
        }
    }
}
