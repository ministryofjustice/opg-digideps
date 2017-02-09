<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoneyShortType extends AbstractType
{
    /**
     private $field;

     /**
     * MoneyShortType constructor.
     * @param $field moneyShortCategoriesIn | moneyShortCategoriesOut
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add($this->field, 'collection', [
                'type' => new MoneyShortCategoryType(),
            ])
        ;


        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-short',
            'cascade_validation' => true,
            'validation_groups'  => ['xxx'],
        ]);
    }

    public function getName()
    {
        return 'money_short';
    }
}
