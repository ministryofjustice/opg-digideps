<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class UserDetailsType extends AbstractType
{
    /**
     * @var string
     */
    private $addressCountryEmptyValue;
    
    /**
     * @var array
     */
    private $countryPreferredOptions;
    
    /**
     * @param array $options needed keys: addressCountryEmptyValue
     */
    public function __construct($options)
    {
        $this->addressCountryEmptyValue = empty($options['addressCountryEmptyValue']) 
                                        ? null : $options['addressCountryEmptyValue'];
        
        $this->countryPreferredOptions = empty($options['countryPreferredOptions']) 
                                        ? null : $options['countryPreferredOptions'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', 'text')
                ->add('lastname', 'text')
                ->add('address1', 'text')
                ->add('address2', 'text')
                ->add('address3', 'text')
                ->add('addressPostcode', 'text')
                ->add('addressCountry', 'country', [
                    'preferred_choices' => $this->countryPreferredOptions,
                    'empty_value' => $this->addressCountryEmptyValue
                ])
                ->add('phoneHome', 'text')
                ->add('phoneWork', 'text')
                ->add('phoneMobile', 'text')
                ->add('save', 'submit');
    }
    
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'user-details',
              'validation_groups' => ['user_details'],
        ]);
    }
    
    public function getName()
    {
        return 'user_details';
    }
}
