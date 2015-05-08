<?php
namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ChangePasswordTransformer implements DataTransformerInterface
{
    public function transform($string)
    {
        return null;
    }
    
    public function reverseTransform($passwords)
    {
        if(isset($passwords['new_password'])){
            return $passwords['new_password'];
        }
        return null;
    }
}