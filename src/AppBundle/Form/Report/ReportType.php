<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    /**
     * @var string
     */
    private $name;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->name = $options['name'];

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

                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['name' => 'report']);
    }

    public function getName()
    {
        return $this->name;
    }
}
