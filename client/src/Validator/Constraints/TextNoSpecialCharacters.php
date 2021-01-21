<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TextNoSpecialCharacters extends Constraint
{
    public $message = 'This text box contains a illegal special characters.';
}
