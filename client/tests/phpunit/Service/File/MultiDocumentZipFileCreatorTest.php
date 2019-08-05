<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\File\Storage\StorageInterface;
use Mockery as m;
use ZipArchive;

class MultiDocumentZipFileCreatorTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * This is far from ideal, as we're also having to partially test DocumentsZipFileCreator here.
     * We could simplify this test by making DocumentsZipFileCreator available through the service locator
     */
    public function testCreateZipFile()
    {
        $zipFileName1 = 'Report_12345678_2016_2017.zip';
        $zipFileName2 = 'Report_12345678_2017_2018.zip';

        $storageRef1 = 'r1';
        $storageRef2 = 'r2';
        $storageRef3 = 'r3';
        $storageRef4 = 'r4';

        $doc1 = m::mock(Document::class);
        $doc1->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $doc1->shouldReceive('getFileName')->once()->withNoArgs()->andReturn('file1.pdf');
        $doc1->shouldReceive('getStorageReference')->once()->withNoArgs()->andReturn($storageRef1);

        $doc2 = m::mock(Document::class);
        $doc2->shouldReceive('getId')->once()->withNoArgs()->andReturn(2);
        $doc2->shouldReceive('getFileName')->once()->withNoArgs()->andReturn('file2.pdf');
        $doc2->shouldReceive('getStorageReference')->once()->withNoArgs()->andReturn($storageRef2);

        $doc3 = m::mock(Document::class);
        $doc3->shouldReceive('getId')->once()->withNoArgs()->andReturn(3);
        $doc3->shouldReceive('getFileName')->once()->withNoArgs()->andReturn('file3.pdf');
        $doc3->shouldReceive('getStorageReference')->once()->withNoArgs()->andReturn($storageRef3);

        $doc4 = m::mock(Document::class);
        $doc4->shouldReceive('getId')->once()->withNoArgs()->andReturn(4);
        $doc4->shouldReceive('getFileName')->once()->withNoArgs()->andReturn('file4.pdf');
        $doc4->shouldReceive('getStorageReference')->once()->withNoArgs()->andReturn($storageRef4);

        /** @var StorageInterface|m\MockInterface $storage */
        $storage = m::mock(StorageInterface::class);
        $storage->shouldReceive('retrieve')->once()->with($storageRef1)->andReturn('doc1-content');
        $storage->shouldReceive('retrieve')->once()->with($storageRef2)->andReturn('doc2-content');
        $storage->shouldReceive('retrieve')->once()->with($storageRef3)->andReturn('doc3-content');
        $storage->shouldReceive('retrieve')->once()->with($storageRef4)->andReturn('doc4-content');

        $reportSubmission1 = m::mock(ReportSubmission::class);
        $reportSubmission1->shouldReceive('getZipName')->once()->withNoArgs()->andReturn($zipFileName1);
        $reportSubmission1->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(true);
        $reportSubmission1->shouldReceive('getDocuments')->twice()->withNoArgs()->andReturn([$doc1, $doc2]);

        $reportSubmission2 = m::mock(ReportSubmission::class);
        $reportSubmission2->shouldReceive('getZipName')->once()->withNoArgs()->andReturn($zipFileName2);
        $reportSubmission2->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(true);
        $reportSubmission2->shouldReceive('getDocuments')->twice()->withNoArgs()->andReturn([$doc3, $doc4]);

        $reportSubmissions = [$reportSubmission1, $reportSubmission2];

        $sut = new MultiDocumentZipFileCreator($storage, $reportSubmissions);
        $mainZipFilename = $sut->createZipFile();

        $zipArchive = new ZipArchive();
        $zipArchive->open($mainZipFilename);

        //check we have two zip files created, and the names are correct
        $this->assertEquals(2, $zipArchive->numFiles);
        $this->assertEquals(0, $zipArchive->locateName($zipFileName1));
        $this->assertEquals(1, $zipArchive->locateName($zipFileName2));
        $zipArchive->close();

        //check cleanup is working properly
        $this->assertTrue(file_exists($mainZipFilename));
        $this->assertTrue(file_exists(DocumentsZipFileCreator::TMP_ROOT_PATH . $zipFileName1));
        $this->assertTrue(file_exists(DocumentsZipFileCreator::TMP_ROOT_PATH . $zipFileName2));
        $sut->cleanup();
        $this->assertFalse(file_exists($mainZipFilename));
        $this->assertFalse(file_exists(DocumentsZipFileCreator::TMP_ROOT_PATH . $zipFileName1));
        $this->assertFalse(file_exists(DocumentsZipFileCreator::TMP_ROOT_PATH . $zipFileName2));
    }

    public function testExceptionNoDocuments()
    {
        $this->setExpectedException(\Exception::class, DocumentsZipFileCreator::MSG_NOT_DOWNLOADABLE);

        $storage = m::mock(StorageInterface::class);
        $reportSubmission = m::mock(ReportSubmission::class);
        $reportSubmission->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(false);
        $reportSubmissions = [$reportSubmission];

        $sut = new MultiDocumentZipFileCreator($storage, $reportSubmissions);
        $sut->createZipFile();
    }
}
