<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Form\Report\MoneyShortCategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MoneyShortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('moneyShortCategoriesIn', 'collection', [
                'type' => new MoneyShortCategoryType(),
            ]);


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
        return 'income_benefits';
    }
}
