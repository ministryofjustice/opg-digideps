# Rotating AWS Account Root Credentials

## Rotation policy
- Password: maximum age of 90 days
- Access Key: maximum age of 90 days

## Requirements
- Lastpass Enterprise account with access to the 'Shared-OPG\OPG WebOps' folder
- Virtual MFA device (eg. authy, google authenticator)

## Credentials
Root credentials can be found in lastpass by searching for "digideps"

Each entry contains the following:
- root account email in the username field
- password
- MFA token in the notes field

## Process
- Configure your MFA device using the MFA token
- Follow the amazon documentation using the credentials & MFA device:
https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_passwords_change-root.html
- Ensure any aged access keys are deleted: https://docs.aws.amazon.com/IAM/latest/UserGuide/id_root-user.html#id_root-user_manage_delete-key
