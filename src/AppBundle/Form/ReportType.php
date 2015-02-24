<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    /**
     * @var \AppBundle\Service\Util $util
     */
    private $util;
    
    /**
     * @param \AppBundle\Service\Util $util
     */
    public function __construct($util) 
    {
        $this->util = $util;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', 'date', [ 'widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => '' ])
                
                ->add('endDate', 'date', [ 'widget' => 'text',
                                            'input' => 'datetime',
                                            'format' => 'yyyy-MM-dd',
                                            'invalid_message' => ''
                                          ])
                
                ->add('courtOrderType', 'choice',   [ 'choices' => $this->util->getAllowedCourtOrderTypeChoiceOptions(), 
                                                      'empty_data' => null ,
                                                      'empty_value' => 'Please select ..'] )
                ->add('client', 'hidden')
                ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'registration',
        ]);
    }
    
    public function getName()
    {
        return 'report';
    }
}