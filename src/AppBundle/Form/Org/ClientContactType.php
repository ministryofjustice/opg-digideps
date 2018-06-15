<?php

namespace AppBundle\Form\Org;

use AppBundle\Entity as EntityDir;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ClientContactType
 *
 *
 */
class ClientContactType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('firstName', FormTypes\TextType::class, ['required' => true])
            ->add('lastName', FormTypes\TextType::class, ['required' => true])
            ->add('jobTitle', FormTypes\TextType::class)
            ->add('phone', FormTypes\TextType::class, ['required' => true])
            ->add('email', FormTypes\TextType::class, ['required' => true])
            ->add('orgName', FormTypes\TextType::class)
            ->add('address1', FormTypes\TextType::class)
            ->add('address2', FormTypes\TextType::class)
            ->add('address3', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('addressCountry', FormTypes\CountryType::class, ['preferred_choices' => ['', 'GB'], 'empty_value' => 'Please select ...',])
            ;

        $builder->add('save', FormTypes\SubmitType::class);
    }

    /**
     * Set default form options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['add_clientcontact'],
                'translation_domain' => 'client-contacts',
                'data-class' => EntityDir\ClientContact::class
            ]
        );
    }
}
