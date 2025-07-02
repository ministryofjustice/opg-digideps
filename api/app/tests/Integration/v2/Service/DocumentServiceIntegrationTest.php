<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Service;

use App\Repository\DocumentRepository;
use App\Service\File\Storage\S3Storage;
use App\Tests\Integration\Fixtures;
use App\v2\Service\DocumentService;
use Aws\S3\S3Client;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentServiceIntegrationTest extends KernelTestCase
{
    private const PREFIX = 'DocumentServiceIntegrationTest_';

    private Fixtures $fixtures;
    private S3Client $s3Client;
    private DocumentRepository $documentRepository;
    private S3Storage $s3Storage;
    private string $bucketName;
    private EntityManagerInterface $em;
    private DocumentService $sut;

    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);

        $this->bucketName = getenv('S3_BUCKETNAME');

        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);

        $this->s3Client = $container->get(S3Client::class);

        $this->documentRepository = $container->get(DocumentRepository::class);

        $this->fixtures = new Fixtures($this->em);

        // manually construct these objects because they are otherwise wired to the wrong bucket
        $this->s3Storage = new S3Storage(
            $this->s3Client,
            $this->bucketName,
            $container->get(LoggerInterface::class)
        );

        $this->sut = new DocumentService(
            $this->documentRepository,
            $this->s3Storage,
            $container->get(LoggerInterface::class),
        );
    }

    public function tearDown(): void
    {
        (new ORMPurger($this->em))->purge();

        // delete S3 objects
        $objectsForTest = $this->s3Client->listObjectsV2([
            'Bucket' => $this->bucketName,
            'Prefix' => self::PREFIX,
        ])->get('Contents');

        foreach ($objectsForTest as $object) {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $object['Key'],
            ]);
        }
    }

    public function testDeleteDocumentsOlderThan(): void
    {
        $now = new \DateTime();
        $oneYearAgo = new \DateTime('-1 year');

        $oldDocRef = self::PREFIX.'oldDoc';
        $newDocRef = self::PREFIX.'newDoc';

        // add files to S3, to associate with documents
        $this->s3Storage->store($oldDocRef, 'foo bar');
        $this->s3Storage->store($newDocRef, 'bar baz');

        // add two reports so we can create documents
        $client = $this->fixtures->createClient();
        $report1 = $this->fixtures->createReport($client);
        $report2 = $this->fixtures->createReport($client);

        // add two documents: one which is old and should be deleted, and one which is new and should remain
        $oldDoc = $this->fixtures->createDocument($report1, $oldDocRef, false);
        $oldDoc->setCreatedOn($oneYearAgo);
        $oldDoc->setStorageReference($oldDocRef);

        $newDoc = $this->fixtures->createDocument($report2, $newDocRef, false);
        $newDoc->setCreatedOn($now);
        $newDoc->setStorageReference($newDocRef);

        $this->em->persist($oldDoc);
        $this->em->persist($newDoc);
        $this->em->flush();

        // test
        $numMarkedForDeletion = $this->sut->deleteDocumentsOlderThan($now);

        // check correct objects are marked for deletion
        self::assertEquals(1, $numMarkedForDeletion);

        // old document should be marked
        $tags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $oldDocRef,
        ])->get('TagSet');

        self::assertContains(['Key' => 'Purge', 'Value' => '1'], $tags);

        // new document should not be marked
        $tags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $newDocRef,
        ])->get('TagSet');

        self::assertNotContains(['Key' => 'Purge', 'Value' => '1'], $tags);

        // check old database record has been deleted and the new remains
        self::assertNull($this->documentRepository->findOneBy(['fileName' => $oldDocRef]));
        self::assertNotNull($this->documentRepository->findOneBy(['fileName' => $newDocRef]));
    }
}
