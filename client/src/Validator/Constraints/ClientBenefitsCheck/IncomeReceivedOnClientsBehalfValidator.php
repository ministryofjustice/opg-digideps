<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\IncomeReceivedOnClientsBehalfInterface;
use App\Validator\Constraints\ClientBenefitsCheck\IncomeReceivedOnClientsBehalf as IncomeReceivedOnClientsBehalfConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IncomeReceivedOnClientsBehalfValidator extends ConstraintValidator
{
    private string $translationDomain = 'report-client-benefits-check';

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IncomeReceivedOnClientsBehalfConstraint) {
            throw new UnexpectedTypeException($constraint, IncomeReceivedOnClientsBehalfConstraint::class);
        }

        /** @var IncomeReceivedOnClientsBehalfInterface $object */
        $object = $this->context->getObject();
        $propertyName = $this->context->getPropertyName();

        if ('incomeType' === $propertyName) {
            if (is_null($value)) {
                $this->context
                    ->buildViolation($constraint->incomeDetailsMissingIncomeTypeMessage)
                    ->setTranslationDomain($this->translationDomain)
                    ->addViolation();
            }
        }

        if ('amount' === $propertyName) {
            $this->amountValid($value, $object, $constraint);
        }

        if ('amountDontKnow' === $propertyName) {
            $this->amountDontKnowValid($value, $object, $constraint);
        }
    }

    private function amountValid($value, IncomeReceivedOnClientsBehalfInterface $object, IncomeReceivedOnClientsBehalfConstraint $constraint)
    {
        if (!is_null($value) && true === $object->getAmountDontKnow()) {
            $this->context
                ->buildViolation($constraint->incomeDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if (is_null($value) && false === $object->getAmountDontKnow() && !is_null($object->getIncomeType())) {
            $this->context
                ->buildViolation($constraint->incomeDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }

    private function amountDontKnowValid($value, IncomeReceivedOnClientsBehalfInterface $object, IncomeReceivedOnClientsBehalfConstraint $constraint)
    {
        if (true === $value && !is_null($object->getAmount())) {
            $this->context
                ->buildViolation($constraint->incomeDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if (false === $value && is_null($object->getAmount()) && !is_null($object->getIncomeType())) {
            $this->context
                ->buildViolation($constraint->incomeDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }
}
