<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;

class ConfirmDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, $options)
    {
        $builder->add('confirm', FormTypes\SubmitType::class);
    }
}
