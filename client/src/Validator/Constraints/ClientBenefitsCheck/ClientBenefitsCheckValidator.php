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

class ClientBenefitsCheckValidator extends ConstraintValidator
{
    private string $translationDomain = 'report-client-benefits-check';

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
            $this->context
                ->buildViolation($constraint->whenLastCheckedNoOptionSelected)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }
    }

    private function dateLastCheckedEntitlementValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedMissingDate)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }

        if (!is_null($value) && $value > new DateTime()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedFutureDate)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }
    }

    private function neverCheckedExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedNeverCheckedEntitlementMissingExplanation)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedNeverCheckedEntitlementExplanationTooShort)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }
    }

    // Add check for selecting an option to use noOptionSelected

    private function dontKnowIncomeExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW === $object->getDoOthersReceiveIncomeOnClientsBehalf()) {
            $this->context
                ->buildViolation($constraint->incomeOnClientsBehalfNeverCheckedIncomeMissingExplanation)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $this->context
                ->buildViolation($constraint->incomeOnClientsBehalfNeverCheckedIncomeExplanationTooShort)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                ->addViolation();
        }
    }

    private function typesOfIncomeReceivedOnClientsBehalfValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if ($object->getTypesOfIncomeReceivedOnClientsBehalf() instanceof ArrayCollection && 1 === $object->getTypesOfIncomeReceivedOnClientsBehalf()->count()) {
            $income = $object->getTypesOfIncomeReceivedOnClientsBehalf()->first();

            if (is_null($income->getAmount()) && is_null($income->getIncomeType()) && false === $income->getAmountDontKnow()) {
                $this->context
                    ->buildViolation($constraint->incomeOnClientsBehalfMissingIncome)
                    ->setTranslationDomain($this->translationDomain)
                    ->setParameter('%client%', $object->getReport()->getClient()->getFirstName())
                    ->addViolation();
            }
        }
    }
}
