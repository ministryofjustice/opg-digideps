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
     * @param array $options keys: array roleChoices, array roleIdEmptyValue
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleIdOptions = [
            'choices' => $this->options['roleChoices'],
            'empty_value' => $this->options['roleIdEmptyValue'],
        ];

        if (!empty($this->options['roleIdSetTo'])) {
            $roleIdOptions['data'] = $this->options['roleIdSetTo'];

        }

        if (!empty($this->options['roleIdDisabled'])) {
            $roleIdOptions['disabled'] = 'disabled';
        }

        $odrEnabledOptions =[];
        if (!empty($this->options['odrEnabledDisabled'])) {
            $odrEnabledOptions['disabled'] = 'disabled';
        }
        $odrEnabledOptions['data']=true;

        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roleId', 'choice', $roleIdOptions)
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
