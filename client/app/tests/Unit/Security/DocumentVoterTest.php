<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Security;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Organisation;
use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Security\DocumentVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DocumentVoterTest extends TestCase
{
    private TokenInterface&MockObject $token;
    private User $user;
    private Organisation $organisation;
    private Client $client;
    private Report $report;
    private Document $document;
    private DocumentVoter $sut;

    public function setUp(): void
    {
        $this->sut = new DocumentVoter();
        $this->token = self::createMock(TokenInterface::class);

        $this->user = new User()->setId(87);
        $this->organisation = new Organisation()->setId(31)->setIsActivated('yes');
        $this->report = new Report();
        $this->client = new Client();
        $this->document = new Document();
    }

    /**
     * @dataProvider getSupportedAttributes
     */
    public function testSupports(string $attribute, int $expected): void
    {
        $this->token->method('getUser')->willReturn(null);
        self::assertEquals($expected, $this->sut->vote($this->token, new Report(), [$attribute]));
    }

    public static function getSupportedAttributes(): array
    {
        return [
            [DocumentVoter::ADD_DOCUMENT, VoterInterface::ACCESS_DENIED],
            [DocumentVoter::DELETE_DOCUMENT, VoterInterface::ACCESS_DENIED],
            ['UNKNOWN', VoterInterface::ACCESS_ABSTAIN],
        ];
    }

    public function testVoteOnAttributeAllowsLayDeputiesToAddDocumentsToTheirOwnReport(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureReportBelongsToClient()
            ->ensureClientBelongsToDeputy()
            ->assertDeputyCanAddDocument();
    }

    public function testVoteOnAttributeDeniesLayDeputiesFromAddingDocumentsToAnotherDeputiesReport(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureReportBelongsToClient()
            ->ensureClientBelongsToDifferentDeputy()
            ->assertDeputyCannotAddDocument();
    }

    public function testVoteOnAttributeAllowsOrgDeputiesToAddDocumentsToReportBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToSameOrganisation()
            ->assertDeputyCanAddDocument();
    }

    public function testVoteOnAttributeDeniesOrgDeputiesToAddDocumentsToReportNotBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToDifferentOrganisation()
            ->assertDeputyCannotAddDocument();
    }

    public function testVoteOnAttributeAllowsLayDeputiesToDeleteDocumentsFromTheirOwnReport(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDeputy()
            ->assertDeputyCanDeleteDocument();
    }

    public function testVoteOnAttributeDeniesLayDeputiesFromDeletingDocumentsFromAnotherDeputiesReport(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientBelongsToDifferentDeputy()
            ->assertDeputyCannotDeleteDocument();
    }

    public function testVoteOnAttributeAllowsOrgDeputiesToDeleteDocumentsFromReportBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToSameOrganisation()
            ->assertDeputyCanAddDocument();
    }

    public function testVoteOnAttributeDeniesOrgDeputiesFromDeletingDocumentsFromReportNotBelongingToTheirOrg(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->ensureDocumentBelongsToReport()
            ->ensureReportBelongsToClient()
            ->ensureClientAndDeputyBelongToDifferentOrganisation()
            ->assertDeputyCannotAddDocument();
    }

    private function ensureReportBelongsToClient(): static
    {
        $this->report->setClient($this->client);

        return $this;
    }

    private function ensureDocumentBelongsToReport(): static
    {
        $this->document->setReport($this->report);

        return $this;
    }

    private function ensureClientBelongsToDeputy(): static
    {
        $this->client->addUser($this->user);

        return $this;
    }

    private function ensureClientAndDeputyBelongToSameOrganisation(): static
    {
        $this->user->setOrganisations([$this->organisation]);
        $this->client->setOrganisation($this->organisation);

        return $this;
    }

    private function ensureClientAndDeputyBelongToDifferentOrganisation(): static
    {
        $deputyOrg = new Organisation()->setId(72)->setIsActivated('yes');
        $this->user->setOrganisations([$deputyOrg]);
        $this->client->setOrganisation($this->organisation);

        return $this;
    }

    private function ensureClientBelongsToDifferentDeputy(): static
    {
        $this->client->addUser(new User());

        return $this;
    }

    private function assertDeputyCanAddDocument(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->sut->vote($this->token, $this->report, [DocumentVoter::ADD_DOCUMENT])
        );
    }

    private function assertDeputyCannotAddDocument(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->sut->vote($this->token, $this->report, [DocumentVoter::ADD_DOCUMENT])
        );
    }

    private function assertDeputyCanDeleteDocument(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->sut->vote($this->token, $this->document, [DocumentVoter::DELETE_DOCUMENT])
        );
    }

    private function assertDeputyCannotDeleteDocument(): void
    {
        self::assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->sut->vote($this->token, $this->document, [DocumentVoter::DELETE_DOCUMENT])
        );
    }
}
