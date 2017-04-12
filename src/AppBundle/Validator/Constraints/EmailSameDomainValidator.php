<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Model\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use AppBundle\Form\Traits\HasSecurityContextTrait;
use AppBundle\Form\Traits\HasTranslatorTrait;

class EmailSameDomainValidator extends ConstraintValidator
{
    use HasSecurityContextTrait;
    use HasTranslatorTrait;

    /**
     * Validates a given email address matches the same domain as the logged in user
     *
     * @param mixed $email
     * @param Constraint $constraint
     */
    public function validate($email, Constraint $constraint)
    {
        $creatorEmail = $this->getLoggedUserEmail();

        $creatorDomain = $this->getDomain($creatorEmail);
        $targetDomain = $this->getDomain($email);

        if (!empty($targetDomain) && $targetDomain !== $creatorDomain) {
            $this->context->addViolationAt('email', $constraint->message, ['creatorDomain' => $creatorDomain]);
        }
    }

    /**
     * Return domain portion of email address
     *
     * @param $email string
     *
     * @return string
     */
    private function getDomain($email)
    {
        return substr(strrchr($email, "@"), 1);
    }
}
