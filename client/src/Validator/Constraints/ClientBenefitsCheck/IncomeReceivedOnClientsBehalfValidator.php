<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\IncomeReceivedOnClientsBehalfInterface;
use App\Validator\Constraints\ClientBenefitsCheck\IncomeReceivedOnClientsBehalf as IncomeReceivedOnClientsBehalfConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class IncomeReceivedOnClientsBehalfValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;
    private string $translationDomain = 'report-client-benefits-check';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IncomeReceivedOnClientsBehalfConstraint) {
            throw new UnexpectedTypeException($constraint, IncomeReceivedOnClientsBehalfConstraint::class);
        }

        /** @var IncomeReceivedOnClientsBehalfInterface $object */
        $object = $this->context->getObject();

        if ('amount' === $this->context->getPropertyName()) {
            $this->amountValid($value, $object, $constraint);
        }

        if ('amountDontKnow' === $this->context->getPropertyName()) {
            $this->amountDontKnowValid($value, $object, $constraint);
        }
    }

    private function amountValid($value, IncomeReceivedOnClientsBehalfInterface $object, IncomeReceivedOnClientsBehalfConstraint $constraint)
    {
        if (!is_null($value) && true === $object->getAmountDontKnow()) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeDetailsAmountAndDontKnowMessage,
                [],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }

        if (is_null($value) && false === $object->getAmountDontKnow() && !is_null($object->getIncomeType())) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeDetailsMissingAmountMessage,
                [],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }
    }

    private function amountDontKnowValid($value, IncomeReceivedOnClientsBehalfInterface $object, IncomeReceivedOnClientsBehalfConstraint $constraint)
    {
        if (true === $value && !is_null($object->getAmount())) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeDetailsAmountAndDontKnowMessage,
                [],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }

        if (false === $value && is_null($object->getAmount()) && !is_null($object->getIncomeType())) {
            $errorMessage = $this->translator->trans(
                $constraint->incomeDetailsMissingAmountMessage,
                [],
                $this->translationDomain
            );

            $this->context
                ->buildViolation($errorMessage)
                ->addViolation();
        }
    }
}
