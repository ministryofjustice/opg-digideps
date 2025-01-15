<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CommonPasswordValidator extends ConstraintValidator
{
    public const TMP_ROOT_PATH = '/tmp/';
    public const PWNED_PW_URL = 'https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt';

    private string $filePathCommonPasswords;

    private string $pwnedPasswordsUrl;

    public function __construct(
        string $filePathCommonPasswords = self::TMP_ROOT_PATH.'commonpasswords.txt',
        string $pwnedPasswordsUrl = self::PWNED_PW_URL,
    ) {
        $this->filePathCommonPasswords = $filePathCommonPasswords;
        $this->pwnedPasswordsUrl = $pwnedPasswordsUrl;
    }

    /**
     * Validates a password is not in list of pwned passwords.
     */
    public function validate($password, Constraint $constraint)
    {
        if (isset($password)) {
            $this->checkCommonPasswordsFileExists($this->filePathCommonPasswords);

            if ($this->passwordMatchesCommonPasswords($password, $this->filePathCommonPasswords)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }

    protected function passwordMatchesCommonPasswords(string $searchTerm, string $filePath)
    {
        $matches = [];
        $handle = @fopen($filePath, 'r');
        if ($handle && strlen($searchTerm) > 0) {
            while (!feof($handle)) {
                $buffer = fgets($handle);
                if (false !== strpos($buffer, $searchTerm)) {
                    $matches[] = $buffer;
                }
            }
            fclose($handle);
        }
        // show results:
        if (count($matches) > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkCommonPasswordsFileExists(string $filePath)
    {
        if (file_exists($filePath) & (time() - filemtime($filePath) < 24 * 3600)) {
            return;
        } else {
            $fp = fopen($this->pwnedPasswordsUrl, 'r');
            if (false !== $fp) {
                $written = file_put_contents(
                    "$filePath",
                    $fp
                );
                if (false === $written) {
                    throw new \RuntimeException(sprintf('Unable to download or write common password file to disk'));
                }
            }
        }
    }
}
