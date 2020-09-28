<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use AppBundle\v2\Registration\Uploader\OrgDeputyshipUploader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Tests\AppBundle\v2\Registration\TestHelpers\OrgDeputyshipTestHelper;

class OrgDeputyShipUploaderTest extends KernelTestCase
{
    /** @var NamedDeputyRepository */
    private $namedDeputyRepository;

    /** @var Serializer */
    private $serializer;

    /** @var EntityManager */
    private $em;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->em = $container->get('em');
        $this->namedDeputyRepository = $this->em->getRepository(NamedDeputy::class);
        $this->serializer = $container->get('serializer');
    }
    /**
     * @test
     * @dataProvider uploadProvider
     */
    public function upload_provides_feedback_on_entities_processed(array $deputyships, int $expectedValid, int $expectedInvalid)
    {
        $sut = new OrgDeputyshipUploader();

        $actualUploadResults = $sut->upload($deputyships, $this->em);

        self::assertCount($expectedValid, $actualUploadResults['added']['clients']);
        self::assertCount($expectedValid, $actualUploadResults['added']['named_deputies']);
        self::assertEquals($expectedInvalid, $actualUploadResults['errors']);
    }

    // add extra field in array for orgs created
    public function uploadProvider()
    {
        return [
            '3 valid Org Deputyships' =>
                [
                    OrgDeputyshipTestHelper::generateOrgDeputyshipDtos(3, 0), 3, 0
                ],
            '2 valid, 1 invalid Org Deputyships' =>
                [
                    OrgDeputyshipTestHelper::generateOrgDeputyshipDtos(2, 1), 2, 1
                ]
        ];
    }

    /** @test  */
    public function upload_new_named_deputies_are_created()
    {
        $sut = new OrgDeputyshipUploader();
        $deputyships = OrgDeputyshipTestHelper::generateOrgDeputyshipDtos(1, 0);

        $sut->upload($deputyships, $this->em);

        self::assertTrue(
            OrgDeputyshipTestHelper::namedDeputyWasCreated($deputyships[0], $this->namedDeputyRepository),
            sprintf('Named deputy with email %s could not be found', $deputyships[0]->getEmail())
        );
    }

    /** @test */
    public function upload_existing_named_deputies_are_not_processed()
    {
        $sut = new OrgDeputyshipUploader();
        $deputyships = OrgDeputyshipTestHelper::generateOrgDeputyshipDtos(1, 0);

        $namedDeputy = (new NamedDeputy())
            ->setEmail1($deputyships[0]->getEmail())
            ->setDeputyNo($deputyships[0]->getDeputyNumber())
            ->setFirstname($deputyships[0]->getFirstname())
            ->setLastname($deputyships[0]->getLastname());

        $this->em->persist($namedDeputy);
        $this->em->flush();

        $actualUploadResults = $sut->upload($deputyships, $this->em);

        self::assertCount(0, $actualUploadResults['added']['named_deputies']);
        self::assertEquals(0, $actualUploadResults['errors']);
    }

    public function upload_existing_organisations_are_not_processed()
    {
    }

    public function upload_existing_clients_are_updated()
    {
    }
}
