<?php

namespace AppBundle\Form\Ad;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddUserType extends AbstractType
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options keys: array roleChoices, array roleNameEmptyValue
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleNameOptions = [
            'choices' => $this->options['roleChoices'],
            'empty_value' => $this->options['roleNameEmptyValue'],
        ];

        if (!empty($this->options['roleNameSetTo'])) {
            $roleNameOptions['data'] = $this->options['roleNameSetTo'];
        }

        if (!empty($this->options['roleNameDisabled'])) {
            $roleNameOptions['disabled'] = 'disabled';
        }

        $odrEnabledOptions =[];
        if (!empty($this->options['odrEnabledDisabled'])) {
            $odrEnabledOptions['disabled'] = 'disabled';
        }
        $odrEnabledOptions['data']=true;

        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roleName', 'choice', $roleNameOptions)
            ->add('odrEnabled', 'checkbox', $odrEnabledOptions)
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ad',
            'validation_groups' => ['ad_add_user'],
        ]);
    }

    public function getName()
    {
        return 'ad';
    }
}
