<?php

namespace AppBundle\Service;

class CsvUploader
{
    /**
     * keep in sync with inverse method in API
     *
     * @param  mixed  $data
     * @return string
     */
    public static function compressData($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }
}
