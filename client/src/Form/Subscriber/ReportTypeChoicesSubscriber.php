<?php

namespace App\Form\Subscriber;

use App\Entity\Report\Report;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportTypeChoicesSubscriber implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $report = $event->getData();
        $form = $event->getForm();

        $form->add('type', ChoiceType::class, [
            'choices' => $this->resolveReportTypeOptions($report),
        ]);
    }

    private function resolveReportTypeOptions(Report $report): array
    {
        $options = [
            $this->translator->trans('propertyAffairsGeneral', [], 'common') => Report::LAY_PFA_HIGH_ASSETS_TYPE,
            $this->translator->trans('propertyAffairsMinimal', [], 'common') => Report::LAY_PFA_LOW_ASSETS_TYPE,
            $this->translator->trans('healthWelfare', [], 'common') => Report::TYPE_104,
            $this->translator->trans('propertyAffairsGeneralHealthWelfare', [], 'common') => Report::TYPE_102_4,
            $this->translator->trans('propertyAffairsMinimalHealthWelfare', [], 'common') => Report::TYPE_103_4,
        ];

        if (!$report->isLayReport()) {
            foreach ($options as $key => &$value) {
                $value = ($report->isPAreport()) ? "$value-6" : "$value-5";
            }
        }

        return $options;
    }
}
