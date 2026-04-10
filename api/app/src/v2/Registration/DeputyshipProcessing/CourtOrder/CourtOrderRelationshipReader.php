<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use Doctrine\DBAL\Connection;
use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class CourtOrderRelationshipReader
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return \Generator<int,CourtOrderRelationship,void,void>
     */
    public function read(): \Generator
    {
        $result = $this->connection->executeQuery("
            WITH data AS (
                SELECT
                    d.case_number AS case_number,
                    d.order_uid AS order_uid,
                    d.order_type AS order_type,
                    COUNT(d.is_hybrid) > 0 AS is_hybrid,
                    ARRAY_AGG(d.deputy_uid ORDER BY d.deputy_uid) AS deputy_uids
                FROM staging.deputyship d
                WHERE
                    d.order_status = 'ACTIVE'
                    AND d.deputy_status_on_order = 'ACTIVE'
                GROUP BY d.case_number, d.order_uid, d.order_type
            ), resolved AS (
                SELECT
                    d1.order_uid AS hw_order_uid,
                    d2.order_uid AS pfa_order_uid,
                    CASE
                        WHEN d1.deputy_uids = d2.deputy_uids THEN 'hybrid'
                        ELSE 'dual'
                        END AS kind,
                    d1.is_hybrid AND d2.is_hybrid AS sirius_hybrid
                FROM data d1
                    JOIN data d2
                        ON d1.case_number = d2.case_number
                        AND d1.order_uid <> d2.order_uid
                WHERE
                    d1.order_type = 'hw'
                    AND d2.order_type = 'pfa'
            )
            SELECT
                co.client_id,
                co.id AS order_id,
                sco.id AS sibling_id,
                COALESCE(r.kind, 'single') AS kind
            FROM court_order co
            LEFT JOIN resolved r
                ON (co.order_type = 'hw' AND co.court_order_uid = r.hw_order_uid)
                OR (co.order_type = 'pfa' AND co.court_order_uid = r.pfa_order_uid)
            LEFT JOIN court_order sco
                ON (co.order_type = 'pfa' AND sco.court_order_uid = r.hw_order_uid)
                OR (co.order_type = 'hw' AND sco.court_order_uid = r.pfa_order_uid)
            WHERE
                co.status = 'ACTIVE'
            ORDER BY co.client_id
        ");
        foreach ($result->iterateAssociative() as $row) {
            $row = new ValidatingArray($row);
            yield new CourtOrderRelationship(
                $row->getIntegerOrDefault('client_id', 0),
                $row->getIntegerOrThrow('order_id'),
                $row->getIntegerOrNull('sibling_id'),
                CourtOrderKind::from($row->getStringOrThrow('kind'))
            );
        }
    }
}
