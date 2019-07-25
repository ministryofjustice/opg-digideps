<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportDeclarationType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $agreedBehalfChoices = [
            // api models contains those keys too. Change them accordingly if needed
            'only_deputy' => 'agreedBehalfDeputy.only_deputy',
            'more_deputies_behalf' => 'agreedBehalfDeputy.more_deputies_behalf',
            'more_deputies_not_behalf' => 'agreedBehalfDeputy.more_deputies_not_behalf',
        ];

        $loggedInUser = $this->tokenStorage->getToken()->getUser();

        $report = $builder->getData();
        if (!$loggedInUser->isLayDeputy()) {
            $agreedBehalfChoices = ['not_deputy' => 'agreedBehalfDeputy.not_deputy'] + $agreedBehalfChoices;
        }

        $builder
                ->add('id', FormTypes\HiddenType::class)
                ->add('agree', FormTypes\CheckboxType::class, [
                     'constraints' => new NotBlank(['message' => 'report-declaration.agree.notBlank']),
                 ])
                 ->add('agreedBehalfDeputy', FormTypes\ChoiceType::class, [
                    'choices' => array_flip($agreedBehalfChoices),
                    'choice_translation_domain' => 'report-declaration',
                    'translation_domain' => 'report-declaration',
                    'expanded' => true,
                ])
                ->add('agreedBehalfDeputyExplanation', FormTypes\TextareaType::class)
                ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-declaration',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $validationGroups = ['declare'];

                if ($data->getAgreedBehalfDeputy() == 'more_deputies_not_behalf') {
                    $validationGroups[] = 'declare-explanation';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'report_declaration';
    }
}
