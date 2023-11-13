<?php

namespace App\Service;

class DataCompression
{
    /**
     * @return string
     */
    public function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    /**
     * @return mixed
     */
    public function decompress($data)
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
