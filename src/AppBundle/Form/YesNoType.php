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
     * YesNoType constructor.
     * @param $field
     * @param string $translationDomain
     */
    public function __construct($field, $translationDomain)
    {
        $this->field = $field;
        $this->translationDomain = $translationDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->field, 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'Please choose Yes or No'])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => $this->translationDomain,
        ]);
    }

    public function getName()
    {
        return 'yes_no';
    }
}
