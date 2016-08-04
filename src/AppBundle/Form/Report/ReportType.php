<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name formName report or report_edit
     */
    public function __construct($name = 'report')
    {
        $this->name = $name;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('startDate', 'date', ['widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'report.startDate.invalidMessage', ])

                ->add('endDate', 'date', ['widget' => 'text',
                                            'input' => 'datetime',
                                            'format' => 'yyyy-MM-dd',
                                            'invalid_message' => 'report.endDate.invalidMessage',
                                          ])

                /*->add('courtOrderTypeId', 'choice',[ 'choices' => $choices, 
                                                 'empty_data' => null ,
                                                 'empty_value' => 'Please select ..'] )*/
                ->add('courtOrderTypeId', 'hidden')
//                ->add('client', 'hidden')
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'registration',
        ]);
    }

    public function getName()
    {
        return  $this->name;
    }
}
