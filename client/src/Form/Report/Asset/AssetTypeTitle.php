<?php

namespace App\Form\Report\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssetTypeTitle extends AbstractType
{
    public function __construct(protected array $assetDropdownKeys, protected TranslatorInterface $translator, protected $translatorDomain)
    {
    }

    /**
     * @return array with choices for the "title" dropdown element. key and values
     */
    public function getTitleChoices()
    {
        if (empty($this->assetDropdownKeys)) {
            return [];
        }

        $ret = [];

        // translate keys and order by name
        foreach ($this->assetDropdownKeys as $key) {
            $translation = $this->translator->trans('form.title.choices.' . $key, [], $this->translatorDomain);
            $ret[$translation] = $translation;
        }

        return $ret;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', FormTypes\ChoiceType::class, [
                'choices' => $this->getTitleChoices(),
                'expanded' => true])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-assets',
            'validation_groups' => 'title_only',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'asset_title';
    }
}
