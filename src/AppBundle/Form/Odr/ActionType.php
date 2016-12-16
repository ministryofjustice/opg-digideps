<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Account;
use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionType extends AbstractType
{
    private $step;

    /**
     * @param $step
     */
    public function __construct($step)
    {
        $this->step = (int)$step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden');

        if ($this->step === 1) {
            $builder
                ->add('actionGiveGiftsToClient', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ))
                ->add('actionGiveGiftsToClientDetails', 'textarea');
        }

        if ($this->step === 2) {
            $builder->add('actionPropertyMaintenance', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ));
        }

        if ($this->step === 3) {
            $builder->add('actionPropertySellingRent', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ));
        }

        if ($this->step === 4) {
            $builder->add('actionPropertyBuy', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ));
        }

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'odr_actions';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-actions',
            'validation_groups' => function (FormInterface $form) {

                $odr = $form->getData();
                /* @var $odr Odr */

                return [
                    1 => ($odr->getActionGiveGiftsToClient() == 'yes')
                        ? ['action-give-gifts', 'action-give-gifts-details']
                        : ['action-give-gifts'],
                    2 => ['action-property-maintenance'],
                    3 => ['action-property-selling-rent'],
                    4 => ['action-property-buy'],
                ][$this->step];
            },
        ]);
    }
}
