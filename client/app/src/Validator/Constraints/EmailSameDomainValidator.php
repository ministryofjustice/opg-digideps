<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use OPG\Digideps\Frontend\Form\Traits\HasTranslatorTrait;
use OPG\Digideps\Frontend\Form\Traits\TokenStorageTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailSameDomainValidator extends ConstraintValidator
{
    use TokenStorageTrait;
    use HasTranslatorTrait;

    /**
     * Validates a given email address matches the same domain as the logged in user.
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $creatorEmail = $this->getLoggedUserEmail();

        $creatorDomain = $this->getDomain($creatorEmail);

        /** @var string $targetValue */
        $targetValue = $value;

        $targetDomain = $this->getDomain($targetValue);

        if (!empty($targetDomain) && $targetDomain !== $creatorDomain && property_exists($constraint, 'message')) {
            $this->context->buildViolation($constraint->message, ['creatorDomain' => $creatorDomain])->atPath('email')->addViolation();
        }
    }

    /**
     * Return domain portion of email address
     */
    private function getDomain(string $email): string
    {
        return substr(strrchr($email, '@'), 1);
    }
}
