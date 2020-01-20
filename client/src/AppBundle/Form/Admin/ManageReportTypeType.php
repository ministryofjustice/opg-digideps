<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Report\Report;
use AppBundle\Form\Traits\HasTranslatorTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ManageReportTypeType extends AbstractType
{
    use HasTranslatorTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dueDateChoiceTransPrefix = 'reportManage.form.dueDateChoice.choices.';
        $report = $options['report'];
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $this->resolveReportTypeOptions($report)
            ])
            ->add('dueDateChoice', ChoiceType::class, [
                'choices'     => array_flip([
                    'keep'  => $dueDateChoiceTransPrefix . 'keep',
                    3       => $dueDateChoiceTransPrefix . '3weeks',
                    4       => $dueDateChoiceTransPrefix . '4weeks',
                    5       => $dueDateChoiceTransPrefix . '5weeks',
                    'custom' => $dueDateChoiceTransPrefix . 'custom',
                ]),
                'expanded'    => true,
                'multiple'    => false,
                'mapped'      => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDateChoice.notBlank', 'groups' => ['change_due_date']])
                ],
            ])
            ->add('dueDateCustom', DateType::class, [
                'widget'      => 'text',
                'input'       => 'datetime',
                'format'      => 'yyyy-MM-dd',
                'invalid_message' => 'report.dueDate.invalidMessage',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDate.notBlank', 'groups' => ['due_date_new']]),
                    new Constraints\Date(['message' => 'report.dueDate.invalidMessage', 'groups' => ['due_date_new']]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
        ])->setRequired(['report']);
    }

    private function resolveReportTypeOptions(Report $report)
    {
        if ($report->isLayReport()) {
            return [
                $this->translate('propertyAffairsGeneral', [], 'common') => Report::TYPE_102,
                $this->translate('propertyAffairsMinimal', [], 'common') => Report::TYPE_103,
                $this->translate('healthWelfare', [], 'common') => Report::TYPE_104,
                $this->translate('propertyAffairsGeneralHealthWelfare', [], 'common') => Report::TYPE_102_4,
                $this->translate('propertyAffairsMinimalHealthWelfare', [], 'common') => Report::TYPE_103_4,
            ];

        } else if ($report->isPAreport()) {
            return [
                $this->translate('propertyAffairsGeneral', [], 'common') => Report::TYPE_102_6,
                $this->translate('propertyAffairsMinimal', [], 'common') => Report::TYPE_103_6,
                $this->translate('healthWelfare', [], 'common') => Report::TYPE_104_6,
                $this->translate('propertyAffairsGeneralHealthWelfare', [], 'common') => Report::TYPE_102_4_6,
                $this->translate('propertyAffairsMinimalHealthWelfare', [], 'common') => Report::TYPE_103_4_6,
            ];
        } else if ($report->isProfReport()) {
            return [
                $this->translate('propertyAffairsGeneral', [], 'common') => Report::TYPE_102_5,
                $this->translate('propertyAffairsMinimal', [], 'common') => Report::TYPE_103_5,
                $this->translate('healthWelfare', [], 'common') => Report::TYPE_104_5,
                $this->translate('propertyAffairsGeneralHealthWelfare', [], 'common') => Report::TYPE_102_4_5,
                $this->translate('propertyAffairsMinimalHealthWelfare', [], 'common') => Report::TYPE_103_4_5,
            ];
        }
    }
}
