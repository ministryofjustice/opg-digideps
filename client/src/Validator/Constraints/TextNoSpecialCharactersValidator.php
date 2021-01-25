<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TextNoSpecialCharactersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TextNoSpecialCharacters) {
            throw new UnexpectedTypeException($constraint, TextNoSpecialCharacters::class);
        }
        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        // Allowed .
        // Reserved : / ? # [ ] @ ! $ & ' ( ) * + , ; =
        // Danger " < > % { } | \ ^ `
        // ASCII Control characters	Includes the ISO-8859-1 (ISO-Latin) character ranges 00-1F hex (0-31 decimal) and 7F (127 decimal)	YES
        // Non-ASCII characters	Includes the entire “top half” of the ISO-Latin set 80-FF hex (128-255 decimal)

        //We take pragmatic approach and disallow the following:

        if (preg_match("/[<>|^`{}]/", $value, $matches)) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
        }

        //Disallow @ symbols not between letters or numbers
        if (preg_match("/[^A-Za-z0-9][@]|[@][^A-Za-z0-9]|^(@)|(@)$/", $value, $matches)) {
            $this->context->buildViolation("The @ symbol is found in an incorrect position")
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }

        //Allow apostrophe only between space and Alphanumeric and end of sentence
        if (preg_match("/[^A-Za-z0-9 ][']|['][^A-Za-z0-9 ]|^(')/", $value, $matches)) {
            $this->context->buildViolation("Special characters around apostrophe disallowed")
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
