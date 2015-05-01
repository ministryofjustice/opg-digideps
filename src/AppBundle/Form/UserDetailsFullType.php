<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class UserDetailsFullType extends UserDetailsBasicType
{
    /**
     * @var string
     */
    private $addressCountryEmptyValue;
    
    /**
     * @param array $options needed keys: addressCountryEmptyValue, countryPreferredOptions
     */
    public function __construct($options)
    {
        $this->addressCountryEmptyValue = empty($options['addressCountryEmptyValue']) 
                                        ? null : $options['addressCountryEmptyValue'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('address1', 'text')
        ->add('address2', 'text')
        ->add('address3', 'text')
        ->add('addressPostcode', 'text')
        ->add('addressCountry', 'country', [
            'preferred_choices' => ['', 'GB'],
            'empty_value' => $this->addressCountryEmptyValue
        ])
        ->add('phoneMain', 'text')
        ->add('phoneAlternative', 'text');
    }
    
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'user-details',
            'validation_groups' => ['user_details_full'],
        ]);
    }
    
    public function getName()
    {
        return 'user_details';
    }
}
