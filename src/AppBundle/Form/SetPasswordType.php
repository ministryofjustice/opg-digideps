<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class SetPasswordType extends AbstractType
{
    protected $options;
    
    function __construct($options)
    {
        $this->options = $options;
    }

                
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('email', 'text', [
                    'attr'=>['readonly'=>'readonly'
                ]])
                ->add('password', 'repeated',[
                    'type' => 'password',
                    'invalid_message' => $this->options['passwordMismatchMessage']
                ])
                ->add('save', 'submit');
    }
    
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'user-activate'
        ]);
    }
    
    public function getName()
    {
        return 'set_password';
    }
}
