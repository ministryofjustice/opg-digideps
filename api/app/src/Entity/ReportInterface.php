<?php

namespace App\Entity;

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

    public function setSubmitDate(?\DateTime $submitDate = null);
}
