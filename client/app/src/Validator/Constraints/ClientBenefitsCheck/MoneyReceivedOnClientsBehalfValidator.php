<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck;

use OPG\Digideps\Frontend\Entity\MoneyReceivedOnClientsBehalfInterface;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck\MoneyReceivedOnClientsBehalf as MoneyReceivedOnClientsBehalfConstraint;
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

        if ($propertyName === 'moneyType') {
            if (is_null($value)) {
                $this->context
                    ->buildViolation($constraint->moneyDetailsMissingMoneyTypeMessage)
                    ->setTranslationDomain($this->translationDomain)
                    ->addViolation();
            }
        }

        if ($propertyName === 'whoReceivedMoney') {
            if (is_null($value)) {
                $this->context
                    ->buildViolation($constraint->moneyDetailsMissingWhoReceivedMoneyMessage)
                    ->setTranslationDomain($this->translationDomain)
                    ->addViolation();
            }
        }

        if ($propertyName === 'amount') {
            $this->amountValid($value, $object, $constraint);
        }

        if ($propertyName === 'amountDontKnow') {
            $this->amountDontKnowValid($value, $object, $constraint);
        }
    }

    private function amountValid($value, MoneyReceivedOnClientsBehalfInterface $object, MoneyReceivedOnClientsBehalfConstraint $constraint)
    {
        if (!is_null($value) && $object->getAmountDontKnow() === true) {
            $this->context
                ->buildViolation($constraint->moneyDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if (is_null($value) && $object->getAmountDontKnow() === false) {
            $this->context
                ->buildViolation($constraint->moneyDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }

    private function amountDontKnowValid($value, MoneyReceivedOnClientsBehalfInterface $object, MoneyReceivedOnClientsBehalfConstraint $constraint)
    {
        if ($value === true && !is_null($object->getAmount())) {
            $this->context
                ->buildViolation($constraint->moneyDetailsAmountAndDontKnowMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }

        if ($value === false && is_null($object->getAmount())) {
            $this->context
                ->buildViolation($constraint->moneyDetailsMissingAmountMessage)
                ->setTranslationDomain($this->translationDomain)
                ->addViolation();
        }
    }
}
