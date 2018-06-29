<?php

namespace AppBundle\Entity;

interface ReportInterface
{
    /**
     * @return Client
     */
    public function getClient();

    public function getFinancialSummary();
}
