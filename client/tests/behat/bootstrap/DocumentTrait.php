<?php declare(strict_types=1);


namespace DigidepsBehat;


use AppBundle\Entity\Client;
use AppBundle\Service\Client\RestClient;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @method Behat\Mink\Session getSession
 */
trait DocumentTrait
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @Given /^the document "([^"]*)" belonging to "([^"]*)" is deleted from AWS but not updated locally$/
     * @param string $document, the filename of the document
     * @param string $userEmail, the email associated with the client the document is associated with
     */
    public function theDocumentBelongingToIsDeletedFromAWSButNotUpdatedLocally(string $document, string $userEmail)
    {
        $url = $this->getSession()->getCurrentUrl();
        preg_match_all('/report\/([\d]+)/',$url,$matches);
        $reportId = $matches[1];

        $report = $this->restClient->get("/report/${reportId}", 'Report\\Report');
        $docs = $report->getDocuments();

        $documentToAmend = null;

        foreach ($docs as $doc) {
            if ($doc->getFileName() === $document) {
                $documentToAmend = $doc;
            }
        }

        $documentToAmend->setStorageReference('wrong_reference');

        $this->restClient->put("/document/" . $documentToAmend->getId(), );
    }
}
