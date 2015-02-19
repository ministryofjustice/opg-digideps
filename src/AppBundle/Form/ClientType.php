<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder ->add('firstname', 'text')
                 ->add('lastname', 'text')
                 ->add('caseNumber', 'text')
                 ->add('courtDate', 'date', [ 'widget' => 'text',
                                              'input' => 'string',
                                              'format' => 'dd-MM-yyyy'
                                            ])
                ->add('allowedCourtOrderTypes', 'choice', [ 'choices' => $this->getAllowedCourtOrderTypes(), 
                                                            'multiple' => true,
                                                            'expanded' => true ])
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')
                ->add('county', 'text')
                ->add('country', 'country', [ 'preferred_choices' => ['GB']])
                ->add('phone', 'text')
                ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'registration',
        ]);
    }
    
    protected function getAllowedCourtOrderTypes()
    {
        return [ 'test', 'test2' ];
    }
    
    public function getName()
    {
        return 'client';
    }
}