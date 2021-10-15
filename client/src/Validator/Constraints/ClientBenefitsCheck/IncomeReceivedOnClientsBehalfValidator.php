<?php

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\Validator\Constraints\ClientBenefitsCheck\IncomeReceivedOnClientsBehalf as IncomeReceivedOnClientsBehalfConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IncomeReceivedOnClientsBehalfValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IncomeReceivedOnClientsBehalfConstraint) {
            throw new UnexpectedTypeException($constraint, IncomeReceivedOnClientsBehalfConstraint::class);
        }

        /** @var IncomeReceivedOnClientsBehalf $object */
        $object = $this->context->getObject();

        if ('amount' === $this->context->getPropertyName()) {
            $this->amountValid($value, $object);
        }

        if ('amountDontKnow' === $this->context->getPropertyName()) {
            $this->amountDontKnowValid($value, $object);
        }
    }

    private function amountValid($value, IncomeReceivedOnClientsBehalf $object)
    {
        if (!is_null($value) && true === $object->getAmountDontKnow()) {
            $this->context
                ->buildViolation("You've provided an amount and confirmed you don't know the income amount. Either remove the amount value or untick don't know amount.")
                ->addViolation();
        }

        if (is_null($value) && false === $object->getAmountDontKnow() && !is_null($object->getIncomeType())) {
            $this->context
                ->buildViolation("Either provide an amount or tick don't know amount.")
                ->addViolation();
        }
    }

    private function amountDontKnowValid($value, IncomeReceivedOnClientsBehalf $object)
    {
        if (true === $value && !is_null($object->getAmount())) {
            $this->context
                ->buildViolation("You've confirmed you don't know the income amount and provided an amount. Either untick don't know amount or remove the amount value.")
                ->addViolation();
        }

        if (false === $value && is_null($object->getAmount()) && !is_null($object->getIncomeType())) {
            $this->context
                ->buildViolation("Either provide an amount or tick don't know amount.")
                ->addViolation();
        }
    }
}
