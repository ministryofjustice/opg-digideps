<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportDeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder /*->add('title', 'text')*/
                 ->add('id', 'hidden')
                 ->add('agree', 'checkbox', [
                     'constraints' => new NotBlank(['message'=>'report-declaration.agree.notBlank']),
                 ])
                 ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'report-declaration'
        ]);
    }
    
    public function getName()
    {
        return 'report_declaration';
    }
}
