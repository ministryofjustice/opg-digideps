<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait FileTrait
{
    /**
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
            switch($check) {
                case 'exactFileName+md5sum':
                    $expectedChecksum = $value;
                    exec("unzip -c $tmpFile $file | md5sum", $lines);
                    if (empty($lines)) {
                        throw new \RuntimeException("$file not found in ZIP file");
                    }
                    $md5Sum = trim($lines[0], '- ');
                    if ($md5Sum !== $expectedChecksum) {
                        throw new \RuntimeException("File missing or wrong checksum for $file, expected $expectedChecksum, $md5Sum given");
                    }
                    break;

                case 'regexpName+sizeAtLeast':
                    $sizeAtLeast = $value;
                    exec("unzip -l $tmpFile | grep -E \"{$file}\" ", $lines);
                    if (empty($lines)) {
                        throw new \RuntimeException("File matching $file not found in ZIP file");
                    }
                    $sizeBytes = array_shift(array_filter(explode(' ',$lines[0])));
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
