<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddAnotherRecordType extends AbstractType
{
    protected $translationDomain;

    /**
     * AddAnotherRecordType constructor.
     *
     * @param string $translationDomain
     */
    public function __construct($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addAnother', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => 'Please choose yes or no'])],
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
        return 'add_another';
    }
}
