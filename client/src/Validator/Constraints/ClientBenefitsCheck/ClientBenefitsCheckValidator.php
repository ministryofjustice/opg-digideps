<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\Report\ClientBenefitsCheck;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ClientBenefitsCheckValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ClientBenefitsCheckConstraint) {
            throw new UnexpectedTypeException($constraint, ClientBenefitsCheckConstraint::class);
        }

        $object = $this->context->getObject();

        if ('whenLastCheckedEntitlement' === $this->context->getPropertyName()) {
            $this->whenLastCheckedEntitlementValid($value, $object);
        }

        if ('dateLastCheckedEntitlement' === $this->context->getPropertyName()) {
            $this->dateLastCheckedEntitlementValid($value, $object);
        }

        if ('neverCheckedExplanation' === $this->context->getPropertyName()) {
            $this->neverCheckedExplanationValid($value, $object);
        }

        if ('dontKnowIncomeExplanation' === $this->context->getPropertyName()) {
            $this->dontKnowIncomeExplanationValid($value, $object);
        }

        if ('typesOfIncomeReceivedOnClientsBehalf' === $this->context->getPropertyName()) {
            $this->typesOfIncomeReceivedOnClientsBehalfValid($value, $object);
        }
    }

    private function whenLastCheckedEntitlementValid($value, ClientBenefitsCheck $object)
    {
        $expectedValues = [
            ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED,
            ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING,
            ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED,
        ];

        if (!in_array($value, $expectedValues)) {
            $unexpectedValueMessage = sprintf(
                '$whenLastCheckedEntitlementValid must be one of %s',
                implode(',', $expectedValues)
            );

            $this->context
                ->buildViolation($unexpectedValueMessage)
                ->addViolation();
        }
    }

    private function dateLastCheckedEntitlementValid($value, ClientBenefitsCheck $object)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation('Must provide a date when have checked entitlement')
                ->addViolation();
        }
    }

    private function neverCheckedExplanationValid($value, ClientBenefitsCheck $object)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation('Must provide an explanation when never checked entitlement')
                ->addViolation();
        }
    }

    private function dontKnowIncomeExplanationValid($value, ClientBenefitsCheck $object)
    {
        if (is_null($value) && ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW === $object->getDoOthersReceiveIncomeOnClientsBehalf()) {
            $this->context
                ->buildViolation('Must provide an explanation when you don\'t know if anyone else received income on clients behalf')
                ->addViolation();
        }
    }

    private function typesOfIncomeReceivedOnClientsBehalfValid($value, ClientBenefitsCheck $object)
    {
        if ($object->getTypesOfIncomeReceivedOnClientsBehalf() instanceof ArrayCollection && 1 === $object->getTypesOfIncomeReceivedOnClientsBehalf()->count()) {
            $income = $object->getTypesOfIncomeReceivedOnClientsBehalf()->first();

            if (is_null($income->getAmount()) && is_null($income->getIncomeType()) && false === $income->getAmountDontKnow()) {
                $this->context
                    ->buildViolation('Must add at least one type of income received by others if answering "yes" to "Do others receive income ion clients behalf". Use the back link if you do not have any income to declare.')
                    ->addViolation();
            }
        }
    }
}
