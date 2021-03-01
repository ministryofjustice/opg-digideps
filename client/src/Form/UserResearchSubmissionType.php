<?php declare(strict_types=1);


namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserResearchSubmissionType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $deputyshipLengthTransKeys = ['underOne', 'oneToFive', 'sixToTen', 'overTen'];
        $satisfactionLabels = array_map(function ($length) {
            return $this->translator->trans('form.deputyshipLength.choices.' . $length, [], 'feedback');
        }, $deputyshipLengthTransKeys);

        $typesOfResearchTransKeys = ['surveys', 'videoCall', 'phone', 'inPerson'];
        $typesOfResearchLabels = array_map(function ($researchType) {
            return $this->translator->trans('form.typesOfResearch.choices.' . $researchType, [], 'feedback');
        }, $typesOfResearchTransKeys);

        $deviceAccessTransKeys = ['yes', 'no'];
        $deviceAccessLabels = array_map(function ($response) {
            return $this->translator->trans('form.deviceAccess.choices.' . $response, [], 'feedback');
        }, $deviceAccessTransKeys);

        $builder
            ->add('deputyshipLength', ChoiceType::class, [
                'choices' => array_combine($satisfactionLabels, $deputyshipLengthTransKeys),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('agreedResearchTypes', ChoiceType::class, [
                'choices' => array_combine($typesOfResearchLabels, $typesOfResearchTransKeys),
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('hasAccessToVideoCallDevice', ChoiceType::class, [
                'choices' => array_combine($deviceAccessLabels, $deviceAccessTransKeys),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('submitButton', SubmitType::class);
    }
}
