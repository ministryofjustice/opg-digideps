<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Report;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ClientBenefitsCheckValidator extends ConstraintValidator
{
    private string $translationDomain = 'report-client-benefits-check';
    private ?string $clientName = null;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ClientBenefitsCheckConstraint) {
            throw new UnexpectedTypeException($constraint, ClientBenefitsCheckConstraint::class);
        }

        $object = $this->context->getObject();
        $report = $object->getReport() instanceof Report ? $object->getReport() : $object->getNdr();
        $this->clientName = $report->getClient()->getFirstName();
        $propertyName = $this->context->getPropertyName();

        if ('whenLastCheckedEntitlement' === $propertyName) {
            $this->whenLastCheckedEntitlementValid($value, $object, $constraint);
        }

        if ('dateLastCheckedEntitlement' === $propertyName) {
            $this->dateLastCheckedEntitlementValid($value, $object, $constraint);
        }

        if ('neverCheckedExplanation' === $propertyName) {
            $this->neverCheckedExplanationValid($value, $object, $constraint);
        }

        if ('doOthersReceiveMoneyOnClientsBehalf' === $propertyName) {
            $this->moneyOnClientsBehalfValid($value, $object, $constraint);
        }

        if ('dontKnowMoneyExplanation' === $propertyName) {
            $this->dontKnowMoneyExplanationValid($value, $object, $constraint);
        }

        if ('typesOfMoneyReceivedOnClientsBehalf' === $propertyName) {
            $this->typesOfMoneyReceivedOnClientsBehalfValid($value, $object, $constraint);
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
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }
    }

    private function dateLastCheckedEntitlementValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedMissingDate)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }

        if (!is_null($value) && $value > new DateTime()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedFutureDate)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }
    }

    private function neverCheckedExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED === $object->getWhenLastCheckedEntitlement()) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedNeverCheckedEntitlementMissingExplanation)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $this->context
                ->buildViolation($constraint->whenLastCheckedNeverCheckedEntitlementExplanationTooShort)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }
    }

    private function moneyOnClientsBehalfValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value)) {
            $this->context
                ->buildViolation($constraint->moneyOnClientsBehalfNoOptionSelected)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }
    }

    private function dontKnowMoneyExplanationValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if (is_null($value) && ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW === $object->getDoOthersReceiveMoneyOnClientsBehalf()) {
            $this->context
                ->buildViolation($constraint->moneyOnClientsBehalfNeverCheckedMoneyMissingExplanation)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }

        if (!is_null($value) && strlen($value) < 4) {
            $this->context
                ->buildViolation($constraint->moneyOnClientsBehalfNeverCheckedMoneyExplanationTooShort)
                ->setTranslationDomain($this->translationDomain)
                ->setParameter('%client%', $this->clientName)
                ->addViolation();
        }
    }

    private function typesOfMoneyReceivedOnClientsBehalfValid($value, ClientBenefitsCheckInterface $object, ClientBenefitsCheckConstraint $constraint)
    {
        if ($object->getTypesOfMoneyReceivedOnClientsBehalf() instanceof ArrayCollection && 1 === $object->getTypesOfMoneyReceivedOnClientsBehalf()->count()) {
            $money = $object->getTypesOfMoneyReceivedOnClientsBehalf()->first();

            if (is_null($money->getAmount()) && is_null($money->getMoneyType()) && false === $money->getAmountDontKnow()) {
                $this->context
                    ->buildViolation($constraint->moneyOnClientsBehalfMissingMoney)
                    ->setTranslationDomain($this->translationDomain)
                    ->setParameter('%client%', $this->clientName)
                    ->addViolation();
            }
        }
    }
}
