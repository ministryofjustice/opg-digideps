<?php

declare(strict_types=1);

namespace App\Service\Search;

use App\Entity\Client;

class CourtOrderSearchFilter
{
    public function handleSearchTermFilter(string $searchTerm): string
    {
        if (Client::isValidCaseNumber($searchTerm)) {
            return sprintf('WHERE cl.case_number = LOWER(\'%1$s\') or ca.client_case_number = LOWER(\'%1$s\')', $searchTerm);
        } else {
            return sprintf('WHERE LOWER(cl.lastname) LIKE LOWER(\'%1$s\') or LOWER(ca.client_lastname) LIKE LOWER(\'%1$s\')', '%'.$searchTerm.'%');
        }
    }
}
