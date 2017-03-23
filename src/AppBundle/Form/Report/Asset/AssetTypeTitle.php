<?php

namespace AppBundle\Form\Report\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AssetTypeTitle extends AbstractType
{
    /**
     * @var array
     */
    protected $assetDropdownKeys;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $translatorDomain;

    public function __construct(array $assetDropdownKeys, TranslatorInterface $translator, $translatorDomain)
    {
        $this->assetDropdownKeys = $assetDropdownKeys;
        $this->translator = $translator;
        $this->translatorDomain = $translatorDomain;
    }

    /**
     * @return array with choices for the "title" dropdown element
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
        $builder->add('title', 'choice', [
                'choices' => $this->getTitleChoices(),
                'expanded' => true])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-assets',
            'validation_groups' => 'title_only',
        ]);
    }

    public function getName()
    {
        return 'asset_title';
    }
}
