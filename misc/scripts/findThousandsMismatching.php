<?php


function findTotalsThousandsMismatch($values)
{
    $numbersOfValues = count($values);
    
    $ret = [];
    for ($i = 0; $i <= pow(2, $numbersOfValues); $i++) {

        $vals = $values; 
        $valsWithRealValue = [];
        // modify values
        $bin = str_pad((string) base_convert($i, 10, 2), $numbersOfValues, "0", STR_PAD_LEFT);
        for ($j = 0; $j < strlen($bin); $j++) {
            if ($bin[$j] === '1') {
                $valsWithRealValue[$j] =$vals[$j] . ' => ' . $vals[$j] * 1000;
                $vals[$j] = $vals[$j] * 1000;
                
            }
        }
        $diff = abs(array_sum($vals));
        $ret[$diff] = "DIFF: £" . number_format(array_sum($vals), 2) . " Changes: ".implode(' | ',$valsWithRealValue);
    }
    ksort($ret);

    return array_values($ret);
}

$combinations = findTotalsThousandsMismatch([
   // add values here, mone out and closing balance must be negative
]);
echo implode("\n", $combinations);
echo "best Match: £" . array_slice(array_keys($combinations), 0, 1)[0]."\n";
