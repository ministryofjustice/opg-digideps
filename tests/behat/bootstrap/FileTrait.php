<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait FileTrait
{
    /**
     * @Then the page content should be a zip file containing files with the following MD5 checksums:
     */
    public function thePagecontentShouldBeZipContainingFilesChecksum(TableNode $table)
    {
        $pageContent = $this->getSession()->getPage()->getContent();

        $tmpFile = '/tmp/dd_filetrait.zip';
        file_put_contents($tmpFile, $pageContent);

        foreach ($table->getRowsHash() as $file => $expectedChecksum) {
            exec("unzip -c $tmpFile $file | md5sum", $lines);
            if (empty($lines)) {
                throw new \RuntimeException("$file not found in ZIP file");
            }
            $md5Sum = trim($lines[0], '- ');
            if ($md5Sum !== $expectedChecksum) {
                throw new \RuntimeException("File missing or wrong checksum for $file, expected $expectedChecksum, $md5Sum given");
            }
        }
    }
}
