<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyShortType extends AbstractType
{
    private $field;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->field = $options['field'];

        $builder
            ->add('id', 'hidden')
            ->add($this->field, 'collection', [
                'type' => new MoneyShortCategoryType(),
            ])
        ;

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 'translation_domain' => 'report-money-short'
                               , 'cascade_validation' => true
                               , 'validation_groups'  => ['xxx']
                               ])
                 ->setRequired(['field']);
    }

    public function getName()
    {
        return 'money_short';
    }
}