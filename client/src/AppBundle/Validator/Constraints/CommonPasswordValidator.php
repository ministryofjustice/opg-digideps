<?php
namespace AppBundle\Validator\Constraints;

use RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CommonPasswordValidator extends ConstraintValidator
{
    const TMP_ROOT_PATH = '/tmp/';
    const PWNED_PW_URL = 'https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt';

    /**
     * @var string
     */
    private string $filePathCommonPasswords;
    /**
     * @var string
     */
    private string $pwnedPasswordsUrl;

    public function __construct()
    {
        $this->filePathCommonPasswords = self::TMP_ROOT_PATH . 'commonpasswords.txt';
        $this->pwnedPasswordsUrl = self::PWNED_PW_URL;
    }
    /**
     * Validates a password is not in list of pwned passwords
     *
     * @param string $password
     * @param Constraint $constraint
     */
    public function validate($password, Constraint $constraint)
    {
        $this->checkCommonPasswordsFileExists($this->filePathCommonPasswords);

        if ($this->passwordMatchesCommonPasswords($password, $this->filePathCommonPasswords)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    protected function passwordMatchesCommonPasswords(string $searchTerm, string $filePath)
    {
        $matches = array();

        $handle = @fopen($filePath, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle);
                if (strpos($buffer, $searchTerm) !== false) {
                    $matches[] = $buffer;
                }
            }
            fclose($handle);
        }
        //show results:
        if (count($matches) > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkCommonPasswordsFileExists(string $filePath)
    {
        if (file_exists($filePath) & (time()-filemtime($filePath) < 24 * 3600)) {
            return;
        } else {
            $written = file_put_contents(
                "$filePath",
                fopen($this->pwnedPasswordsUrl, 'r')
            );
            if ($written === false) {
                throw new RuntimeException(sprintf('Unable to download or write common password file to disk'));
            }
        }
    }
}
