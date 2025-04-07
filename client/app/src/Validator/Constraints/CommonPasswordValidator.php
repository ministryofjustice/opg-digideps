<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CommonPasswordValidator extends ConstraintValidator
{
    public const TMP_ROOT_PATH = '/tmp/';
    public const PWNED_PW_URL = 'https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt';
    private const CACHE_PASSWORDS_SECS = 24 * 3600;

    private string $filePathCommonPasswords;
    private string $pwnedPasswordsUrl = '';
    private bool $refreshCache;

    /**
     * @param bool $refreshCache Set to true if password cache should be regularly refreshed
     */
    public function __construct(
        string $filePathCommonPasswords = self::TMP_ROOT_PATH.'commonpasswords.txt',
        string $pwnedPasswordsUrl = self::PWNED_PW_URL,
        bool $refreshCache = true,
    ) {
        $this->filePathCommonPasswords = $filePathCommonPasswords;
        $this->pwnedPasswordsUrl = $pwnedPasswordsUrl;
        $this->refreshCache = $refreshCache;
    }

    /**
     * Validates a password is not in list of pwned passwords.
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (isset($value)) {
            $this->checkCommonPasswordsFileExists($this->filePathCommonPasswords);

            if ($this->passwordMatchesCommonPasswords($value, $this->filePathCommonPasswords)) {
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
        if (
            file_exists($filePath) &
            ((!$this->refreshCache) | (time() - filemtime($filePath) < self::CACHE_PASSWORDS_SECS))
        ) {
            return;
        }

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
