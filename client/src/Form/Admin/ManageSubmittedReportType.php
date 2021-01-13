<?php

namespace App\Form\Admin;

use App\Form\DateType;
use App\Form\Subscriber\ReportTypeChoicesSubscriber;
use App\Form\Traits\HasTranslatorTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ManageSubmittedReportType extends AbstractType
{
    use HasTranslatorTrait;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->addEventSubscriber(new ReportTypeChoicesSubscriber($this->translator))
            ->add('unsubmittedSection', FormTypes\CollectionType::class, [
                'entry_type' => UnsubmittedSectionType::class,
            ])
            ->add('startDate', DateType::class, [
                'invalid_message' => 'report.startDate.invalidMessage',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.startDate.notBlank', 'groups' => ['startEndDates']]),
                    new Constraints\Date(['message' => 'report.startDate.invalidMessage', 'groups' => ['startEndDates ']]),
                ]
            ])
            ->add('endDate', DateType::class, [
                'invalid_message' => 'report.endDate.invalidMessage',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.endDate.notBlank', 'groups' => ['startEndDates']]),
                    new Constraints\Date(['message' => 'report.endDate.invalidMessage', 'groups' => ['startEndDates ']]),
                ],
            ])
            ->add('dueDateChoice', ReportDueDateType::class)
            ->add('dueDateCustom', DateType::class, [
                'invalid_message' => 'report.dueDate.invalidMessage',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDate.notBlank', 'groups' => ['due_date_new']]),
                    new Constraints\Date(['message' => 'report.dueDate.invalidMessage', 'groups' => ['due_date_new']]),
                ],
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name' => 'report',
            'compound' => true,
            'validation_groups'  => function (FormInterface $form) {
                $ret = ['unsubmitted_sections', 'change_due_date', 'startEndDates'];

                if ($form['dueDateChoice']->getData() == 'custom') {
                    $ret[] = 'due_date_new';
                }

                return $ret;
            },
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'manage_report';
    }
}
