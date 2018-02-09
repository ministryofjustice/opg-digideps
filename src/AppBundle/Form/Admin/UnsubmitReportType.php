<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnsubmitReportType extends AbstractType
{
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

                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['name' => 'report']);
    }
}
