<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class MoneyShortType extends AbstractType
{
    private $field;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->field = $options['field'];

        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add($this->field, FormTypes\CollectionType::class, [
                'entry_type' => MoneyShortCategoryType::class,
            ])
        ;

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-short',
            'constraints' => new Valid(),
            'validation_groups'  => ['xxx']
        ])
         ->setRequired(['field']);
    }

    public function getBlockPrefix()
    {
        return 'money_short';
    }
}
