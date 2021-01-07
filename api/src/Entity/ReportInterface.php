<?php

namespace App\Entity;

use DateTime;

interface ReportInterface
{
    /**
     * @return Client
     */
    public function getClient();

    public function getFinancialSummary();

    public function updateSectionsStatusCache(array $sectionIds);

    public function getSubmitted();

    /**
     * @return AssetInterface[]
     */
    public function getAssets();

    /**
     * @return BankAccountInterface[]
     */
    public function getBankAccounts();

    /**
     * @return string
     */
    public function getAgreedBehalfDeputy();

    public function setSubmittedBy($user);

    /**
     * @param bool $submitted
     */
    public function setSubmitted($submitted);

    /**
     * @param DateTime|null $submitDate
     */
    public function setSubmitDate(?DateTime $submitDate = null);
}
