<?php

namespace DigidepsBehat\Common;

trait UserOrganisationTrait
{
    /**
     * @Given :userEmail has been added to the :organisationEmailIdentifier organisation
     */
    public function hasBeenAddedToTheOrganisation($userEmail, $organisationEmailIdentifier)
    {
        $query = "INSERT INTO organisation_user (user_id, organisation_id) VALUES
          (
            (SELECT id FROM dd_user WHERE email = '{$userEmail}'),
            (SELECT id FROM organisation WHERE email_identifier = '{$organisationEmailIdentifier}')
          ) ON CONFLICT DO NOTHING;";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given :userEmail has been removed from the :organisationEmailIdentifier organisation
     */
    public function hasBeenRemovedFromTheOrganisation($userEmail, $organisationEmailIdentifier)
    {
        $query = "DELETE FROM organisation_user WHERE organisation_id =
                    (SELECT id FROM organisation WHERE email_identifier = '{$organisationEmailIdentifier}')
                    AND user_id = (SELECT id FROM dd_user WHERE email = '{$userEmail}')";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }
}
