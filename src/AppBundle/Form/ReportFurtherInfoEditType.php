<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportFurtherInfoEditType extends ReportFurtherInfoAddType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
                ->add('id', 'hidden')
                ->add('furtherInformation', 'textarea')
                ->add('edit', 'submit')
                ->add('next', 'submit');
    }
}
