<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
{
    /**
     * @var \AppBundle\Service\ApiClient 
     */
    private $apiClient;
    
     /**
     * @var string
     */
    private $addressCountryEmptyValue;
    
    
    public function __construct($apiClient, $options) 
    {
        $this->apiClient = $apiClient;
        $this->addressCountryEmptyValue = empty($options['addressCountryEmptyValue']) 
                                        ? null : $options['addressCountryEmptyValue'];
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('firstname', 'text')
                 ->add('lastname', 'text')
                 ->add('caseNumber', 'text')
                 ->add('courtDate', 'date', [ 'widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'client.courtDate.message'
                                            ])
                ->add('allowedCourtOrderTypes', 'choice', [ 'choices' => $this->getAllowedCourtOrderTypes(), 
                                                            'multiple' => true,
                                                            'expanded' => true ])
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')
                ->add('county','text')
                ->add('country', 'country', [ 
                    'preferred_choices' => ['GB'], 
                    'empty_value' => $this->addressCountryEmptyValue
                ])
                ->add('phone', 'text')
                ->add('user', 'hidden')
                ->add('id', 'hidden')
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
        $choices = [];
        $response = $this->apiClient->get('get_all_court_order_type');
        
        if($response->getStatusCode() == 200){
            $arrayData = $response->json();
        
            foreach($arrayData['data']['court_order_types'] as $value){
                $choices[$value['id']] = $value['name'];
            }
        }
        return $choices;
    }
    
    public function getName()
    {
        return 'client';
    }
}