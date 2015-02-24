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
    private $filter;
    
    /**
     * @param \AppBundle\Service\Util $util
     */
    public function __construct($util, array $filter = []) 
    {
        $this->util = $util;
        $this->filter = $filter;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!empty($this->filter)){
            $choices = $this->util->getAllowedCourtOrderTypeChoiceOptions($this->filter);
        }else{
            $choices = $this->util->getAllowedCourtOrderTypeChoiceOptions();
        }
        
        $builder->add('startDate', 'date', [ 'widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'report.startDate.invalidMessage' ])
                
                ->add('endDate', 'date', [ 'widget' => 'text',
                                            'input' => 'datetime',
                                            'format' => 'yyyy-MM-dd',
                                            'invalid_message' => 'report.endDate.invalidMessage'
                                          ])
                
                ->add('courtOrderType', 'choice',   [ 'choices' => $choices, 
                                                      'empty_data' => null ,
                                                      'empty_value' => 'Please select ..'] )
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