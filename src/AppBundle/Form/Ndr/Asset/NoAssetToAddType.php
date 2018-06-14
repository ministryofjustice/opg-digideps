<?php

namespace AppBundle\Form\Ndr\Asset;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class NoAssetToAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noAssetToAdd', FormTypes\CheckboxType::class, [
                       'constraints' => new Constraints\NotBlank(['message' => 'ndr.asset.no_assets.notBlank']),
                 ])
                 ->add('saveNoAsset', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'ndr-assets',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ndr_no_assets_add';
    }
}
