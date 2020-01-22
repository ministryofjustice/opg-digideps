<?php

namespace AppBundle\Form\Subscriber;

use AppBundle\Entity\Report\Report;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class ReportTypeChoicesSubscriber implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event): void
    {
        $report = $event->getData();
        $form = $event->getForm();

        $form->add('type', ChoiceType::class, [
            'choices' => $this->resolveReportTypeOptions($report)
        ]);
    }

    /**
     * @param Report $report
     * @return array
     */
    private function resolveReportTypeOptions(Report $report): array
    {
        $keys = [
            $this->translator->trans('propertyAffairsGeneral', [], 'common'),
            $this->translator->trans('propertyAffairsMinimal', [], 'common'),
            $this->translator->trans('healthWelfare', [], 'common'),
            $this->translator->trans('propertyAffairsGeneralHealthWelfare', [], 'common'),
            $this->translator->trans('propertyAffairsMinimalHealthWelfare', [], 'common')
        ];

        $options = [
            $keys[0] => Report::TYPE_102,
            $keys[1] => Report::TYPE_103,
            $keys[2] => Report::TYPE_104,
            $keys[3] => Report::TYPE_102_4,
            $keys[4] => Report::TYPE_103_4,
        ];

        if (!$report->isLayReport()) {
            $options[$keys[0]] = ($report->isPAreport()) ? Report::TYPE_102_6 : Report::TYPE_102_5;
            $options[$keys[1]] = ($report->isPAreport()) ? Report::TYPE_103_6 : Report::TYPE_103_5;
            $options[$keys[2]] = ($report->isPAreport()) ? Report::TYPE_104_6 : Report::TYPE_104_5;
            $options[$keys[3]] = ($report->isPAreport()) ? Report::TYPE_102_4_6 : Report::TYPE_102_4_5;
            $options[$keys[4]] = ($report->isPAreport()) ? Report::TYPE_103_4_6 : Report::TYPE_103_4_5;
        }

        return $options;
    }
}
