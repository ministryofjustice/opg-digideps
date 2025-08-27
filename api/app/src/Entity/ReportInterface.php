<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

interface ReportInterface
{
    public function getClient(): Client;

    public function getFinancialSummary();

    /**
     * @param string[] $sectionIds
     */
    public function updateSectionsStatusCache(array $sectionIds);

    public function getSubmitted(): ?bool;

    /**
     * @return Collection<int, AssetInterface>|AssetInterface[]
     */
    public function getAssets(): Collection|array;

    /**
     * @return Collection<int, BankAccountInterface>|BankAccountInterface[]
     */
    public function getBankAccounts(): Collection|array;

    public function getAgreedBehalfDeputy(): ?string;

    public function setSubmittedBy(?User $submittedBy): static;

    public function setSubmitted(?bool $submitted): static;

    public function setSubmitDate(?DateTimeInterface $submitDate = null): static;
}
