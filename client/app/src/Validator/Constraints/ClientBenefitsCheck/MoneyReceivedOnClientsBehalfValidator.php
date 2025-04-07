<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use App\Validator\Constraints\ClientBenefitsCheck\MoneyReceivedOnClientsBehalf as MoneyReceivedOnClientsBehalfConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MoneyReceivedOnClientsBehalfValidator extends ConstraintValidator
{
    private string $translationDomain = 'report-client-benefits-check';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MoneyReceivedOnClientsBehalfConstraint) {
            throw new UnexpectedTypeException($constraint, MoneyReceivedOnClientsBehalfConstraint::class);
        }

        /** @var MoneyReceivedOnClientsBehalfInterface $object */
        $object = $this->context->getObject();
        $propertyName = $this->context->getPropertyName();

        if ('moneyType' === $propertyName) {
            if (is_null($value)) {
                $this->context
                    ->buildViolation($constraint->moneyDetailsMissingMoneyTypeMessage)
                    ->setTranslationDomain($this->translationDomain)
                    ->addViolation();
            }
        }

        if ('whoReceivedMoney' === $propertyName) {
            if (is_null($value)) {
                $this->context
                    ->buildViolation($constraint->moneyDetailsMissingWhoReceivedMoneyMessage)
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

    private function amountValid($value, MoneyReceivedOnClientsBehalfInterface $object, MoneyReceivedOnClientsBehalfConstraint $constraint)
    {
        if (!is_null($value) && true === $object->getAmountDontKnow()) {
            $this->context
                ->buildViolation($constraint->moneyDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if (is_null($value) && false === $object->getAmountDontKnow()) {
            $this->context
                ->buildViolation($constraint->moneyDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }

    private function amountDontKnowValid($value, MoneyReceivedOnClientsBehalfInterface $object, MoneyReceivedOnClientsBehalfConstraint $constraint)
    {
        if (true === $value && !is_null($object->getAmount())) {
            $this->context
                ->buildViolation($constraint->moneyDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if (false === $value && is_null($object->getAmount())) {
            $this->context
                ->buildViolation($constraint->moneyDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }
}
