<?php

namespace AppBundle\Service;

class DataNormaliser
{
    /** @var array */
    const NORMALISE_CHARS = [
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'ü' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f',
        'ă' => 'a', 'î' => 'i', 'â' => 'a', 'ș' => 's', 'ț' => 't', 'Ă' => 'A', 'Î' => 'I', 'Â' => 'A', 'Ș' => 'S', 'Ț' => 'T',
    ];

    /**
     * @param string $value
     * @return string
     */
    public function normaliseSurname(string $value): string
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = strtr($value, self::NORMALISE_CHARS);

        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);

        $value = preg_replace('/([^a-z0-9])/i', '', $value);

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public function normaliseCaseNumber(string $value): string
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('#^([a-z0-9]+/)#i', '', $value);

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public function normaliseDeputyNo(string $value): string
    {
        $value = trim($value);
        $value = strtolower($value);

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public function normalisePostCode(string $value): string
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('/([^a-z0-9])/i', '', $value);

        return $value;
    }
}
