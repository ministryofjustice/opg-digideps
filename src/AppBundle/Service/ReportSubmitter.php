<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\RouterInterface;
use AppBundle\Form\ReportSubmitType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Report;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;

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
     * @return boolean
     */
    public function isReportSubmitted(Report $report)
    {
        $this->form->handleRequest($this->request);
        
        return $this->form->get('submitReport')->isClicked() 
               && $this->form->isValid() 
               && $report->readyToSubmit();
    }
    
    /**
     * @param Report $report
     * 
     * @return RedirectResponse
     */
    public function getRedirectResponse(Report $report)
    {
        return new RedirectResponse($this->router->generate('report_declaration', [ 'reportId' => $report->getId()]));
    }
    
    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

}