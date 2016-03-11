<?php

namespace AppBundle\Form\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssetTypeProperty extends AbstractAssetType
{

    protected function addFields($builder, $options)
    {
        $builder
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')
                ->add('county', 'text')
                ->add('occupants', 'textarea')
                //->add('occupants_info', 'text')
                ->add('owned', 'choice', array(
                    'choices' => ['fully' => 'fully', 'partly' => 'partly'],
                    'expanded' => true
                ))
                ->add('owned_percentage', 'text')
                ->add('is_subject_to_equity_release', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ])
                ->add('value', 'number', [
                    'grouping' => true,
                    'precision' => 2,
                    'invalid_message' => 'asset.value.type'
                ])
                ->add('has_mortgage', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ])
                ->add('mortgage_outstanding_amount', 'text')
                ->add('has_charges', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ])
                ->add('is_rented_out', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true
                ])
                ->add('rent_agreement_end_date', 'date', [ 'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date'
                ])
                ->add('rent_income_month', 'text')
               
                
        ;
    }

}
