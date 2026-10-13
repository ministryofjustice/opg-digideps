<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Mail;

use Symfony\Component\Uid\Uuid;

enum EmailTemplate: string implements \JsonSerializable
{
    case SetPassword = '48f53dab-dcc7-47d8-8cea-30c99b5eaf94';
    case ResetPassword = 'bd7a889a-f539-4a53-beac-d038bf351ee8';
    case ConfirmPassword = 'b2315b43-e0bf-45cf-876e-f2becea85374';

    case Activation = '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa';
    case ReportSubmittedConfirmation = '2f8fff09-5a71-446a-a220-d8a3dc78fa42';
    case ClientDetailsChange = '258aaf2d-076b-4b5c-a386-f3551c5f3945';
    case DeputyDetailsChange = '6469b39b-6ace-4f93-9e80-6152627e0d36';
    case InvitationLay = 'b8afb0d0-c8e5-4191-bce7-74ba91c74cad';
    case InvitationOrg = 'd410fce7-ce00-46eb-824d-82f998a437a4';
    case LegacyResetPassword = '827555cc-498a-43ef-957a-63fa387065e3';
    case ProcessOrgCsv = 'ce20ca97-a954-4d34-8a21-8b4f156188a8';
    case ProcessLayCsv = '1e6fddc4-999d-4c44-8038-1853ea0e8511';


    public function asUuid(): Uuid
    {
        return Uuid::fromRfc4122($this->value);
    }

    public static function fromUuid(Uuid $uuid): self
    {
        return EmailTemplate::from($uuid->toRfc4122());
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
