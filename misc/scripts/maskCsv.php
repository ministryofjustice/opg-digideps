<?php
if (empty($argv[3])) {
    echo "Usage: php [script name] source.csv output.csv name.surname\n" . "
    e.g. php misc/scripts/maskCsv.php ~/Desktop/file.csv ~/Desktop/file.masked.csv elvis.ciotti";
    exit(1);
}
$replacer = new Replacer($argv[1], $argv[2], $argv[3] . '+pa[INDEX]@digital.justice.gov.uk');
$replacer->run();


class Replacer
{
    private $f1;

    private $f2;

    /**
     * @var string
     */
    private $baseEmailToUse;

    /**
     * @var array
     */
    private static $emailReplaced = [];

    /**
     * @var int
     */
    private static $emailCurrentIndex = 1;

    /**
     * Replacer constructor.
     * @param $f1
     * @param $f2
     * @param $baseEmailToUse
     */
    public function __construct($f1, $f2, $baseEmailToUse)
    {
        $this->f1 = fopen($f1, 'r');
        $this->f2 = fopen($f2, 'w+');
        $this->baseEmailToUse = $baseEmailToUse;
    }


    public function run()
    {
        $firstRow = fgetcsv($this->f1);
        fwrite($this->f2, implode(',', $firstRow) . "\n");
        //fputcsv($this->f2, $firstRow);
        $columnIndexToName = array_reverse($firstRow, true);
        $cycleCount = 0;
        while (($data = fgetcsv($this->f1)) !== FALSE) {
            $cycleCount++;
            foreach ($data as $columnIndex => $columnValue) {
                $columnName = $columnIndexToName[$columnIndex];
                switch ($columnName) {
                    case 'Email':
                        $data[$columnIndex] = $this->replaceEmail($columnValue);
                        break;

                    case 'Case':
                        $data[$columnIndex] = '99' . str_pad($cycleCount, 6, '0', STR_PAD_LEFT);
                        break;

                    case 'Forename':
                    case 'Surname':
                    case 'Adrs1':
                    case 'Adrs2':
                    case 'Adrs2':
                    case 'Adrs3':
                    case 'Adrs4':
                    case 'Adrs5':
                    case 'Postcode':
                    case 'Dep Forename':
                    case 'Dep Surname':
                    case 'Dep Adrs1':
                    case 'Dep Adrs2':
                    case 'Dep Adrs3':
                    case 'Dep Adrs4':
                    case 'Dep Adrs5':
                    case 'Dep Postcode':
                        $data[$columnIndex] = $this->maskString($columnValue);
                        break;
                }
            }
            fwrite($this->f2, implode(',', $data) . "\n");
            //fputcsv($this->f2, $data);
        }
        fclose($this->f2);
    }


    /**
     * @param string $email
     * @return mixed
     */
    private function replaceEmail($email)
    {
        if (isset(self::$emailReplaced[$email])) {
            return self::$emailReplaced[$email];
        }

        $ret = str_replace('[INDEX]', self::$emailCurrentIndex++, $this->baseEmailToUse);
        self::$emailReplaced[$email] = $ret;

        return $ret;
    }

    /**
     * Mask data
     * @param $string
     * @return mixed
     */
    private function maskString($string)
    {
        $string = preg_replace('/[A-D]/', 'A', $string);
        $string = preg_replace('/[E-I]/', 'E', $string);
        $string = preg_replace('/[L-P]/', 'L', $string);
        $string = preg_replace('/[Q-V]/', 'Q', $string);
        $string = preg_replace('/[W-Z]/', 'W', $string);
        $string = preg_replace('/[1-3]/', '1', $string);
        $string = preg_replace('/[4-7]/', '4', $string);
        $string = preg_replace('/[8-9]/', '8', $string);

        return $string;
    }


}

