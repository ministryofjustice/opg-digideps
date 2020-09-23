<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

class OrgDeputyshipUploader
{
    public function upload(array $deputyships)
    {
        $uploadResults = ['added' => 0, 'errors' => 0];

        foreach ($deputyships as $deputyship) {
            $deputyship->isValid() ? $uploadResults['added']++ : $uploadResults['errors']++;
        }

        return $uploadResults;
    }
}
