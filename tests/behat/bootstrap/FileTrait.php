<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait FileTrait
{
    /**
     * | NdrRep-.*\.pdf | regexpName+sizeAtLeast | 50000  |
     * | file2.pdf | exactFileName+md5sum | 6b871eed6b34b560895f221de1420a5a |
     *
     * @Then the page content should be a zip file containing files with the following files:
     */
    public function thePagecontentShouldBeZipContainingFilesChecksum(TableNode $table)
    {
        $pageContent = $this->getSession()->getPage()->getContent();

        $tmpFile = '/tmp/dd_filetrait.zip';
        file_put_contents($tmpFile, $pageContent);

        foreach ($table->getRowsHash() as $file => $data) {
            list($check, $value) = $data;
            $lines = [];
            switch ($check) {

                case 'regexpName+sizeAtLeast':
                    $sizeAtLeast = $value;
                    exec("unzip -l $tmpFile | grep -E \"{$file}\" ", $lines);
                    if (empty($lines)) {
                        exec("unzip -l $tmpFile", $all);
                        throw new \RuntimeException("File matching $file not found in ZIP file. Files:".implode(', ', $all));
                    }
                    $sizeBytes = array_shift(array_filter(explode(' ', $lines[0])));
                    if ($sizeBytes < $sizeAtLeast) {
                        throw new \RuntimeException("File matching $file is $sizeBytes bytes, at least $sizeAtLeast expected");
                    }
                    break;

                default:
                    throw new \RuntimeException("$check check not implemented");
            }
        }
    }
}
