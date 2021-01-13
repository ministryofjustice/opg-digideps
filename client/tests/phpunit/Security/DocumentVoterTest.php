<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoterTest extends TestCase
{
    private $sut;
    private $token;
    private $deputy;
    private $organisation;
    private $client;
    private $report;
    private $document;

    public function setUp(): void
    {
        $this->sut = new DocumentVoter();
        $this->token = $this->createMock(TokenInterface::class);

        $this->deputy = (new User())->setId(87);
        $this->organisation =  (new Organisation())->setId(31)->setIsActivated(true);
        $this->report = new Report();
        $this->client = new Client();
        $this->document = new Document();
    }

    /**
     * @dataProvider getSupportedAttributes
     * @param string $attribute
     * @param bool $expected
     */
    public function testSupports(string $attribute, int $expected): void
    {
        $this->token->method('getUser')->willReturn(null);
        $this->assertEquals($expected, $this->sut->vote($this->token, new Report(), [$attribute]));
    }

    /**
     * @return array
     */
    public function getSupportedAttributes(): array
    {
        return [
            [DocumentVoter::ADD_DOCUMENT, Voter::ACCESS_DENIED],
            [DocumentVoter::DELETE_DOCUMENT, Voter::ACCESS_DENIED],
            ['UNKNOWN', Voter::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @test
     */
    public function voteOnAttributeAllowsLayDeputiesToAddDocumentsToTheirOwnReport(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDeputy()
            ->assertDeputyCanAddDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeDeniesLayDeputiesFromAddingDocumentsToAnotherDeputiesReport(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDifferentDeputy()
            ->assertDeputyCannotAddDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeAllowsOrgDeputiesToAddDocumentsToReportBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToSameOrganisation()
            ->assertDeputyCanAddDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeDeniesOrgDeputiesToAddDocumentsToReportNotBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToDifferentOrganisation()
            ->assertDeputyCannotAddDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeAllowsLayDeputiesToDeleteDocumentsFromTheirOwnReport(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDeputy()
            ->assertDeputyCanDeleteDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeDeniesLayDeputiesFromDeletingDocumentsFromAnotherDeputiesReport(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDifferentDeputy()
            ->assertDeputyCannotDeleteDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeAllowsOrgDeputiesToDeleteDocumentsFromReportBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToSameOrganisation()
            ->assertDeputyCanAddDocument();
    }

    /**
     * @test
     */
    public function voteOnAttributeDeniesOrgDeputiesFromDeletingDocumentsFromReportNotBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->deputy);

        $this
            ->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToDifferentOrganisation()
            ->assertDeputyCannotAddDocument();
    }

    private function ensureReportBelongsToClient()
    {
        $this->report->setClient($this->client);

        return $this;
    }

    private function ensureDocumentBelongsToReport()
    {
        $this->document->setReport($this->report);

        return $this;
    }

    private function ensureClientBelongsToDeputy()
    {
        $this->client->addUser($this->deputy);

        return $this;
    }

    private function ensureClientAndDeputyBelongToSameOrganisation()
    {
        $this->deputy->setOrganisations(new ArrayCollection([$this->organisation]));
        $this->client->setOrganisation($this->organisation);

        return $this;
    }

    private function ensureClientAndDeputyBelongToDifferentOrganisation()
    {
        $deputyOrg = (new Organisation())->setId(72)->setIsActivated(true);
        $this->deputy->setOrganisations(new ArrayCollection([$deputyOrg]));
        $this->client->setOrganisation($this->organisation);

        return $this;
    }

    private function ensureClientBelongsToDifferentDeputy()
    {
        $this->client->addUser(new User());

        return $this;
    }

    private function assertDeputyCanAddDocument()
    {
        $this->assertEquals(Voter::ACCESS_GRANTED, $this->sut->vote($this->token, $this->report, [DocumentVoter::ADD_DOCUMENT]));
    }

    private function assertDeputyCannotAddDocument()
    {
        $this->assertEquals(Voter::ACCESS_DENIED, $this->sut->vote($this->token, $this->report, [DocumentVoter::ADD_DOCUMENT]));
    }

    private function assertDeputyCanDeleteDocument()
    {
        $this->assertEquals(Voter::ACCESS_GRANTED, $this->sut->vote($this->token, $this->document, [DocumentVoter::DELETE_DOCUMENT]));
    }

    private function assertDeputyCannotDeleteDocument()
    {
        $this->assertEquals(Voter::ACCESS_DENIED, $this->sut->vote($this->token, $this->document, [DocumentVoter::DELETE_DOCUMENT]));
    }
}
