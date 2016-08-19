<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class NoAssetToAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noAssetToAdd', 'checkbox', [
                       'constraints' => new Constraints\NotBlank(['message' => 'odr.asset.no_assets.notBlank']),
                 ])
                 ->add('saveNoAsset', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'odr-assets',
        ]);
    }

    public function getName()
    {
        return 'odr_no_assets_add';
    }
}
