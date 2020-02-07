<?php


namespace AppBundle\Form\Admin\Fixture;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompleteSectionsFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // List of sections to complete
        $builder
            ->add('decisions', CheckboxType::class, [
                'label'    => 'Decisions',
                'required' => false,
            ])
            ->add('contacts', CheckboxType::class, [
                'label'    => 'Contacts',
                'required' => false,
            ])
            ->add('visitsCare', CheckboxType::class, [
                'label'    => 'Visits and Care',
                'required' => false,
            ])
            ->add('actions', CheckboxType::class, [
                'label'    => 'Actions',
                'required' => false,
            ])
            ->add('otherInfo', CheckboxType::class, [
                'label'    => 'Other Info',
                'required' => false,
            ])
            ->add('documents', CheckboxType::class, [
                'label'    => 'Documents',
                'required' => false,
            ])
            ->add('expenses', CheckboxType::class, [
                'label'    => 'Expenses',
                'required' => false,
            ])
            ->add('gifts', CheckboxType::class, [
                'label'    => 'Gifts',
                'required' => false,
            ])
            ->add('bankAccounts', CheckboxType::class, [
                'label'    => 'Bank Accounts',
                'required' => false,
            ])
            ->add('moneyIn', CheckboxType::class, [
                'label'    => 'Money In',
                'required' => false,
            ])
            ->add('moneyOut', CheckboxType::class, [
                'label'    => 'Money Out',
                'required' => false,
            ])
            ->add('moneyInShort', CheckboxType::class, [
                'label'    => 'Money In Short',
                'required' => false,
            ])
            ->add('moneyOutShort', CheckboxType::class, [
                'label'    => 'Money Out Short',
                'required' => false,
            ])
            ->add('assets', CheckboxType::class, [
                'label'    => 'Assets',
                'required' => false,
            ])
            ->add('debts', CheckboxType::class, [
                'label'    => 'Debts',
                'required' => false,
            ])
            ->add('lifestyle', CheckboxType::class, [
                'label'    => 'Lifestyle',
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures'
        ]);
    }

}
