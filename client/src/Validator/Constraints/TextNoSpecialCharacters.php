<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TextNoSpecialCharacters extends Constraint
{
    public $message = 'This text box contains special characters that are not permitted.';
}
