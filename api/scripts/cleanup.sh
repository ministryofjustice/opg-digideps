#!/bin/bash
# exit on error
set -e

# Export unit test DB config so it can be used in tests
export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=digideps_unit_test}
export PGUSER=${DATABASE_USERNAME:=api}

psql -c "SELECT COUNT(1)
FROM dd_user u
LEFT JOIN deputy_case dc ON u.id = dc.user_id
LEFT JOIN client c ON c.id = dc.client_id AND (c.deleted_at IS NULL)
WHERE u.registration_date < now() - INTERVAL '30 days' AND u.role_name = 'ROLE_LAY_DEPUTY'
  AND NOT EXISTS (SELECT 1 FROM report r WHERE r.client_id = c.id)
  AND NOT EXISTS (SELECT 1 FROM odr o WHERE o.client_id = c.id)"
