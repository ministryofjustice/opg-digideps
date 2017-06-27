<?php

namespace AppBundle\Form\Report;

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
     * ReportType constructor.
     * @param string $name //TODO not clear why this is passed. Try to remove and update behat tests
     *  but using different translations
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

                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            // since used to create and edit report, this has to be set from the controller
        ]);
    }

    public function getName()
    {
        return  $this->name;
    }
}
