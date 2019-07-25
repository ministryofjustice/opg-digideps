<?php

namespace AppBundle\Service;

class DataCompression
{
    /**
     * @param $data
     * @return string
     */
    public function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function decompress($data)
    {
        return json_decode(gzuncompress(base64_decode($data)), true);
    }
}
