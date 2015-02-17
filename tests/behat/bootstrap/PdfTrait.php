<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * @method ApplicationBehatHelper getApplicationBehatHelper()
 */
trait PdfTrait
{

    /**
     * @BeforeScenario @cleanPDFs
     */
    public function cleanPDFs(BeforeScenarioScope $scope)
    {
        throw new Exception('TO IMPLEMENT');
        self::getApplicationBehatHelper()->getSm()->get('PDFService')->cleanAllPDFs();
    }

    /**
     * @Then the PDF contains each of the following text:
     */
    public function thePdfContains(PyStringNode $pieces)
    {
        throw new Exception('TO IMPLEMENT');
        $pdfs = self::getApplicationBehatHelper()->getSm()->get('PDFService')->getPdfs();

        if (count($pdfs) > 1) {
            throw new \RuntimeException("found more than one PDF, use the tag @cleanPDFs before the scenario");
        }
        $pdf = array_shift($pdfs);
        $pdfTextFile = $pdf . '.txt';
        exec("pdftotext $pdf $pdfTextFile");
        if (!file_exists($pdfTextFile)) {
            echo "pdftotext not found or not executed, $pdfTextFile not found. Step skipped.\nInstall poppler-utils to check PDF content\n";
        }
        $pdfText = file_get_contents($pdfTextFile);
        foreach ($pieces->getStrings() as $search) {
            if (strpos($pdfText, $search) === false) {
                throw new \RuntimeException("Cannot find '{$search}' in text version of the PDF file located at $pdfTextFile\n");
            }
        }
    }

}