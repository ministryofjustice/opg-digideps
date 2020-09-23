<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\v2\Registration\Model\OrgDeputyship;
use AppBundle\v2\Registration\Uploader\OrgDeputyshipUploader;
use PHPUnit\Framework\TestCase;

class OrgDeputyShipUploaderTest extends TestCase
{
    /**
     * @test
     * @dataProvider uploadProvider
     */
    public function upload(array $deputyships, int $validCount, int $invalidCount)
    {
        $sut = new OrgDeputyshipUploader();

        $uploadResults = $sut->upload($deputyships);

        self::assertEquals($validCount, $uploadResults['added']);
        self::assertEquals($invalidCount, $uploadResults['errors']);
    }

    public function uploadProvider()
    {
        return [
            'Valid deputyships' => [$this->createOrgDeputyship(3, 0), 3, 0],
            'Mix valid and invalid deputyships' => [$this->createOrgDeputyship(2, 1), 2, 1]
        ];
    }

    private function createOrgDeputyship(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = (new OrgDeputyship())->setIsValid(true);
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = (new OrgDeputyship())->setIsValid(false);
            }
        }

        return $deputyships;
    }
}
