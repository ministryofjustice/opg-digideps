<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\v2\Registration\DTO\LayDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;

/**
 * If $reportTypeShouldChangeTo is set, this is the proposed new type for the found report.
 * If $activeClientExistsForCase is true, we found at least one active case with the same case number as the DTO,
 * but it had an incompatible report. This is always true if a compatible client and report were found, but not vice
 * versa.
 */
class ClientMatch
{
    public function __construct(
        public readonly ?Client $client,
        public readonly ?Report $report,
        public readonly ?string $reportTypeShouldChangeTo,
        public readonly bool $activeClientExistsForCase,
    ) {
    }
}

/**
 * Given a Lay Deputy DTO, decide whether there is an existing client already present in the database compatible
 * with it. To be compatible, the DTO has to reference the same case, and the current report associated with
 * that client has to marry up with the report and order type details in the DTO.
 */
class LayClientMatcher
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function matchDto(LayDeputyshipDto $dto): ClientMatch
    {
        $caseNumber = $dto->getCaseNumber();
        $potentialClients = $this->em->getRepository(Client::class)->findByCaseNumberIncludingDischarged($caseNumber);
        $activeClientExistsForCase = false;
        $reportTypeShouldChangeTo = null;

        /** @var Client $potentialClient */
        foreach ($potentialClients as $potentialClient) {
            if ($potentialClient->isDeleted()) {
                continue;
            }

            $activeClientExistsForCase = true;

            // If there is an undeleted Client, we might be able to just use that Client,
            // rather than making a new one, providing this deputy can see that client's report as a co-deputy;
            // to work that out, we work out which report type we should be creating for the DTO, then check whether the
            // existing client already has a report of a compatible type and that the DTO is marked as HYBRID.
            //
            // COMPATIBLE REPORT TYPES (incoming = in CSV row, existing = type of existing report on client)
            // incoming = 102, existing = 102
            // incoming = 103, existing = 103
            // incoming = 104, existing = 104
            // incoming = 102-4, existing = 102 or 102-4, incoming row marked as HYBRID (report will become a hybrid)
            // incoming = 103-4, existing = 103 or 103-4, incoming row marked as HYBRID (report will become a hybrid)
            $determinedReportType = PreRegistration::getReportTypeByOrderType(
                $dto->getTypeOfReport(),
                $dto->getOrderType(),
                PreRegistration::REALM_LAY,
            );

            $existingReport = $potentialClient->getCurrentReport();

            // if the report type we have calculated is at the start of the existing report's type
            // then we potentially have a compatible report; if the existing report is a hybrid (ends with '-4'),
            // our calculated report is also only compatible if it is also marked as a HYBRID row
            $isCompatibleReport = str_starts_with($determinedReportType, $existingReport->getType());
            if (str_ends_with($determinedReportType, '-4')) {
                $isCompatibleReport &= 'HYBRID' === $dto->getHybrid();
            }

            if ($isCompatibleReport) {
                // report is compatible but type should change
                $existingReportType = $existingReport->getType();
                if ($existingReportType !== $determinedReportType) {
                    $reportTypeShouldChangeTo = $determinedReportType;
                }

                return new ClientMatch(
                    $potentialClient,
                    $existingReport,
                    $reportTypeShouldChangeTo,
                    $activeClientExistsForCase,
                );
            }
        }

        return new ClientMatch(null, null, null, $activeClientExistsForCase);
    }
}
