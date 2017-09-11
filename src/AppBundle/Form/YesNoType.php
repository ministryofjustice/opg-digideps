<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Generic type for Yes/No questions with
 * - single field yes/no (pass the name via ctor)
 * - notBlank validator
 * - save button
 */
class YesNoType extends AbstractType
{
    /**
     * @var string field name
     */
    private $field;

    /**
     * @var string translation domain used for labels
     */
    private $translationDomain;

    /**
     * @var array
     */
    private $choices;

    /**
     * YesNoType constructor.
     * @param $field
     * @param string $translationDomain
     * @param array  $choices
     */
    public function __construct($field, $translationDomain, array $choices)
    {
        $this->field = $field;
        $this->translationDomain = $translationDomain;
        $this->choices = $choices;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->field, 'choice', [
                'choices' => $this->choices,
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => "Please select either 'Yes' or 'No'", 'groups'=>'yesno_type_custom'])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => $this->translationDomain,
            'validation_groups' => ['yesno_type_custom']
        ]);
    }

    public function getName()
    {
        return 'yes_no';
    }
}
