<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class NoAssetToAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noAssetToAdd', FormTypes\CheckboxType::class, [
                       'constraints' => new Constraints\NotBlank(['message' => 'asset.no_assets.notBlank']),
                 ])
                 ->add('saveNoAsset', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'report-assets',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'report';
    }
}
