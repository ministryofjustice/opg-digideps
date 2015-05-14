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

/**
 * Logic to handle report submit form from each report page tab
 */
class ReportSubmitter
{

    /**
     * @var FormFactory 
     */
    protected $formFactory;

    /**
     * @var ReportSubmitType
     */
    protected $form;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;


    public function __construct(FormFactory $formFactory, ReportSubmitType $type, RouterInterface $router, $container)
    {
        $this->form = $formFactory->create($type);
        $this->router = $router;
        $this->request = $container->get('request');
    }

    /**
     * @param Report $report
     * 
     * @return RedirectResponse|null
     */
    public function isReportSubmitted(Report $report)
    {
        $this->form->handleRequest($this->request);

        if ($this->form->get('submitReport')->isClicked() && $this->form->isValid() && $report->readyToSubmit()) {
            return new RedirectResponse($this->router->generate('report_declaration', ['reportId' => $report->getId()]));
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