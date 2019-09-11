<?php declare(strict_types=1);


namespace DigidepsBehat;


use AppBundle\Entity\Report\Document;
use Exception;

/**
 * @method Behat\Mink\Session getSession
 */
trait DocumentTrait
{
    /**
     * @Given /^the document "([^"]*)" belonging to "([^"]*)" is deleted from AWS but not updated locally$/
     * @param string $document, the filename of the document
     * @param string $userEmail, the email associated with the client the document is associated with
     */
    public function theDocumentBelongingToIsDeletedFromAWSButNotUpdatedLocally(string $document, string $userEmail)
    {
        $url = $this->getSession()->getCurrentUrl();
        preg_match_all('/report\/([\d]+)/',$url,$matches);

        if (strpos($matches[0][0], 'report') === false) {
            throw new Exception(
                "This step can only be run while on a page that includes '/report/{id}'"
            );
        }

        $reportId = $matches[1][0];

        $report = $this->getRestClient()->get("/report/${reportId}", 'Report\\Report');
        $docs = $report->getDocuments();

        /** @var Document $documentToAmend */
        $documentToAmend = null;

        foreach ($docs as $doc) {
            if ($doc->getFileName() === $document) {
                $documentToAmend = $doc;
            }
        }

        $this->getS3Storage()->delete($documentToAmend->getStorageReference());
    }
}
