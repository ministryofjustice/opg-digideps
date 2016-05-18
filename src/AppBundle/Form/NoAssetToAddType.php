<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class NoAssetToAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noAssetToAdd', 'checkbox', [
                       'constraints' => new Constraints\NotBlank(['message' => 'asset.no_assets.notBlank']),
                 ])
                 ->add('saveNoAsset', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'report-assets',
        ]);
    }

    public function getName()
    {
        return 'report';
    }
}
