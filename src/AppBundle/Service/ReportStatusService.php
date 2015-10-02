<?php
namespace AppBundle\Service;

use AppBundle\Entity\Report;
use Symfony\Component\Translation\TranslatorInterface;

class ReportStatusService {

    /**
     * @var Report
     */
    private $report;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Report $report, TranslatorInterface $translator)
    {
        $this->report = $report;
        $this->translator = $translator;
    }
    
    public function getDecisionsStatus() {
        
        $decisions = $this->report->getDecisions();
        
        if ($decisions) {
            $count = count($decisions);
            
            if ($count == 1) {
                return "1 " . $this->translator('decision');
            } else if ($count > 1) {
                return "${count} " . $this->translator('decisions');
            }

        }
        
        return;
        
    }

    





}
