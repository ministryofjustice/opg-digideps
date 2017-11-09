<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Asset form.
 *
 * note: title is hidden (filled from the controller based on AssetTypeTitle form)
 */
abstract class AbstractAssetType extends AbstractType
{
    /**
     * @param string $type
     *
     * @return AbstractAssetType instance
     */
    public static function factory($type)
    {
        switch (strtolower($type)) {
            case 'property':
                return new AssetTypeProperty();
            default:
                return new AssetTypeOther();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFields($builder, $options);

        $builder
                ->add('title', 'hidden')
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }

    abstract protected function addFields($builder, $options);

    public function getName()
    {
        return 'oasset';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-assets',
            'validation_groups' => $this->getValidationGroups(),
        ]);
    }

    protected function getValidationGroups()
    {
        return [];
    }
}
