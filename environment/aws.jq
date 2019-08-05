# jq filter file to build aws config file
[
    "[profile default]",
    "region=eu-west-1",
    "role_arn=arn:aws:iam::" + .account_ids[env.TF_WORKSPACE] + ":role/" + (env.TF_VAR_default_role // "ci"),
    "credential_source=Environment"
] | join("\n")
