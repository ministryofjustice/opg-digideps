<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use AppBundle\Form\DataTransformer\ChangePasswordTransformer;


class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password','password', [ 'constraints' => new SecurityAssert\UserPassword()])
                ->add('new_password', 'repeated', [
                        'type' => 'password',
                        'invalid_message' => 'Password does not match' ])
                ->addModelTransformer(new ChangePasswordTransformer()); 
    }
    
    public function getParent() 
    {
        return 'form';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'user-activate'
        ]);
    }
    
    public function getName()
    {
        return 'change_password';
    }
}
