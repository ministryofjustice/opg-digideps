<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\RouterInterface;
use AppBundle\Form\ReportSubmitType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Report;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\DependencyInjection\Container;

/**
 * Logic to handle report submit form from each report page tab
 */
class ReportSubmitter
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ReportSubmitType
     */
    protected $form;

    /**
     * @param Container $container
     * @param ReportSubmitType $type
     */
    public function __construct(Container $container, ReportSubmitType $type)
    {
        $this->container = $container;
        $this->form = $this->container->get('form.factory')->create($type);
    }

    /**
     * @param Report $report
     * 
     * @return RedirectResponse|null
     */
    public function submit(Report $report)
    {
        $this->form->handleRequest($this->container->get('request'));

        if ($this->form->get('submitReport')->isClicked() && $this->form->isValid() && $reportStatus->isReadyToSubmit()) {
            $report->setReviewed(true);
            $this->container->get('apiclient')->putC('report/' . $report->getId(), $report, [
                'deserialise_group' => 'reviewed',
            ]);
            return new RedirectResponse($this->container->get('router')->generate('report_add_further_info', ['reportId' => $report->getId()]));
        }
        return null;
    }

    /**
     * @return FormView
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

}
