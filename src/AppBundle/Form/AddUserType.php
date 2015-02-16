<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Role;

class AddUserType extends AbstractType
{
    /**
     * @var array 
     */
    private $roleChoices = [];
    
    /**
     * @param Role[] $roles
     */
    public function __construct(array $roles)
    {
        foreach ($roles as $role) {
            $this->roleChoices[$role->getRole()] = $role->getName();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('email', 'text')
                 ->add('firstname', 'text')
                 ->add('lastname', 'text')
                 ->add('role', 'choice', array(
                    'choices' => $this->roleChoices
                  ))
                 ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'admin',
              'validation_groups' => ['admin_add_user'],
        ]);
    }
    
    public function getName()
    {
        return 'admin';
    }
}
